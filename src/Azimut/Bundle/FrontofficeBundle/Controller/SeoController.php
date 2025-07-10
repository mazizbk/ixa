<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-03-08 14:18:52
 */

namespace Azimut\Bundle\FrontofficeBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use Azimut\Bundle\CmsBundle\Entity\CmsFile;
use Azimut\Bundle\FrontofficeBundle\Entity\Page;
use Azimut\Bundle\FrontofficeBundle\Entity\Site;
use Azimut\Bundle\FrontofficeBundle\Entity\Zone;
use Azimut\Bundle\FrontofficeBundle\Entity\ZoneDefinition;
use Azimut\Bundle\FrontofficeBundle\Entity\PageContent;
use Azimut\Bundle\FrontofficeBundle\Entity\ZoneDefinitionCmsFiles;
use Azimut\Bundle\DoctrineExtraBundle\Translation\TranslationProxy;
use Azimut\Bundle\FrontofficeBundle\Service\AbstractSearchEngineProvider;

class SeoController extends AbstractFrontController
{
    /**
     * Display robots.txt for a given Site
     */
    public function robotsAction(Request $request)
    {
        $site = $this->getSite($request);

        // if requested domain name is not site's main domain, redirect
        if (null != $mainDomainRedirection = $this->getMainDomainRedirection($site, $request)) {
            return $mainDomainRedirection;
        }

        $content = "User-agent: *\n";

        $content .= 'Sitemap: '.$this->generateUrl('azimut_frontoffice_sitemap', [], UrlGeneratorInterface::ABSOLUTE_URL)."\n";

        $response = new Response($content);
        $response->headers->set('Content-Type', 'text/plain');

        return $response;
    }

    /**
     * Display sitemap for a given Site
     */
    public function sitemapAction(Request $request)
    {
        $site = $this->getSite($request);

        // if requested domain name is not site's main domain, redirect
        if (null != $mainDomainRedirection = $this->getMainDomainRedirection($site, $request)) {
            return $mainDomainRedirection;
        }

        $rootNode = new \SimpleXMLElement( '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml"></urlset>' );

        // Insert dynamic content from site
        foreach ($site->getMenus() as $menu) {
            foreach ($menu->getPages() as $page) {
                $this->buildPageUrlNode($page, $site, $rootNode);
            }
        }

        // Insert static content
        if (true === $site->isSearchEngineActive()) {
            $this->buildSearchEngineUrlNode($site, $rootNode);
        }

        // Insert content from custom subrouters (via search engine providers)
        foreach($this->get('azimut_frontoffice.search_engine_provider_chain')->getProviders() as $searchEngineProvider) {
            $this->buildSubrouterUrlNode($searchEngineProvider, $site, $rootNode);
        }

        $response = new Response($rootNode->asXML());
        $response->headers->set('Content-Type', 'xml');

        return $response;
    }

