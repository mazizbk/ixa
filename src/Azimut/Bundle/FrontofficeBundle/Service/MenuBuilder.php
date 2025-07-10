<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:    2016-09-20 14:50:54
 */

namespace Azimut\Bundle\FrontofficeBundle\Service;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Azimut\Bundle\FrontofficeBundle\Entity\Menu;
use Azimut\Bundle\FrontofficeBundle\Entity\Page;
use Azimut\Bundle\FrontofficeBundle\Entity\PageLink;
use Azimut\Bundle\FrontofficeBundle\Entity\PagePlaceholder;
use Symfony\Bridge\Doctrine\RegistryInterface;

class MenuBuilder
{
    private $factory;
    /**
     * @var RegistryInterface
     */
    private $registry;

    public function __construct(FactoryInterface $factory, RegistryInterface $registry)
    {
        $this->factory = $factory;
        $this->registry = $registry;
    }

    /**
     * Adds menu and children pages to a Knp MenuItem.
     */
    public function buildKnpMenu(Menu $menu, $locale)
    {
        $menuItem = $this->factory->createItem('root');
        //$menuItem->setChildrenAttribute('class', 'nav navbar-nav');

        $pages = $this->registry->getRepository(Page::class)->findActiveByMenuWithTranslations($menu, $locale);

        $this->addChildrenItems($menuItem, $pages, $locale);

        return $menuItem;
    }

    public function buildKnpSubMenu(Page $page, $locale, $forceShowPages = false)
    {
        $menuItem = $this->factory->createItem('root');
        $subPages = $forceShowPages ? $page->getActiveChildrenPages($locale) : $page->getActiveChildrenPagesShownInMenu($locale);
        $this->addChildrenItems($menuItem, $subPages, $locale);

        return $menuItem;
    }

    /**
     * @param ItemInterface $menuItem
     * @param Page[]        $pages
     * @param string        $locale
     */
    public function addChildrenItems(ItemInterface $menuItem, array $pages, $locale)
    {
        if (count($pages)) {
            $menuItem->setAttribute('dropdown', true);
        }

        foreach ($pages as $page) {
            $path = ltrim($page->getFullSlug(), '/');

            $options = [
                'label' => $page->getMenuTitle()
            ];

            //Si la page est de type lien et que la destinatation est en dehors du site alors on ouvre dans une nouvelle fenetre/onglet
            if ($page instanceof PageLink){
                $domainLink = parse_url($page->getUrl(), PHP_URL_HOST);
                if($domainLink
                    && $page->getSite()->getMainDomainName() != $domainLink
                    && !in_array($domainLink,$page->getSite()->getSecondaryDomainNames()->toArray())
                ){
                    $options = array_merge($options,
                        array('linkAttributes' => array('target' => '_blank'))
                    );
                }
            }

            // do not include route for placeholder type
            if (!$page instanceof PagePlaceholder) {
                $options = array_merge($options, [
                    'route' => 'azimut_frontoffice',
                    'routeParameters' => array('path' => $path),
                ]);
            } else {
                $options['uri'] = '#';
            }

            $sub = $menuItem->addChild('menu_page_' . $page->getId(), $options);

            $this->addChildrenItems($sub, $page->getActiveChildrenPagesShownInMenu($locale), $locale);
        }
    }
}
