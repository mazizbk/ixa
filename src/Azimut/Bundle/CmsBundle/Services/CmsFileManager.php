<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2019-10-10 15:37:14
 */

namespace Azimut\Bundle\CmsBundle\Services;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\RouterInterface;
use Doctrine\ORM\QueryBuilder;

use Azimut\Bundle\CmsBundle\Entity\CmsFile;
use Azimut\Bundle\FrontofficeBundle\Entity\Zone;
use Azimut\Bundle\FrontofficeBundle\Entity\ZoneCmsFileAttachment;
use Azimut\Bundle\FrontofficeBundle\Entity\ZoneDefinitionCmsFiles;
use Azimut\Bundle\FrontofficeBundle\ZoneFilter\ZoneFilterBuilder;
use Azimut\Bundle\FrontofficeBundle\Service\SearchEngineProviderChain;

// Note: CmsFile manager should be refactored with CmsFileRepository to act as a intermediate service)

class CmsFileManager
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var array
     */
    private $locales;

    private $searchEngineProviderChain;

    public function __construct(EntityManagerInterface $entityManager, RouterInterface $router, $locales, SearchEngineProviderChain $searchEngineProviderChain)
    {
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->locales = $locales;
        $this->searchEngineProviderChain = $searchEngineProviderChain;
    }

    /**
     * Get CmsFile publications
     * @param  CmsFile $cmsFile
     * @return array
     */
    public function getCmsfilePublications(CmsFile $cmsFile)
    {
        // Find zones explicitely publishing cms file
        $zones = $this->getZonePublishingCmsFileQueryBuilder()
            ->setParameter('cmsFile', $cmsFile)
            ->getQuery()
            ->getResult()
        ;

        // Find zones auto publishing cms file (and merge results)
        $autoZones = $this->getZoneAutoPublishingCmsFileQueryBuilder()
           ->setParameter('className', '%'.str_replace('\\', '\\\\', get_class($cmsFile)).'%')
           ->getQuery()
           ->getResult()
        ;

        $autoZones = $this->applyPermanentFiltersOnZonesForCmsFile($autoZones, $cmsFile);

        $zones = array_merge($zones, $autoZones);

        // Page having standalone router publishing CmsFile
        $standaloneRouterPageZones = $this->getStandaloneRouterVirtualZonesPublishingCmsFile($cmsFile);

        $zones = array_merge($zones, $standaloneRouterPageZones);

        $results = [];
        foreach ($zones as $zone) {
            $results[] = [
                'site_name' => $zone->getPage()->getSite()->getName(),
                'page_name' => $zone->getPage()->getName(),
                'page_id' => $zone->getPage()->getId(),
                'zone_name' => $zone->getName(),
                'zone_id' => $zone->getId(),
                'page_url' => $zone->getPage()->getSite()->getUri().$this->router->generate('azimut_frontoffice', ['path' => $zone->getPage()->getFullSlug()]),
            ];
        }

        return $results;
    }

    /**
     * Get CmsFile publications
     * @param  CmsFile $cmsFile
     * @return array
     */
    public function getCmsfilePublicationsCount(CmsFile $cmsFile)
    {
        // Find zones explicitely publishing cms file
        $zonesCount = $this->getZonePublishingCmsFileQueryBuilder()
            ->select('count(z.id)')
            ->setParameter('cmsFile', $cmsFile)
            ->getQuery()
            ->getSingleScalarResult()
        ;


        // Find zones auto publishing cms file (and merge results)

        // $zonesCount += $this->getZoneAutoPublishingCmsFileQueryBuilder()
        //     ->select('count(z.id)')
        //     ->setParameter('className', '%'.str_replace('\\', '\\\\', get_class($cmsFile)).'%')
        //     ->getQuery()
        //     ->getSingleScalarResult()
        // ;

        // To simplify support of zone filters, we fetch them before executing filters on each

        $autoZones = $this->getZoneAutoPublishingCmsFileQueryBuilder()
           ->setParameter('className', '%'.str_replace('\\', '\\\\', get_class($cmsFile)).'%')
           ->getQuery()
           ->getResult()
        ;

        $autoZones = $this->applyPermanentFiltersOnZonesForCmsFile($autoZones, $cmsFile);
        $zonesCount += count($autoZones);

        // Page having standalone router publishing CmsFile

        $standaloneRouterPageZones = $this->getStandaloneRouterVirtualZonesPublishingCmsFile($cmsFile);
        $zonesCount += count($standaloneRouterPageZones);

        return $zonesCount;
    }

    /**
         * Get CmsFile publications from id
         * @param  int $id
         * @return array
         */
        public function getCmsfilePublicationsByCmsFileId($id)
        {
            return $this->getCmsfilePublicationsCount($this->entityManager->getRepository(CmsFile::class)->find($id));
        }

    /**
     * get Zone publishing CmsFile QueryBuilder
     * @return QueryBuilder
     */
    private function getZonePublishingCmsFileQueryBuilder()
    {
        $qb = $this->entityManager
            ->createQueryBuilder()
            ->select('z')
            ->from(Zone::class, 'z')
            ->leftJoin(ZoneCmsFileAttachment::class, 'a', 'WITH', 'a.zone = z')
            ->leftJoin('a.cmsFile', 'c')
            ->where('c = :cmsFile')

            // restrict to active page and active site
            ->leftJoin('z.pageContent', 'p')
            ->andWhere('p.active = true')
            ->leftJoin('p.site', 's')
            ->andWhere('s.active = true')
        ;

        return $qb;
    }

    /**
     * get Zone auto-publishing CmsFile QueryBuilder
     * @return QueryBuilder
     */
    private function getZoneAutoPublishingCmsFileQueryBuilder()
    {
        return $this->entityManager
            ->createQueryBuilder()
            ->select('z')
            ->from(Zone::class, 'z')
            ->join(ZoneDefinitionCmsFiles::class, 'zd', 'WITH', 'z.zoneDefinition = zd')
            ->where('zd.autoFillAttachments = true AND zd.acceptedAttachmentClasses LIKE :className')

            // restrict to active page and active site
            ->leftJoin('z.pageContent', 'p')
            ->andWhere('p.active = true')
            ->leftJoin('p.site', 's')
            ->andWhere('s.active = true')
        ;
    }

    /**
     * Build virtual zones for pages having standalone router publishing CmsFile (we use the search engine providers to find files)
     * @param  CmsFile $cmsFile
     * @return array
     */
    private function getStandaloneRouterVirtualZonesPublishingCmsFile(CmsFile $cmsFile)
    {
        $standaloneRouterPageZones = [];

        if ($this->searchEngineProviderChain->hasProviders()) {

            // Create empty virtual zone definition to hold results
            $virtualZoneDefinition = new ZoneDefinitionCmsFiles('');
            foreach ($this->searchEngineProviderChain->getProviders() as $key => $provider) {
                if ($provider->getProvidedClass() == CmsFile::class) {

                    // Find if this provider publish our cmsFile
                    $qb = $provider->getQueryBuilder('c')
                        ->select('COUNT(c.id)')
                        ->andWhere('c = :cmsFile')
                        ->setParameter('cmsFile', $cmsFile)
                    ;

                    if($qb->getQuery()->getSingleScalarResult() > 0) {
                        foreach ($provider->getPublishingPageContents() as $page) {
                            $standaloneRouterPageZones[] = new Zone([
                                'page_content' => $page,
                                'zone_definition' => $virtualZoneDefinition,
                            ]);
                        }
                    }
                }
            }
        }

        return $standaloneRouterPageZones;
    }

    /**
     * Apply permanent filters on zones
     * @param  array  $zones
     * @return array
     */
    private function applyPermanentFiltersOnZonesForCmsFile(array $zones, CmsFile $cmsFile)
    {
        // Execute permanent filters on auto publish zones
        foreach ($zones as $zoneKey => $zone) {
            if ($zone->hasPermanentFilters()) {
                $cmsFileSubClasses = $zone->getZoneDefinition()->getAcceptedAttachmentClasses();
                if (0 == count($cmsFileSubClasses)) {
                    throw new \InvalidArgumentException('cmsFileSubClasses is mandatory when zone content is automatic');
                }

                $cmsFileFulfilFilters = false;

                // Execute filters for each locale
                foreach ($this->locales as $locale) {
                    // Apply filters on zone, restricting to given CmsFile
                    $qb = $this->entityManager->getRepository(CmsFile::class)
                        ->getQueryBuilderPublishedByZoneId($zone->getId(), null, $locale, $cmsFileSubClasses, null, null, $zone->getPermanentFilters())
                    ;
                    $qb
                        ->select('COUNT(c.id)')
                        ->andWhere('c = :cmsFile')
                        ->setParameter('cmsFile', $cmsFile)
                    ;

                    // If our cmsfile is still in result, it fulfils filters
                    if($qb->getQuery()->getSingleScalarResult() > 0) {
                        $cmsFileFulfilFilters = true;
                        continue;
                    }
                }

                // Remove the zone from results if cmsfile doesn't fulfil filters
                if (false === $cmsFileFulfilFilters) {
                    unset($zones[$zoneKey]);
                }
            }
        }

        return $zones;
    }
}