    /**
     * Add search engine URL nodes to a parent XML node
     */
    private function buildSearchEngineUrlNode(Site $site, \SimpleXMLElement $parentNode)
    {
        $mainLocale = $this->getParameter('locale');
        $locales = $this->getParameter('locales');

        // Main search engine URL
        TranslationProxy::setDefaultLocale($mainLocale);
        $mainUrl = $this->generateUrl('azimut_frontoffice', ['path' => $this->get('translator')->trans('search', [], null, $mainLocale)], UrlGeneratorInterface::ABSOLUTE_URL);

        $localizedUrls = [];
        foreach ($locales as $locale) {
            TranslationProxy::setDefaultLocale($locale);
            $localizedUrls[$locale] = $this->generateUrl('azimut_frontoffice', ['path' => $this->get('translator')->trans('search', [], null, $locale)], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        $this->buildSitemapUrlNode($parentNode, $mainUrl, $localizedUrls);
    }

    /**
     * Add page URL nodes to a parent XML node
     * Recursively adds nodes to page children (subpages and cms files)
     */
    private function buildPageUrlNode(Page $page, Site $site, \SimpleXMLElement $parentNode)
    {
        $mainLocale = $this->getParameter('locale');
        $locales = $this->getParameter('locales');

        // Main page URL
        TranslationProxy::setDefaultLocale($mainLocale);
        $mainUrl = $this->generateUrl('azimut_frontoffice', ['path' => $page->getFullSlug()], UrlGeneratorInterface::ABSOLUTE_URL);

        $priority = null;
        // Home page top priority
        if ('' == $mainUrl) {
            $priority = '1.0';
        }

        // Localized page URLs
        $localizedUrls = [];
        foreach ($locales as $locale) {
            TranslationProxy::setDefaultLocale($locale);
            $localizedUrls[$locale] = $this->generateUrl('azimut_frontoffice', ['path' => $page->getFullSlug()], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        // Add the URL node
        $this->buildSitemapUrlNode($parentNode, $mainUrl, $localizedUrls, $priority);

        // Page's Cms files
        if ($page instanceof PageContent) {
            if ($page->hasZoneHavingStandaloneCmsfilesRoutes()) {
                // Manually published CmsFiles
                foreach ($page->getZones() as $zone) {
                    if ($zone->getZoneDefinition() instanceof ZoneDefinitionCmsFiles && $zone->getZoneDefinition()->hasStandaloneCmsfilesRoutes()) {
                        foreach ($zone->getAttachments() as $cmsFileAttachement) {
                            $this->buildSitemapCmsFileUrlNode($cmsFileAttachement->getCmsFile(), $page, $site, $parentNode);
                        }
                    }
                }

                // Auto published CmsFiles
                $autoFilledZoneDefinitions = $this
                    ->getDoctrine()
                    ->getRepository(ZoneDefinitionCmsFiles::class)
                    ->findAutoFilledInPageLayout($page->getLayout())
                ;

                if (count($autoFilledZoneDefinitions) > 0) {
                    // Find accepted classes
                    $autoPublishedCmsFileClasses = [];
                    foreach ($autoFilledZoneDefinitions as $autoFilledZoneDefinition) {
                        foreach ($autoFilledZoneDefinition->getAcceptedAttachmentClasses() as $acceptedAttachmentClass) {
                            $autoPublishedCmsFileClasses[] = $acceptedAttachmentClass;
                        }
                    }
                    $acceptedAttachmentsClasses = array_unique($autoPublishedCmsFileClasses);

                    $cmsFiles = $this
                        ->getDoctrine()
                        ->getRepository(CmsFile::class)
                        ->findPublishedInstanceOf($acceptedAttachmentsClasses)
                    ;

                    foreach ($cmsFiles as $cmsFile) {
                        $this->buildSitemapCmsFileUrlNode($cmsFile, $page, $site, $parentNode);
                    }
                }
            }
        }

        // Subpages
        foreach ($page->getChildrenPages() as $subPage) {
            $this->buildPageUrlNode($subPage, $site, $parentNode);
        }
    }

    /**
     * Add cms file URL nodes to a parent XML node
     */
    private function buildSitemapCmsFileUrlNode(CmsFile $cmsFile, PageContent $page, Site $site, \SimpleXMLElement $parentNode)
    {
        $mainLocale = $this->getParameter('locale');
        $locales = $this->getParameter('locales');

        // Main cms file URL
        TranslationProxy::setDefaultLocale($mainLocale);
        $pageFullSlug = $page->getFullSlug();
        if ('' != $pageFullSlug) {
            $pageFullSlug .= '/';
        }
        $mainUrl = $this->generateUrl('azimut_frontoffice', ['path' => $pageFullSlug.$cmsFile->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL);

        // Localized cms file URLs
        $localizedUrls = [];
        foreach ($locales as $locale) {
            TranslationProxy::setDefaultLocale($locale);
            $pageFullSlug = $page->getFullSlug();
            if ('' != $pageFullSlug) {
                $pageFullSlug .= '/';
            }
            $localizedUrls[$locale] = $this->generateUrl('azimut_frontoffice', ['path' => $pageFullSlug.$cmsFile->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL);

        }

        // Add the URL node
        $this->buildSitemapUrlNode($parentNode, $mainUrl, $localizedUrls);
    }

    /**
     * Add pages with custom subrouter URLs node to a parent XML node
     */
    private function buildSubrouterUrlNode(AbstractSearchEngineProvider $searchEngineProvider, Site $site, \SimpleXMLElement $parentNode)
    {
        if ($searchEngineProvider->getProvidedClass() != CmsFile::class) {
            return;
        }

        $pages = $searchEngineProvider->getPublishingPageContents($site);

        // Process only providers attached to a page in the site
        if (count($pages) > 0) {
            $cmsFiles = $searchEngineProvider->getQueryBuilder()->getQuery()->getResult();

            // Add a route for each CMS file in each page exposing cms files standalone routes
            foreach ($pages as $page) {
                if ($page->getLayout()->hasStandaloneRouterHasStandaloneCmsfilesRoutes()) {
                    foreach ($cmsFiles as $cmsFile) {
                        $this->buildSitemapCmsFileUrlNode($cmsFile, $page, $site, $parentNode);
                    }
                }
            }
        }
    }

    /**
     * Create a sitemap URL node in a parent sitemap XML node
     */
    private function buildSitemapUrlNode(\SimpleXMLElement $parentNode, $mainUrl, array $localizedUrls, $priority = null)
    {
        $urlNode = $parentNode->addChild('url');

        // Main URL
        $urlNode->addChild('loc', $mainUrl);

        // Localized page URLs
        foreach ($localizedUrls as $locale => $localizedUrl) {
            $linkNode = $urlNode->addChild( 'xhtml:link', null, 'http://www.w3.org/1999/xhtml');
            $linkNode->addAttribute('rel', 'alternate');
            $linkNode->addAttribute('hreflang', $locale);
            $linkNode->addAttribute('href', $localizedUrl);
        }

        // URL priority
        if (null != $priority) {
            $urlNode->addChild('priority', $priority);
        }
    }
}
