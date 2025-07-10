<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-01-31 14:46:58
 */

namespace Azimut\Bundle\FrontofficeBundle\EventListener\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Azimut\Bundle\FrontofficeBundle\Entity\MenuDefinition;
use Azimut\Bundle\FrontofficeBundle\Entity\Menu;
use Azimut\Bundle\FrontofficeBundle\Entity\Site;
use Doctrine\ORM\UnitOfWork;

class MenuDefinitionSubscriber implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return [
            Events::onFlush,
        ];
    }

    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof MenuDefinition) {
                $this->createSitesMenus($entity, $em, $uow);
            }
        }
    }

    private function createSitesMenus(MenuDefinition $menuDefinition, EntityManagerInterface $em, UnitOfWork $uow)
    {
        /** @var Site[] $sites */
        $sites = $em->getRepository(Site::class)->findBy(['layout' => $menuDefinition->getLayout()]);

        foreach ($sites as $site) {
            $menu = new Menu();
            $menu->setMenuDefinition($menuDefinition);
            $site->addMenu($menu);

            $uow->computeChangeSet($em->getMetadataFactory()->getMetadataFor(Site::class), $site);
        }
    }
}
