<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-02-11 17:28:48
 */

namespace Azimut\Bundle\FrontofficeBundle\Entity\Repository;

use Azimut\Bundle\FrontofficeBundle\Entity\Page;
use Azimut\Bundle\FrontofficeBundle\Entity\Site;
use Azimut\Bundle\FrontofficeBundle\Entity\Menu;
use Azimut\Bundle\FrontofficeBundle\Entity\PageLayout;
use Doctrine\ORM\EntityRepository;

class PageRepository extends EntityRepository
{
    public function createInstanceFromString($name)
    {
        $metadata = $this->getClassMetadata();
        $map = $metadata->discriminatorMap;

        if (!isset($map[$name])) {
            throw new \InvalidArgumentException(sprintf('No page of type "%s". Available: %s', $name, implode(', ', array_keys($map))));
        }

        $class = $map[$name];

        return new $class();
    }

    public function getAvailableTypes()
    {
        $metadata = $this->getClassMetadata();
        return $metadata->discriminatorMap;
    }

    public function findByMenuName($name)
    {
        return $this->getEntityManager()
            ->createQuery('SELECT p from AzimutFrontofficeBundle:Page p LEFT JOIN p.menu m WHERE m.name = :name')
            ->setParameter('name', $name)
            ->getResult()
        ;
    }

    public function findByMenuNameInSite($name, $siteId)
    {
        return $this->getEntityManager()
            ->createQuery('SELECT p from AzimutFrontofficeBundle:Page p LEFT JOIN p.menu m LEFT JOIN m.site s WHERE s.id = :siteId AND m.name = :name')
            ->setParameter('name', $name)
            ->setParameter('siteId', $siteId)
            ->getResult()
        ;
    }

    public function findOneBySlugAndParentPageAndLocale($slug, Page $parentPage, $locale)
    {
        return $this->getEntityManager()
            ->createQuery('SELECT p from AzimutFrontofficeBundle:Page p LEFT JOIN p.parentPage pp LEFT JOIN p.translations t WHERE pp = :parentPage AND t.slug = :slug AND t.locale = :locale')
            ->setParameter('parentPage', $parentPage)
            ->setParameter('slug', $slug)
            ->setParameter('locale', $locale)
            ->getOneOrNullResult()
        ;
    }

    public function findOneBySlugAndParentPageAndLocaleExcludingPage($slug, Page $parentPage, $locale, Page $excludedPage)
    {
        return $this->getEntityManager()
            ->createQuery('SELECT p from AzimutFrontofficeBundle:Page p LEFT JOIN p.parentPage pp LEFT JOIN p.translations t WHERE p != :excludedPage AND pp = :parentPage AND t.slug = :slug AND t.locale = :locale')
            ->setParameter('parentPage', $parentPage)
            ->setParameter('slug', $slug)
            ->setParameter('excludedPage', $excludedPage)
            ->setParameter('locale', $locale)
            ->getOneOrNullResult()
        ;
    }

    public function findOneBySlugAndSiteAndLocale($slug, Site $site, $locale)
    {
        return $this->getEntityManager()
            ->createQuery('SELECT p from AzimutFrontofficeBundle:Page p LEFT JOIN p.menu m LEFT JOIN p.translations t WHERE m.site = :site AND t.slug = :slug AND t.locale = :locale')
            ->setParameter('site', $site)
            ->setParameter('slug', $slug)
            ->setParameter('locale', $locale)
            ->getOneOrNullResult()
        ;
    }

    public function findOneBySlugAndSiteAndLocaleExcludingPage($slug, Site $site, $locale, Page $excludedPage)
    {
        return $this->getEntityManager()
            ->createQuery('SELECT p from AzimutFrontofficeBundle:Page p LEFT JOIN p.menu m LEFT JOIN p.translations t WHERE p != :excludedPage AND m.site = :site AND t.slug = :slug AND t.locale = :locale')
            ->setParameter('site', $site)
            ->setParameter('slug', $slug)
            ->setParameter('excludedPage', $excludedPage)
            ->setParameter('locale', $locale)
            ->getOneOrNullResult()
        ;
    }

