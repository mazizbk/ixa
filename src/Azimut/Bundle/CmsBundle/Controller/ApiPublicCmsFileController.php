<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:    2016-08-26 09:45:54
 */

namespace Azimut\Bundle\CmsBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use JMS\Serializer\SerializationContext;

use Azimut\Bundle\DoctrineExtraBundle\Translation\TranslationProxy;
use Azimut\Bundle\CmsBundle\Entity\CmsFile;
use Azimut\Bundle\FrontofficeBundle\Entity\Zone;
use Azimut\Bundle\FrontofficeBundle\Entity\ZoneDefinitionCmsFiles;

class ApiPublicCmsFileController extends FOSRestController
{
    protected static $rootPropertySingleName = 'cmsFile';
    protected static $rootPropertyPluralName = 'cmsFiles';

    /**
     * Get all action
     * @return array
     *
     * @Rest\View(serializerGroups={"always", "public_list_cms_file", "public_list_media_declination_attachment"})
     *
     * @ApiDoc(
     *  section="CMS",
     *  resource=true,
     *  description="CMS : Get all public cms files (published on a web page zone)"
     * )
     * @QueryParam(
     *  name="locale", requirements="[a-z]{2}|all", strict=true, nullable=true,
     *  description="language (ex: 'en')"
     * )
     * @QueryParam(
     *  name="zoneId", requirements="\d+",
     *  description="page zone id in wich to look for cms files"
     * )
     */
    public function getCmsfilesAction(Request $request, $zoneId = null, $locale = null)
    {
        if (!$zoneId) {
            throw new HttpException(400, "Please specify a zone id filter.");
        }

        if ($locale) {
            TranslationProxy::setDefaultLocale($locale);
        }
        else {
            $locale = TranslationProxy::getDefaultLocale();
        }

        $em = $this->getDoctrine()->getManager();
        $zone = $em->getRepository(Zone::class)->find($zoneId);

        if (!$zone) {
            throw $this->createNotFoundException('Unable to find zone '.$zoneId);
        }

        if (!$zone->getZoneDefinition() instanceof ZoneDefinitionCmsFiles) {
            throw new HttpException(400, sprintf('Expected zone definition of class "ZoneDefinitionCmsFiles", instance of "%s" given.', get_class($zone->getZoneDefinition())));
        }

        //$this->denyAccessUnlessGranted('view', $zone->getPage());

        $hasStandaloneCmsfilesRoutes = $zone->getZoneDefinition()->hasStandaloneCmsfilesRoutes();

        $cmsFiles = $this->getZoneCmsFiles($zoneId, $locale, false, $request->query);
        if ($request->query->has('max')) {
            $cmsFiles = array_slice($cmsFiles,0,$request->query->get('max'));
        }
        // transform entities to arrays

        $serializationContext = new SerializationContext();
        $serializationContext->setGroups(["always", "public_list_cms_file", "public_list_media_declination_attachment"]);

        $serializer = $this->get('jms_serializer');
        $cmsFilesArray = $serializer->toArray($cmsFiles, $serializationContext);

        // build cmsFiles URL
        foreach ($cmsFilesArray as $key => $cmsFile) {
            // handle cmsfile canonical path
            if ($zone->getZoneDefinition()->useCanonicalCmsFilePath) {
                $canonicalPath = $em->getRepository(CmsFile::class)
                    ->getCmsFileCanonicalPathInSite($cmsFiles[$key], $zone->getPageContent()->getSite(), $locale, $request->query)
                ;
                $cmsFiles[$key]->setCanonicalPath($canonicalPath);
            }
            else if ($hasStandaloneCmsfilesRoutes) {
                $canonicalPath = self::getCmsFileUrl($cmsFiles[$key], $zone);
            }
            else {
                $canonicalPath = $zone->getPage()->getFullSlug();
            }

            $cmsFilesArray[$key]['url'] = $this->generateUrl('azimut_frontoffice', ['path' => $canonicalPath], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        return [
            static::$rootPropertyPluralName => $cmsFilesArray,
        ];
    }

    /**
     * Get action
     * @var integer $id Id of the cms file
     * @return array
     *
     * @Rest\View(serializerGroups={"always", "public_detail_cms_file", "public_detail_media_declination_attachment"})
     *
     * @ApiDoc(
     *  section="CMS",
     *  description="CMS: Get a public cms file"
     * )
     * @QueryParam(
     *  name="locale", requirements="[a-z]{2}|all", strict=true, nullable=true,
     *  description="language (ex: 'en')"
     * )
     */
    public function getCmsfileAction($id, $locale=null)
    {
        TranslationProxy::setDefaultLocale($locale);

        $em = $this->getDoctrine()->getManager();

        $cmsFile = $em->getRepository(CmsFile::class)->findPublishedOne($id);

        if ($cmsFile && !$cmsFile->hasPublicApi()) {
            $cmsFile = null;
        }

        if (!$cmsFile) {
            throw $this->createNotFoundException('Unable to find cms file '.$id);
        }

        return array(
            static::$rootPropertySingleName => $cmsFile
        );
    }

    /**
     * @Rest\Get(requirements={"_format"="(rss|atom)"})
     * @param      $name
     * @param      $_format
     * @return Response
     */
    public function getFeedAction($name, $_format = 'rss', Request $request)
    {
        $config = $this->getParameter('azimut_cms.config');
        if(!array_key_exists('feeds', $config)) {
            throw new HttpException(503, 'No feeds are available');
        }
        if(!array_key_exists($name, $config['feeds'])) {
            throw new HttpException(404, 'The requested feed does not exist');
        }

        $feed = $config['feeds'][$name];
        $zoneId = $feed['zone_id'];
        $allowedFeedTypes = $feed['feed_types'];
        if(!in_array($_format, $allowedFeedTypes)) {
            throw new HttpException(400, 'The requested format is not available for this feed');
        }

        $locale = $request->query->get('locale');
        TranslationProxy::setDefaultLocale($locale);
        $em = $this->getDoctrine()->getManager();
        $zone = $em->getRepository(Zone::class)->find($zoneId);

        if (!$zone) {
            throw new HttpException(500, 'Unable to find zone '.$zoneId);
        }

        $data = $this->getZoneCmsFiles($zoneId, $locale, true);
        switch ($_format) {
            case 'rss':
                return $this->makeRssResponse($data, $zone, $feed, $locale);
                break;
            case 'atom':
                return $this->makeAtomResponse($data, $zone, $feed, $locale);
                break;
            default:
                throw new HttpException(503, 'Logic exception');
        }
    }

    /**
     * @todo Handle zone with automagically published CmsFiles
     * @param      $zoneId
     * @param bool $bypassPublicApi
     * @return array
     */
    private function getZoneCmsFiles($zoneId, $locale, $bypassPublicApi = false, $parameterBag = null)
    {
        $em = $this->getDoctrine()->getManager();

        $zone = $em->getRepository(Zone::class)->find($zoneId);

        // If zone accept only one type of cmsFile, run query on its subclass instead of CmsFile (this will decrease the number of SQL joins)
        $cmsFileClass = CmsFile::class;
        if ($zone->getZoneDefinition()->getAcceptedAttachmentClasses()->count() == 1) {
            $cmsFileClass = $zone->getZoneDefinition()->getAcceptedAttachmentClasses()[0];
        }
        $repository = $em->getRepository($cmsFileClass);

        $cmsFiles = $repository->findPublishedByZoneId($zoneId, null, $locale, $zone->getZoneDefinition()->getAcceptedAttachmentClasses(), $zone->getFilters(), $parameterBag, $zone->getPermanentFilters());

        foreach ($cmsFiles as $key => $cmsFile) {
            if (!$bypassPublicApi && !$cmsFile->hasPublicApi()) {
                $cmsFiles = array_splice($cmsFiles, $key, 1);
            }
        }

        return $cmsFiles;
    }

    /**
     * @param CmsFile[] $data
     * @param Zone      $zone
     * @param array     $feed
     * @param           $locale
     * @return Response
     */
    private function makeRssResponse(array $data, Zone $zone, array $feed, $locale = null)
    {
        $rss = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><rss></rss>');
        $rss->addAttribute('version', '2.0');
        $pageContent = $zone->getPageContent();
        $linkBase = array_key_exists('base_url', $feed)?$feed['base_url']:$this->generateUrl('azimut_frontoffice', ['path' => '', '_locale' => $locale], UrlGeneratorInterface::ABSOLUTE_URL);

        $channel = $rss->addChild('channel');
        $channel->addChild('title', array_key_exists('title', $feed)?$feed['title']:$pageContent->getMetaTitle());
        $channel->addChild('link', $linkBase);
        $channel->addChild('description', array_key_exists('description', $feed)?$feed['description']: $pageContent->getMetaDescription());
        $channel->addChild('lastBuildDate', (new \DateTime())->format(DATE_RSS));

        foreach ($data as $cmsFile) {
            $item = $channel->addChild('item');
            $item->addChild('title', $cmsFile->getName());
            self::addCdata('description', $cmsFile->getAbstract(), $item);
            $item->addChild('link', rtrim($linkBase, '/').'/'.ltrim(self::getCmsFileUrl($cmsFile, $zone), '/'));
            $item->addChild('pubDate', $cmsFile->getPublishStartDateTime()?$cmsFile->getPublishStartDateTime()->format(DATE_RSS):null);
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'text/xml; charset=utf-8');
        $response->setContent($rss->asXML());

        return $response;
    }

    /**
     * @param CmsFile[] $data
     * @param Zone      $zone
     * @param array     $feed
     * @return Response
     */
    private function makeAtomResponse(array $data, Zone $zone, array $feed, $locale = null)
    {
        $atom = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><feed xmlns="http://www.w3.org/2005/Atom"></feed>');
        $atom->addAttribute('version', '2.0');
        $pageContent = $zone->getPageContent();
        $linkBase = array_key_exists('base_url', $feed)?$feed['base_url']:$this->generateUrl('azimut_frontoffice', ['path' => '', '_locale' => $locale], UrlGeneratorInterface::ABSOLUTE_URL);

        $atom->addChild('title', array_key_exists('title', $feed)?$feed['title']:$pageContent->getMetaTitle());
        $atom->addChild('id', $this->generateUrl('azimut_cms_api_public_get_feed', ['name' => $feed['name'], '_format' => 'atom'], UrlGeneratorInterface::ABSOLUTE_URL));
        $link = $atom->addChild('link');
        $link->addAttribute('href', $linkBase);
        $atom->addChild('subtitle', array_key_exists('description', $feed)?$feed['description']: $pageContent->getMetaDescription());
        $atom->addChild('updated', (new \DateTime())->format(DATE_ATOM));

        foreach ($data as $cmsFile) {
            $item = $atom->addChild('entry');
            $entryUrl = rtrim($linkBase, '/').'/'.ltrim(self::getCmsFileUrl($cmsFile, $zone), '/');
            $item->addChild('id', $entryUrl);
            $item->addChild('title', $cmsFile->getName());
            self::addCdata('summary', $cmsFile->getAbstract(), $item);
            $link = $item->addChild('link');
            $link->addAttribute('href', $entryUrl);
            $item->addChild('updated', $cmsFile->getPublishStartDateTime()?$cmsFile->getPublishStartDateTime()->format(DATE_ATOM):null);
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'text/xml; charset=utf-8');
        $response->setContent($atom->asXML());

        return $response;
    }

    private static function getCmsFileUrl(CmsFile $cmsFile, Zone $zone)
    {
        $hasStandaloneCmsfilesRoutes = $zone->getZoneDefinition()->hasStandaloneCmsfilesRoutes();
        $pageUrl = $zone->getPageContent()->getFullSlug();

        return $hasStandaloneCmsfilesRoutes?($pageUrl.($pageUrl ? '/' : '').$cmsFile->getSlug()):null;
    }

    private static function addCdata($name, $value, \SimpleXMLElement &$parent) {
        $child = $parent->addChild($name);

        if ($child !== NULL) {
            $child_node = dom_import_simplexml($child);
            $child_owner = $child_node->ownerDocument;
            $child_node->appendChild($child_owner->createCDATASection($value));
        }

        return $child;
    }
}
