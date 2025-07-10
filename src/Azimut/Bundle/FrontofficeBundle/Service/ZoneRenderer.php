<?php
/**
 * Created by mikaelp on 11-Jul-18 5:30 PM
 */

namespace Azimut\Bundle\FrontofficeBundle\Service;

use Azimut\Bundle\CmsBundle\Entity\CmsFile;
use Azimut\Bundle\FrontofficeBundle\Entity\Page;
use Azimut\Bundle\FrontofficeBundle\Entity\PageAlias;
use Azimut\Bundle\FrontofficeBundle\Entity\Zone;
use Azimut\Bundle\FrontofficeBundle\Entity\ZoneDefinitionCmsFileBufferForm;
use Azimut\Bundle\FrontofficeBundle\Entity\ZoneDefinitionCmsFiles;
use Azimut\Bundle\FrontofficeBundle\Entity\ZoneDefinitionForm;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Templating\EngineInterface;

class ZoneRenderer
{
    protected static $defaultPaginitionLimit = 10;

    /**
     * @var RegistryInterface
     */
    private $em;
    /**
     * @var PaginatorInterface
     */
    private $paginator;
    /**
     * @var RequestStack
     */
    private $requestStack;
    /**
     * @var RouterInterface
     */
    private $router;
    /**
     * @var EngineInterface
     */
    private $engine;
    /**
     * @var HttpKernelInterface
     */
    private $kernel;

    public function __construct(RegistryInterface $em, PaginatorInterface $paginator, RequestStack $requestStack, RouterInterface $router, EngineInterface $engine, HttpKernelInterface $kernel)
    {
        $this->em = $em;
        $this->paginator = $paginator;
        $this->requestStack = $requestStack;
        $this->router = $router;
        $this->engine = $engine;
        $this->kernel = $kernel;
    }

    public function renderZone(Page $page, $zoneName, $pagePath, array $zoneOptions = [])
    {
        $zoneOptions = array_merge([
            'templateName' => 'zone_default',
            'cmsFileTemplateName' => null,
            'orderCmsFilesBy' => null,
            'ignoreQueryFilters' => false,
        ], $zoneOptions);

        $hasPagination = isset($zoneOptions['pagination']);

        $zone = $this->getZoneEntity($page, $zoneName);

        $zoneDefinition = $zone->getZoneDefinition();
        if (!$zoneDefinition instanceof ZoneDefinitionCmsFiles) {
            throw new \InvalidArgumentException(sprintf('Expected zone definition of class "ZoneDefinitionCmsFiles" for zone named "%s", instance of "%s" given.', $zoneName, get_class(
                $zoneDefinition
            )));
        }

        $paginationLimit = $hasPagination && isset($zoneOptions['pagination']['paginationLimit']) ? $zoneOptions['pagination']['paginationLimit'] : INF;
        $zoneLimit = $zoneDefinition->getMaxAttachmentsCount() ?: INF;

        // do not allow pagination if zone limit is set
        if (INF != $zoneLimit && $hasPagination && null !== $zoneOptions['pagination']) {
            throw new \InvalidArgumentException('A zone with a fixed limit of cmsfiles cannot have pagination');
        }

        $limit = min($paginationLimit, $zoneLimit);

        $paginationNumber = ($hasPagination && isset($zoneOptions['pagination']['paginationNumber']) && INF == $zoneLimit)
            ? $zoneOptions['pagination']['paginationNumber']
            : null
        ;

        if ($hasPagination && null !== $zoneOptions['pagination'] && INF == $limit) {
            $limit = static::$defaultPaginitionLimit;
        }

        $request = $this->requestStack->getMasterRequest();

        // If zone accept only one type of cmsFile, run query on its subclass instead of CmsFile (this will decrease the number of SQL joins)
        $cmsFileClass = CmsFile::class;
        if ($zoneDefinition->getAcceptedAttachmentClasses()->count() == 1) {
            $cmsFileClass = $zoneDefinition->getAcceptedAttachmentClasses()[0];
        }

        $query = $this->em->getRepository($cmsFileClass)
            ->getQueryPublishedByZoneId(
                $zone->getId(),
                $zoneOptions['orderCmsFilesBy'],
                $request->getLocale(),
                $zoneDefinition->getAcceptedAttachmentClasses(),
                $zoneOptions['ignoreQueryFilters'] ? null : $zone->getFilters(),
                $request->query,
                $zone->getPermanentFilters()
            )
        ;

        /** @var CmsFile[] $paginatedCmsFiles */
        if(INF === $limit) {
            $paginatedCmsFiles = $query->getResult();
        }
        else {
            $paginatedCmsFiles = $this->paginator->paginate(
                $query,
                $paginationNumber ?: 1,
                (INF == $limit) ? PHP_INT_MAX : $limit
            );
        }

        // Handle cmsfile canonical path
        if ($zoneDefinition->useCanonicalCmsFilePath) {
            foreach ($paginatedCmsFiles as $cmsFile) {
                $path = $this->em->getRepository(CmsFile::class)->getCmsFileCanonicalPathInSite($cmsFile, $page->getSite(), $request->getLocale(), $request->query);
                $cmsFile->setCanonicalPath($this->router->generate('azimut_frontoffice', ['path' => $path]));
            }
        }

        $showItemCount = ($hasPagination && isset($zoneOptions['pagination']['showItemCount'])) ? $zoneOptions['pagination']['showItemCount'] : true;

        return $this->engine->render('ZoneLayout/'.$zoneOptions['templateName'].'.html.twig', [
            'zone' => $zone,
            'cmsFileTemplateName' => $zoneOptions['cmsFileTemplateName'],
            'pagePath' => $pagePath,
            'options' => isset($zoneOptions['templateOptions'])?$zoneOptions['templateOptions']:null,
            'paginatedCmsFiles' => $paginatedCmsFiles,
            'isPaginationActive' => $hasPagination,
            'showItemCount' => $hasPagination && $showItemCount,
            'requestQuery' => $request->query,
            'page' => $page,
        ]);
    }