    public function findOneByPathAndSite($path, Site $site)
    {
        $qb = $this->createQueryBuilderFindOneByPathAndSite($path, $site);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param string $path
     * @param Site   $site
     * @param null   $locale
     * @return Page|null
     */
    public function findOneActiveByPathAndSite($path, Site $site, $locale = null)
    {
        $qb = $this->createQueryBuilderFindOneByPathAndSite($path, $site);

        $qb
            ->leftJoin('p.translations', 'pt')
            ->addSelect('pt')
            ->andWhere('p.active = true')
        ;
        if(!is_null($locale)) {
            $qb->andWhere('pt.locale = :locale')->setParameter(':locale', $locale)->setMaxResults(1);
        }

        $query = $qb->getQuery();
        $query->useQueryCache(false);
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @var Page $foundPage */
        $foundPage = $query->getOneOrNullResult();

        if (null != $foundPage) {
            // check that parents pages are actives
            $page = $foundPage;
            while (null != ($page = $page->getParentPage())) {
                if (!$page->isActive()) {
                    $foundPage = null;
                }
            }
        }

        return $foundPage;
    }

    private function createQueryBuilderFindOneByPathAndSite($path, Site $site)
    {
        $qb = $this
            ->createQueryBuilder('p')
            // menu is added in the end
            ->where('m.site = :site')
            ->setParameter('site', $site)
        ;

        $exp = explode('/', $path);
        $currentPage = 'p';
        $currentPageTranslation = 't';
        $i    = 0;
        $exp = array_reverse($exp);
        foreach ($exp as $slug) {
            if ($i > 0) {
                $qb->leftJoin($currentPage.'.parentPage', 'p'.$i);
                $currentPage = 'p'.$i;
                $currentPageTranslation = 't'.$i;
            }

            $qb->andWhere($currentPageTranslation.'.slug = :slug'.$currentPage)->setParameter('slug'.$currentPage, $slug);
            $qb->leftJoin($currentPage.'.translations', $currentPageTranslation);

            $i++;
            if ($i === count($exp)) {
                $qb->andWhere($currentPage.'.parentPage IS NULL');
                $qb->leftJoin($currentPage.'.menu', 'm');
            }
        }

        return $qb;
    }

    public function findOneByRedirectionPathAndSite($path, $site)
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.redirections', 'r')
            ->leftJoin('p.site', 's')
            ->where('r.address = :path')
            ->andWhere('s = :site')
            ->setParameter('path', $path)
            ->setParameter('site', $site)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findOneByMenuAndDisplayOrder(Menu $menu, $displayOrder)
    {
        return $this->getEntityManager()
            ->createQuery('SELECT p from AzimutFrontofficeBundle:Page p WHERE p.menu = :menu and p.displayOrder = :displayOrder')
            ->setParameter('menu', $menu)
            ->setParameter('displayOrder', $displayOrder)
            ->getOneOrNullResult()
        ;
    }

    public function findOneByParentPageAndDisplayOrder(Page $parentPage, $displayOrder)
    {
        return $this->getEntityManager()
            ->createQuery('SELECT p from AzimutFrontofficeBundle:Page p WHERE p.parentPage = :parentPage and p.displayOrder = :displayOrder')
            ->setParameter('parentPage', $parentPage)
            ->setParameter('displayOrder', $displayOrder)
            ->getOneOrNullResult()
        ;
    }

    public function decreaseMenuChildrenDisplayOrdersStartingAt(Menu $menu, $displayOrder)
    {
        return $this->getEntityManager()
            ->createQuery('UPDATE AzimutFrontofficeBundle:Page p SET p.displayOrder=p.displayOrder-1 WHERE p.menu = :menu AND p.displayOrder >= :displayOrder')
            ->setParameter('menu', $menu)
            ->setParameter('displayOrder', $displayOrder)
            ->execute()
        ;
    }

    public function decreasePageChildrenDisplayOrdersStartingAt(Page $parentPage, $displayOrder)
    {
        return $this->getEntityManager()
            ->createQuery('UPDATE AzimutFrontofficeBundle:Page p SET p.displayOrder=p.displayOrder-1 WHERE p.parentPage = :parentPage AND p.displayOrder >= :displayOrder')
            ->setParameter('parentPage', $parentPage)
            ->setParameter('displayOrder', $displayOrder)
            ->execute()
        ;
    }

    public function getPageContentsCountByLayout(PageLayout $pageLayout)
    {
        return $this->getEntityManager()
            ->createQuery('SELECT COUNT(s.id) from AzimutFrontofficeBundle:PageContent s WHERE s.layout = :layout')
            ->setParameter('layout', $pageLayout)
            ->getSingleScalarResult()
        ;
    }

    /**
     * @param Menu   $menu
     * @param string $locale
     * @return Page[]
     */
    public function findActiveByMenuWithTranslations(Menu $menu, $locale)
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.translations', 'pt')
            ->addSelect('pt')
            ->leftJoin('p.childrenPages', 'cp')
            ->addSelect('cp')
            ->leftJoin('cp.translations', 'cpt')
            ->addSelect('cpt')
            ->where('pt.locale = :locale')
            ->andWhere('p.menu = :menu')
            ->andWhere('p.active = true')
            ->andWhere('p.showInMenu = true')
            ->setParameter(':locale', $locale)
            ->setParameter(':menu', $menu)
            ->orderBy('p.displayOrder', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
