<?php

namespace Azimut\Bundle\FrontofficeBundle\Twig\Extension;

use Azimut\Bundle\FrontofficeBundle\Entity\Menu;
use Azimut\Bundle\FrontofficeBundle\Entity\Site;
use Azimut\Bundle\FrontofficeBundle\Service\MenuBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Azimut\Bundle\FrontofficeBundle\Entity\Page;
use Symfony\Component\HttpFoundation\RequestStack;

class MenuExtension extends \Twig_Extension
{
    /**
     * @var RegistryInterface
     */
    private $registry;
    /**
     * @var RequestStack
     */
    private $requestStack;
    /**
     * @var MenuBuilder
     */
    private $menuBuilder;

    public function __construct(RegistryInterface $registry, RequestStack $requestStack, MenuBuilder $menuBuilder)
    {
        $this->registry = $registry;
        $this->requestStack = $requestStack;
        $this->menuBuilder = $menuBuilder;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('menu', array($this, 'renderMenu'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('siteMenu', array($this, 'renderSiteMenu'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('submenu', array($this, 'renderSubmenu'), array('is_safe' => array('html'))),
        );
    }

    public function renderMenu($placeholder)
    {
        $host = $this->requestStack->getCurrentRequest()->getHost();
        $locale = $this->requestStack->getCurrentRequest()->getLocale();

        $menu = $this->registry
            ->getRepository(Menu::class)
            ->findOneByHostAndPlaceholder($host, $placeholder)
        ;

        if (null === $menu) {
            throw new \InvalidArgumentException(sprintf('Menu named "%s" not found', $placeholder));
        }

        return $this->menuBuilder->buildKnpMenu($menu, $locale);
    }

    public function renderSiteMenu(Site $site, $menuName)
    {
        $locale = $this->requestStack->getCurrentRequest()->getLocale();
        $menu = $this->registry->getRepository(Menu::class)->findOneBySiteAndName($site, $menuName);

        return $this->menuBuilder->buildKnpMenu($menu, $locale);
    }

    public function renderSubmenu(Page $page, $options = [])
    {
        $forceShowPages = isset($options['forceShowPages']) ? $options['forceShowPages'] : false;
        $locale = $this->requestStack->getCurrentRequest()->getLocale();

        return $this->menuBuilder->buildKnpSubMenu($page, $locale, $forceShowPages);
    }

    public function getName()
    {
        return 'azimut_menu';
    }
}
