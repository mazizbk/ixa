<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:    2016-12-02 11:43:55
 */

namespace Azimut\Bundle\FrontofficeBundle\EventListener\Doctrine;

use Azimut\Bundle\FrontofficeBundle\Entity\Menu;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Azimut\Bundle\FrontofficeBundle\Entity\Page;
use Azimut\Bundle\FrontofficeBundle\Entity\PageTranslation;

class PageSubscriber implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return [
            Events::onFlush,
            Events::prePersist,
            Events::preUpdate,
        ];
    }

    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof Page) {
                $this->handlePageDisplayOrder($entity, $em);
            }
        }
    }

    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $entity = $eventArgs->getEntity();

        if ($entity instanceof PageTranslation) {
            $this->uniquifySlugs($entity, $em);
        }
    }

    public function preUpdate(LifecycleEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $entity = $eventArgs->getEntity();

        if ($entity instanceof PageTranslation) {
            $this->uniquifySlugs($entity, $em);
        }
    }

    private function handlePageDisplayOrder(Page $page, EntityManagerInterface $em)
    {
        $uow = $em->getUnitOfWork();
        $menuClassMetadata = $em->getMetadataFactory()->getMetadataFor(Menu::class);
        $pageClassMetadata = $em->getMetadataFactory()->getMetadataFor(Page::class);

        $changeSet = $uow->getEntityChangeSet($page);

        // skip if display order and parent element hasn't changed
        if (!isset($changeSet['displayOrder']) && !isset($changeSet['parentPage']) && !isset($changeSet['menu'])) {
            return;
        }

        if (isset($changeSet['parentPage']) || isset($changeSet['menu'])) {
            $oldMenu = isset($changeSet['menu']) ? $changeSet['menu'][0] : null;
            $oldParentPage = isset($changeSet['parentPage']) ? $changeSet['parentPage'][0] : null;
            $oldDisplayOrder = isset($changeSet['displayOrder']) ? $changeSet['displayOrder'][0] : $page->getDisplayOrder();

            // reindex display orders in old parent
            if (null != $oldMenu) {
                $em->getRepository(Page::class)->decreaseMenuChildrenDisplayOrdersStartingAt($oldMenu, $oldDisplayOrder);

                // tell unit of work to recompile old parent menu changeset
                $uow->computeChangeSet($menuClassMetadata, $oldMenu);
            } else {
                $em->getRepository(Page::class)->decreasePageChildrenDisplayOrdersStartingAt($oldParentPage, $oldDisplayOrder);

                // tell unit of work to recompile old parent page changeset
                $uow->computeChangeSet($pageClassMetadata, $oldParentPage);
            }

            // insert in new parent (starting reindex from the end)
            if (null != $page->getMenu()) {
                $this->updatePageDisplayOrder($page, $page->getMenu()->getNextChildPageOrder(), $em);
            } else {
                $this->updatePageDisplayOrder($page, $page->getParentPage()->getNextChildPageOrder(), $em);
            }
        } else {
            $this->updatePageDisplayOrder($page, $changeSet['displayOrder'][0], $em);
        }


        if (null != $page->getMenu()) {
            // tell unit of work to recompile new parent menu changeset
            $uow->computeChangeSet($menuClassMetadata, $page->getMenu());
        } else {
            // tell unit of work to recompile new parent page changeset
            $uow->computeChangeSet($pageClassMetadata, $page->getParentPage());
        }
    }

    private function updatePageDisplayOrder(Page $page, $oldDisplayOrder, EntityManagerInterface $em)
    {
        $uow = $em->getUnitOfWork();
        $pageClassMetadata = $em->getMetadataFactory()->getMetadataFor(Page::class);

        // do nothing if display order did not change or not provided
        if (null == $page->getDisplayOrder() || $page->getDisplayOrder() == $oldDisplayOrder) {
            return;
        }

        // update order (only section between old and new displayOrder)
        $startDisplayOrder = $oldDisplayOrder;
        $endDisplayOrder = $page->getDisplayOrder();

        // displayOrder has been increased
        if ($endDisplayOrder > $startDisplayOrder) {
            for ($i=$startDisplayOrder+1; $i<=$endDisplayOrder; $i++) {
                if (null != $page->getMenu()) {
                    $pagePropagate = $em->getRepository(Page::class)->findOneByMenuAndDisplayOrder($page->getMenu(), $i);
                } else {
                    $pagePropagate = $em->getRepository(Page::class)->findOneByParentPageAndDisplayOrder($page->getParentPage(), $i);
                }

                // if the next display order does not exist then we reach the limit, we will insert element here
                if (null == $pagePropagate) {
                    $page->setDisplayOrder($i-1);
                    break;
                } else {
                    $pagePropagate->setDisplayOrder($pagePropagate->getDisplayOrder()-1);
                    $uow->computeChangeSet($pageClassMetadata, $pagePropagate);
                }
            }
        }
        // displayOrder has been decreased
        else {
            for ($i=$startDisplayOrder-1; $i>=$endDisplayOrder; $i--) {
                if (null != $page->getMenu()) {
                    $pagePropagate = $em->getRepository(Page::class)->findOneByMenuAndDisplayOrder($page->getMenu(), $i);
                } else {
                    $pagePropagate = $em->getRepository(Page::class)->findOneByParentPageAndDisplayOrder($page->getParentPage(), $i);
                }

                // if the next display order does not exist then we reach the limit, we will insert element here
                if (null == $pagePropagate) {
                    $page->setDisplayOrder($i+1);
                } else {
                    $pagePropagate->setDisplayOrder($pagePropagate->getDisplayOrder()+1);
                    $uow->computeChangeSet($pageClassMetadata, $pagePropagate);
                }
            }
        }
    }

    private function uniquifySlugs(PageTranslation $pageTranslation, EntityManagerInterface $em)
    {
        $page = $pageTranslation->getPage();
        $slug = $pageTranslation->getSlug();
        $locale = $pageTranslation->getLocale();

        $i = 0;
        $newSlug = $slug;

        // loop over all children of the current page's parent page
        if (null !== $page->getParentPage()) {
            if (null == $page->getParentPage()->getId()) {
                throw new \InvalidArgumentException("Can't assert unicity of page slug because its parent page hasn't been flushed");
            }

            if ($pageTranslation->getId()) {
                while (null !== $em->getRepository(Page::class)
                        ->findOneBySlugAndParentPageAndLocaleExcludingPage($newSlug, $page->getParentPage(), $locale, $page)) {
                    $i++;
                    $newSlug = $slug.'-'.$i;
                }
            } else {
                while (null !== $em->getRepository(Page::class)
                        ->findOneBySlugAndParentPageAndLocale($newSlug, $page->getParentPage(), $locale)) {
                    $i++;
                    $newSlug = $slug.'-'.$i;
                }
            }
        }

        // loop over all children of the current page's parent menu
        else {
            if (null == $page->getMenu()->getId()) {
                throw new \InvalidArgumentException("Can't assert unicity of page slug because its parent menu hasn't been flushed");
            }

            if ($pageTranslation->getId()) {
                while (null !== $em->getRepository(Page::class)
                        ->findOneBySlugAndSiteAndLocaleExcludingPage($newSlug, $page->getMenu()->getSite(), $locale, $page)) {
                    $i++;
                    $newSlug = $slug.'-'.$i;
                }
            } else {
                while (null !== $em->getRepository(Page::class)
                        ->findOneBySlugAndSiteAndLocale($newSlug, $page->getMenu()->getSite(), $locale)) {
                    $i++;
                    $newSlug = $slug.'-'.$i;
                }
            }
        }

        if ($newSlug !== $slug) {
            $pageTranslation->setSlug($newSlug);
        }
    }
}