    public function renderZoneForm(Page $page, $zoneName)
    {
        $zone = $this->getZoneEntity($page, $zoneName);

        if (!$zone) {
            throw new NotFoundHttpException(sprintf('No zone "%s" found in page with id %s.', $zoneName, $page->getId()));
        }

        $zoneDefinition = $zone->getZoneDefinition();
        if (!$zoneDefinition instanceof ZoneDefinitionForm) {
            throw new \InvalidArgumentException(sprintf('Expected zone definition of class "ZoneDefinitionForm" for zone named "%s", instance of "%s" given.', $zoneName, get_class($zoneDefinition)));
        }

        return $this->forward($zoneDefinition->getController());
    }


    public function renderZoneCmsFileBufferForm(Page $page, $zoneName)
    {
        $zone = $this->getZoneEntity($page, $zoneName);

        $zoneDefinition = $zone->getZoneDefinition();
        if (!$zoneDefinition instanceof ZoneDefinitionCmsFileBufferForm) {
            throw new \InvalidArgumentException(sprintf('Expected zone definition of class "ZoneDefinitionCmsFileBufferForm" for zone named "%s", instance of "%s" given.', $zoneName, get_class($zoneDefinition)));
        }

        return $this->forward('AzimutModerationBundle:Frontoffice:cmsFileBufferForm', [
            'class' => $zoneDefinition->getCmsFileBufferClass(),
            'targetZone' => $zoneDefinition->getTargetZone(),
        ]);
    }

    private function forward($controller, $path = [])
    {
        $request = $this->requestStack->getMasterRequest();
        $path = array_merge($path, [
            'originalRequest' => $request,
            '_forwarded' => $request->attributes,
            '_controller' => $controller,
        ]);
        $subRequest = $request->duplicate([], null, $path);

        /** @noinspection PhpUnhandledExceptionInspection */
        return $this->kernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }

    /**
     * @param Page $page
     * @param      $zoneName
     * @return Zone
     */
    private function getZoneEntity(Page $page, $zoneName) {
        $pageId = $page->getId();
        // if page is an alias, get the content of the aliased page
        if ($page instanceof PageAlias) {
            $pageId = $page->getPageContent();
        }

        $zone = $this->em->getRepository(Zone::class)->findOneByNameAndPage($pageId, $zoneName);

        if (!$zone) {
            throw new \InvalidArgumentException(sprintf('No zone "%s" found in page with id %s .', $zoneName, $page->getId()));
        }

        return $zone;
    }
}
