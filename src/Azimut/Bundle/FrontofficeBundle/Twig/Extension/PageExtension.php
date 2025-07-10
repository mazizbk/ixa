<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:    2016-05-02 17:17:18
 */

namespace Azimut\Bundle\FrontofficeBundle\Twig\Extension;

use Azimut\Bundle\FrontofficeBundle\Entity\Page;
use Azimut\Bundle\FrontofficeBundle\Entity\PageContent;
use Azimut\Bundle\FrontofficeBundle\Entity\PageLayout;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Azimut\Bundle\FrontofficeBundle\Entity\Site;
use Azimut\Bundle\FrontofficeBundle\Service\FrontService;

class PageExtension extends \Twig_Extension
{
    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var FrontService
     */
    private $frontService;

    public function __construct(RegistryInterface $registry, RouterInterface $router, FrontService $frontService)
    {
        $this->registry = $registry;
        $this->router = $router;
        $this->frontService = $frontService;
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('pageSlug', array($this, 'getSlugLink')),
            new \Twig_SimpleFunction('pageUrl', array($this, 'getPageUrl')),
            new \Twig_SimpleFunction('homePage', array($this, 'getHomePage')),
            new \Twig_SimpleFunction('findPageByTemplate', array($this, 'findPageByTemplate'), ['needs_context' => true,]),
            new \Twig_SimpleFunction('getPagePathByLayout', [$this, 'getPagePathByLayout']),
        ];
    }

    /**
     * Get the page URL
     *
     * @param Page $page
     *
     * @return string
     */
    public function getPageUrl(Page $page)
    {
        $context = $this->router->getContext();
        $host = $context->getHost();
        $context->setHost($page->getSite()->getMainDomainName()->getName());

        $pageNetworkPath = $this->router->generate('azimut_frontoffice', [
            'path' => $page->getFullSlug(),
            '_locale' => \Locale::getDefault(),
        ], UrlGeneratorInterface::NETWORK_PATH);

        $context->setHost($host);

        return $page->getSite()->getScheme().$pageNetworkPath;
    }

    /**
     * Get the full slug of a page (relative path)
     */
    public function getSlugLink($pageId)
    {
        $page = $this->registry->getManager()
            ->getRepository(Page::class)
            ->find($pageId)
        ;

        if (null === $page) {
            return false;
        }

        return $page->getFullSlug();
    }

    /**
     * Get the home page of a site
     */
    public function getHomePage(Site $site)
    {
        return $this->registry->getManager()
            ->getRepository(Page::class)
            ->findOneActiveByPathAndSite('', $site)
        ;
    }

    /**
     * @param           $context
     * @param           $templateName
     * @param Site|null $site
     * @return PageContent
     */
    public function findPageByTemplate($context, $templateName, Site $site = null)
    {
        if(!$site) {
            if(array_key_exists('site', $context) && $context['site'] instanceof Site) {
                $site = $context['site'];
            }
            else {
                throw new \InvalidArgumentException('Missing argument site or site variable in context');
            }
        }

        $pageLayoutRepo = $this->registry->getRepository(PageLayout::class);
        $pageLayout = $pageLayoutRepo->findOneBy(['template' => $templateName,]);
        if(!$pageLayout) {
            throw new \RuntimeException('No PageLayout found with template '.$templateName);
        }

        $pageRepo = $this->registry->getRepository(PageContent::class);
        $page = $pageRepo->findOneBy([
            'site' => $site,
            'layout' => $pageLayout,
        ]);

        if(!$page) {
            throw new \RuntimeException('No page found on site #'.$site->getId().' using layout #'.$pageLayout->getId());
        }

        return $page;
    }

    public function getPagePathByLayout($layout, $site = null)
    {
        $em = $this->registry->getManager();

        $routeRefenceType = (null != $site) ? UrlGeneratorInterface::ABSOLUTE_URL : UrlGeneratorInterface::ABSOLUTE_PATH;

        if (null == $site) {
            $site = $this->frontService->getCurrentSite();
        }

        $pageLayout = $em
            ->getRepository(PageLayout::class)
            ->findBy(['template' => $layout,])
        ;
        if(!$pageLayout) {
            return null;
        }

        $page = $em
            ->getRepository(PageContent::class)
            ->findOneBy([
                'layout' => $pageLayout,
                'site' => $site,
                'active' => true
            ])
        ;
        if(!$page) {
            return null;
        }

        return $this->router->generate('azimut_frontoffice', ['path' => $page->getFullSlug()], $routeRefenceType);
    }

    public function getName()
    {
        return 'azimut_page';
    }
}
