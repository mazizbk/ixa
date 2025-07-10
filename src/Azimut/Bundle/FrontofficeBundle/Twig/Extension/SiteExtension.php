<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-06-01 15:24:05
 */

namespace Azimut\Bundle\FrontofficeBundle\Twig\Extension;

use Symfony\Bridge\Doctrine\RegistryInterface;

use Azimut\Bundle\FrontofficeBundle\Entity\Site;
use Azimut\Bundle\CmsBundle\Entity\CmsFile;
use Symfony\Component\HttpFoundation\RequestStack;
use Azimut\Bundle\FrontofficeBundle\Service\FrontService;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Azimut\Bundle\DoctrineExtraBundle\Translation\TranslationProxy;

class SiteExtension extends \Twig_Extension
{
    private $registry;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**

     * @var FrontService
     */
    private $frontService;

    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(RegistryInterface $registry, RequestStack $requestStack, FrontService $frontService, RouterInterface $router)
    {
        $this->registry = $registry;
        $this->requestStack = $requestStack;
        $this->frontService = $frontService;
        $this->router = $router;
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('cmsFileCanonicalPath', [$this, 'getCmsFileCanonicalPath']),
            new \Twig_SimpleFunction('site', [$this, 'getSite']),
            new \Twig_SimpleFunction('siteUrl', array($this, 'getSiteUrl')),
        ];
    }

    /**
     * Return the canonical path of a cms file in a site
     */
    public function getCmsFileCanonicalPath(CmsFile $cmsFile, Site $site = null, $locale = null)
        {
            if (null == $site) {
                $site = $this->frontService->getCurrentSite();
            }

            if (null == $locale) {
                $locale = TranslationProxy::getDefaultLocale();
            }

            $path = $this->registry->getManager()->getRepository(CmsFile::class)
                ->getCmsFileCanonicalPathInSite($cmsFile, $site, $locale, $this->requestStack->getMasterRequest() ? $this->requestStack->getMasterRequest()->query : null)
            ;

            if (null == $path) {
                return null;
            }

            return $this->router->generate('azimut_frontoffice', ['path' => $path]);
        }

    /**
     * Get site entity from id
     *
     * @param int $id
     *
     * @return Site
     */
    public function getSite($id)
    {
        return $this->registry->getManager()->getRepository(Site::class)->find($id);
    }

    /**
     * Get the site URL
     *
     * @param int $siteId
     *
     * @return string
     */
    public function getSiteUrl($siteId)
    {
        $site = $this->registry->getManager()
            ->getRepository(Site::class)
            ->find($siteId)
        ;

        if (null === $site) {
            return false;
        }

        $context = $this->router->getContext();
        $host = $context->getHost();
        $context->setHost($site->getMainDomainName()->getName());

        $siteNetworkPath = $this->router->generate('azimut_frontoffice', [
            'path' => '',
            '_locale' => \Locale::getDefault(),
        ], UrlGeneratorInterface::NETWORK_PATH);

        $context->setHost($host);

        return $site->getScheme().$siteNetworkPath;
    }

    public function getName()
    {
        return 'azimut_site';
    }
}
