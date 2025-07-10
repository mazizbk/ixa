<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-01-17 16:30:41
 */

namespace Azimut\Bundle\FrontofficeBundle\Service;

use Azimut\Bundle\CmsBundle\Entity\CmsFileTranslation;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\QueryBuilder;
use Azimut\Bundle\FrontofficeBundle\Entity\Site;
use Azimut\Bundle\FrontofficeBundle\Entity\Zone;
use Azimut\Bundle\FrontofficeBundle\Entity\ZoneDefinitionCmsFiles;
use Azimut\Bundle\FrontofficeBundle\Entity\ZoneCmsFileAttachment;
use Azimut\Bundle\CmsBundle\Entity\CmsFile;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class SearchEngine
{
    private $registry;
    private $searchEngineProviderChain;
    private $entities = [];
    private $stopWords = [];
    private $maxResults;
    private $authorizationChecker;

    public function __construct(RegistryInterface $registry, SearchEngineProviderChain $searchEngineProviderChain, array $options, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->registry = $registry;
        $this->searchEngineProviderChain = $searchEngineProviderChain;
        $this->entities = $options['entities'];
        $this->stopWords = $options['stop_words'];
        $this->maxResults = $options['max_results'];
        $this->authorizationChecker = $authorizationChecker;
    }

    // split keywords and remove stop words
    public function extractKeywords($searchQuery, $locale)
    {
        $searchKeywords = $this->clearStopWords(explode(' ', trim($searchQuery)), $locale);

        // ignore words with less than 3 caracters
        foreach ($searchKeywords as $key => $searchKeyword) {
            if (mb_strlen($searchKeyword) < 3) {
                array_splice($searchKeywords, $key, 1);
            }
        }

        return $searchKeywords;
    }

    public function find(Site $site, $searchQuery, $locale)
    {
        /** @var EntityManagerInterface $em */
        $em = $this->registry->getManager();

        $searchKeywords = $this->extractKeywords($searchQuery, $locale);

        // exit if no keywords found
        if (count($searchKeywords) == 0) {
            return [];
        }

        // get cms files types from classes
        $cmsFileTypes = [];
        foreach (array_keys($this->entities) as $className) {
            $cmsFileTypes[] = $em->getClassMetadata($className)->discriminatorValue;
        }

        //--------------------------------------------------------------------------
        // find explicitly published cms files via zone attachments

        // find candidates: published cms files ids (prepare subquery)
        // (query splited because of mysql 61 joins limit)
        $candidatesSubQb = $em
            ->createQueryBuilder()
            ->addSelect('cc.id')
            ->from(ZoneCmsFileAttachment::class, 'ca')
            ->leftJoin('ca.cmsFile', 'cc')
            ->leftJoin('ca.zone', 'cz')
            ->leftJoin('cz.pageContent', 'cp')
            ->leftJoin('cp.site', 'cs')
            ->where('cc INSTANCE OF :types')
            ->andWhere('cs = :site')
            ->andWhere('cp.active = true')
        ;
        $candidatesSubQb = $this->restrictQueryToPublishedCmsFiles($candidatesSubQb);


        // apply search on candidates


        // search in translated fields
        // (translated search separated because of mysql 61 joins limit, again)

        // search keywords in translation as separate subquery
        $filterTranslationKeywordsSubQb = $em
            ->createQueryBuilder()
            ->addSelect('fct.id')
            ->from('AzimutCmsBundle:CmsFileTranslation', 'fct')
        ;
        $filterTranslationKeywordsSubQbParameters = $this->addKeywordsFilterToQueryBuilder($searchKeywords, $filterTranslationKeywordsSubQb, 'fct', true);

        $qb = $em
            ->createQueryBuilder()
            ->addSelect('c.id')
            ->from(CmsFileTranslation::class, 'ct')
            ->leftJoin('ct.cmsFile', 'c')
            ->where('ct.locale = :locale')
        ;
        $qb->andWhere($qb->expr()->in('c.id', $candidatesSubQb->getDQL()));
        $qb->andWhere($qb->expr()->in('ct.id', $filterTranslationKeywordsSubQb->getDQL()));

        // bind keywords subquery params
        foreach ($filterTranslationKeywordsSubQbParameters as $name => $value) {
            $qb->setParameter($name, $value);
        }

        $foundCmsFileIds = $qb
            ->setParameter('locale', $locale)
            ->setParameter('types', $cmsFileTypes)
            ->setParameter('site', $site)
            ->setMaxResults($this->maxResults)
            ->getQuery()
            ->getResult()
        ;

        // prefilter site's active attachments in a subquery to decrease joins number
        $candidateAttachmentsSubQb = $em
            ->createQueryBuilder()
            ->addSelect('ca.id')
            ->from(ZoneCmsFileAttachment::class, 'ca')
            ->leftJoin('ca.zone', 'cz')
            ->leftJoin('cz.pageContent', 'cp')
            ->leftJoin('cp.site', 'cs')
            ->where('cs = :site')
            ->andWhere('cp.active = true')
        ;
        $candidateAttachmentsSubQb = $this->restrictQueryToPublishedCmsFiles($candidateAttachmentsSubQb);


        // search in non translated fields and find linked attachments

        // search keywords in separate subquery
        $filterKeywordsSubQb = $em
            ->createQueryBuilder()
            ->addSelect('fc.id')
            ->from(CmsFile::class, 'fc')
        ;
        $filterKeywordsSubQbParameters = $this->addKeywordsFilterToQueryBuilder($searchKeywords, $filterKeywordsSubQb, 'fc', false);

        $qb = $em
            ->createQueryBuilder()
            ->addSelect('a')
            ->from(ZoneCmsFileAttachment::class, 'a')
            ->leftJoin(CmsFile::class, 'c', 'WITH', 'a.cmsFile = c')
            ->where('c INSTANCE OF :types')
        ;
        $qb->andWhere($qb->expr()->in('c.id', $filterKeywordsSubQb->getDQL()));

        // bind keywords subquery params
        foreach ($filterKeywordsSubQbParameters as $name => $value) {
            $qb->setParameter($name, $value);
        }

        // include results from translated fields
        $qb->orWhere('c.id IN (:foundCmsFileIds)');

        // restrict to site's attachments
        $qb->andWhere($qb->expr()->in('a.id', $candidateAttachmentsSubQb->getDQL()));

        /** @var ZoneCmsFileAttachment[] $zoneAttachmentResults */
        $zoneAttachmentResults = $qb
            ->setParameter('foundCmsFileIds', $foundCmsFileIds)
            ->setParameter('types', $cmsFileTypes)
            ->setParameter('site', $site)
            ->setMaxResults($this->maxResults)
            ->getQuery()
            ->getResult()
        ;


        // apply permanent zone filter

        $filteredCmsFileIdsInZones = [];

        foreach ($zoneAttachmentResults as $key=>$zoneAttachment) {
            $zone = $zoneAttachment->getZone();

            if ($zone->hasPermanentFilters()) {
                $className = get_class($zoneAttachment->getCmsFile());

                $filteredCmsFileIdsInZones = $this->applyPermanentFiltersOnZone($zone, $className, $locale, $filteredCmsFileIdsInZones);

                // exclude cmsfile if not corresponding to filters
                if (!in_array($zoneAttachment->getCmsFile()->getId(), $filteredCmsFileIdsInZones[$zone->getId()][$className])) {
                    array_splice($zoneAttachmentResults, $key, 1);
                }
            }
        }



        //--------------------------------------------------------------------------
        // find auto published cms files

        // get list of cmsfile types auto published
        $autoPublishedClassesByZones = $em
            ->createQueryBuilder()
            ->addSelect('DISTINCT zd.acceptedAttachmentClasses')
            ->from(Zone::class, 'z')
            ->join(ZoneDefinitionCmsFiles::class, 'zd', 'WITH', 'z.zoneDefinition = zd AND zd.autoFillAttachments = true')
            ->join('z.pageContent', 'p')
            ->leftJoin('p.site', 's')
            ->where('s = :site')
            ->andWhere('p.active = true')
            ->setParameter('site', $site)
            ->getQuery()
            ->getResult()
        ;


        // flatten results
        $autoPublishedClasses = [];
        foreach ($autoPublishedClassesByZones as $result) {
            $autoPublishedClasses = array_merge($autoPublishedClasses, $result['acceptedAttachmentClasses']->toArray());
        }
        $autoPublishedClasses = array_unique($autoPublishedClasses);

        // convert classes to cmsfile types
        $autoPublishedCmsFileTypes = [];

        // find zones publishing each cms file type having active page
        $autoPublishedCmsFileTypesZones = [];
        foreach ($autoPublishedClasses as $className) {
            $cmsFileType = $em->getClassMetadata($className)->discriminatorValue;
            $autoPublishedCmsFileTypes[] = $cmsFileType;
            $autoPublishedCmsFileTypesZones[$cmsFileType] = $em
                ->createQueryBuilder()
                ->addSelect('DISTINCT z')
                ->from(Zone::class, 'z')
                ->leftJoin('z.pageContent', 'p')
                ->leftJoin(ZoneDefinitionCmsFiles::class, 'zd', 'WITH', 'z.zoneDefinition = zd')
                ->where('p.active = true')
                ->andWhere('zd.autoFillAttachments = true AND zd.acceptedAttachmentClasses LIKE :className')
                ->leftJoin('p.site', 's')
                ->andWhere('s = :site')
                ->setParameter('site', $site)
                ->setParameter('className', '%'.str_replace('\\', '\\\\', $className).'%')
                ->getQuery()
                ->getResult()
            ;
        }


        // search in translated fields
        // (translated search separated because of mysql 61 joins limit, again)

        $qb = $em
            ->createQueryBuilder()
            ->addSelect('c.id')
            ->from(CmsFileTranslation::class, 'ct')
            ->leftJoin('ct.cmsFile', 'c')
            ->where('c INSTANCE OF :autoPublishedCmsFileTypes')
            ->andWhere('ct.locale = :locale')
        ;
        $qb = $this->restrictQueryToPublishedCmsFiles($qb);
        $qb->andWhere($qb->expr()->in('ct.id', $filterTranslationKeywordsSubQb->getDQL()));

        // bind keywords subquery params
        foreach ($filterTranslationKeywordsSubQbParameters as $name => $value) {
            $qb->setParameter($name, $value);
        }

        $foundCmsFileIds = $qb
            ->setParameter('locale', $locale)
            ->setParameter('autoPublishedCmsFileTypes', $autoPublishedCmsFileTypes)
            ->setMaxResults($this->maxResults)
            ->getQuery()
            ->getResult()
        ;


        // search in non translated fields
        $qb = $em
            ->createQueryBuilder()
            ->addSelect('c')
            ->from(CmsFile::class, 'c')
            ->where('c INSTANCE OF :autoPublishedCmsFileTypes')
        ;
        $qb = $this->restrictQueryToPublishedCmsFiles($qb);

        $qb->andWhere($qb->expr()->in('c.id', $filterKeywordsSubQb->getDQL()));

        // bind keywords subquery params
        foreach ($filterKeywordsSubQbParameters as $name => $value) {
            $qb->setParameter($name, $value);
        }

        // include results from translated fields
        $qb->orWhere('c.id IN (:foundCmsFileIds)');


        /** @var CmsFile[] $foundCmsFiles */
        $foundCmsFiles = $qb
            ->setParameter('autoPublishedCmsFileTypes', $autoPublishedCmsFileTypes)
            ->setParameter('foundCmsFileIds', $foundCmsFileIds)
            ->setMaxResults($this->maxResults)
            ->getQuery()
            ->getResult()
        ;

        $virtualCmsFileTypesZones = [];


        // create a virtual zone on each page having auto-published content

        // prepare shared zone definition
        $virtualZoneDefinition = new ZoneDefinitionCmsFiles('virtual');

        foreach ($autoPublishedCmsFileTypesZones as $type => $zones) {
            $virtualCmsFileTypesZones[$type] = [];
            /** @var Zone $zone */
            foreach ($zones as $zone) {
                $zoneDefinition = $virtualZoneDefinition;

                if ($zone->hasPermanentFilters()) {
                    $zoneDefinition = new ZoneDefinitionCmsFiles('virtual_'.$zone->getId());
                    $zoneDefinition->setPermanentFilters($zone->getPermanentFilters());
                    $zoneDefinition->setCmsFilePathPriority($zone->getCmsFilePathPriority());
                }

                $virtualCmsFileTypesZones[$type][] = new Zone([
                    'page_content' => $zone->getPageContent(),
                    'zone_definition' => $zoneDefinition,
                ]);
            }
        }

        $filteredCmsFileIdsInZones = [];

        // create virtual attachment for each auto-published cms file for each possible page
        foreach ($foundCmsFiles as $foundCmsFile) {
            // for each zone accepting cms file type
            foreach ($virtualCmsFileTypesZones[$foundCmsFile->getCmsFileType()] as $zone) {
                // apply permanent zone filter
                if ($zone->hasPermanentFilters()) {
                    $className = get_class($foundCmsFile);
                    $originalZoneId = str_replace('virtual_', '', $zone->getZoneDefinition()->getName());

                    $filteredCmsFileIdsInZones = $this->applyPermanentFiltersOnZone($zone,$className, $locale, $filteredCmsFileIdsInZones);

                    // exclude cmsfile if not corresponding to filters
                    if (!in_array($foundCmsFile->getId(), $filteredCmsFileIdsInZones[$originalZoneId][$className])) {
                        $foundCmsFile = null;
                        break;
                    }
                }

                if (null != $foundCmsFile) {
                    $zoneAttachmentResults[] = new ZoneCmsFileAttachment($zone, $foundCmsFile);
                }
            }
        }


        //--------------------------------------------------------------------------
        // search in specific published content

        if ($this->searchEngineProviderChain->hasProviders()) {

            foreach ($this->searchEngineProviderChain->getProviders() as $key => $provider) {
                if ($provider->getProvidedClass() == CmsFile::class) {

                    // search in translated fields

                    $qb = $em
                        ->createQueryBuilder()
                        ->addSelect('c.id')
                        ->from(CmsFileTranslation::class, 'ct')
                        ->leftJoin('ct.cmsFile', 'c')
                        ->where('ct.locale = :locale')
                    ;
                    $qb->andWhere($qb->expr()->in('ct.id', $filterTranslationKeywordsSubQb->getDQL()));

                    // bind keywords subquery params
                    foreach ($filterTranslationKeywordsSubQbParameters as $name => $value) {
                        $qb->setParameter($name, $value);
                    }

                    $providerParameters = $provider->appendExpressionToQueryBuilder($qb, 'c');

                    // bind providers query params
                    foreach ($providerParameters as $name => $value) {
                        $qb->setParameter($name, $value);
                    }

                    $foundCmsFileIds = $qb
                        ->setParameter('locale', $locale)
                        ->setMaxResults($this->maxResults)
                        ->getQuery()
                        ->getResult()
                    ;


                    // search in non translated fields

                    $qb = $em
                        ->createQueryBuilder()
                        ->addSelect('c')
                        ->from(CmsFile::class, 'c')
                    ;
                    $qb->andWhere($qb->expr()->in('c.id', $filterKeywordsSubQb->getDQL()));

                    // bind keywords subquery params
                    foreach ($filterKeywordsSubQbParameters as $name => $value) {
                        $qb->setParameter($name, $value);
                    }

                    $providerParameters = $provider->appendExpressionToQueryBuilder($qb, 'c');

                    // bind providers query params
                    foreach ($providerParameters as $name => $value) {
                        $qb->setParameter($name, $value);
                    }

                    // include results from translated fields
                    $qb->orWhere('c.id IN (:foundCmsFileIds)');

                    $foundCmsFiles = $qb
                        ->setParameter('foundCmsFileIds', $foundCmsFileIds)
                        ->setMaxResults($this->maxResults)
                        ->getQuery()
                        ->getResult()
                    ;

                    // create a virtual zone for each page
                    $pagesZones = [];
                    foreach ($provider->getPublishingPageContents($site) as $page) {

                        // clone the page because we are going to alter its path
                        // and it may be used by other specific search providers
                        $page = clone $page;

                        // append the specific router url fragment to
                        $page->setSlug($page->getSlug().'/'.$provider->getContentPath());

                        $pagesZones[] = new Zone([
                            'page_content' => $page,
                            'zone_definition' => $virtualZoneDefinition,
                        ]);
                    }

                    // create virtual attachment for each auto-published cms file for each possible page
                    foreach ($foundCmsFiles as $foundCmsFile) {
                        // for each zone accepting cms file type
                        foreach ($pagesZones as $zone) {
                            $zoneAttachmentResults[] = new ZoneCmsFileAttachment($zone, $foundCmsFile);
                        }
                    }
                }
            }
        }

        //--------------------------------------------------------------------------
        // remove unauthorized pages
        foreach ($zoneAttachmentResults as $key => $zoneAttachment) {
            if (!$this->authorizationChecker->isGranted('view', $zoneAttachment->getZone()->getPage())) {
                unset($zoneAttachmentResults[$key]);
            }
        }

        //--------------------------------------------------------------------------
        // remove duplicate publication keeping the one with highest zone priority
        $zoneAttachmentResults = $em->getRepository(ZoneCmsFileAttachment::class)
            ->removeDuplicatesPublicationsInZoneCmsFileAttachments($zoneAttachmentResults)
        ;

        //--------------------------------------------------------------------------
        // order results by cmsfile so that duplicate publications are close together
        // in reverse order to display more recent content first
        usort($zoneAttachmentResults, function(ZoneCmsFileAttachment $a, ZoneCmsFileAttachment $b) {
            return $b->getCmsFile()->getId() - $a->getCmsFile()->getId();
        });

        // limit is applied in each independent query, reapply limit now there're merged
        $zoneAttachmentResults = array_splice($zoneAttachmentResults, 0, $this->maxResults);

        return $zoneAttachmentResults;
    }

    private function clearStopWords(array $searchKeywords, $locale) {
        $array = array_filter($searchKeywords, function($keyword) use ($locale) {
            return !in_array($keyword, $this->stopWords[$locale]);
        });

        // reindex results
        return array_values($array);
    }

    private function restrictQueryToPublishedCmsFiles(QueryBuilder $qb)
    {
        return $qb
            ->andWhere('
                c.publishStartDatetime is null AND c.publishEndDatetime is null
                OR
                c.publishEndDatetime is null AND c.publishStartDatetime <= CURRENT_TIMESTAMP()
                OR
                c.publishStartDatetime <= CURRENT_TIMESTAMP() AND c.publishEndDatetime > CURRENT_TIMESTAMP()
            ')
            ->andWhere('c.trashed = false')
        ;
    }

    // append search keywords section to a query builder, eather on CmsFile subclass or a CmsFileTranslation class
    private function addKeywordsFilterToQueryBuilder(array $searchKeywords, QueryBuilder $qb, $entityAlias, $isTranslationEntity)
    {
        $em = $this->registry->getManager();
        $orXFilter = $qb->expr()->orX();

        $queryParameters = [];

        /**
         * @var CmsFile $class
         * @var string[] $fields
         */
        foreach ($this->entities as $class => $fields) {
            /** @var ClassMetadata $classMetaData */
            $classMetaData = $em->getClassMetadata($class);
            $cmsFileType = $classMetaData->discriminatorValue;
            $classFields = array_keys($classMetaData->fieldMappings);

            $joinClass = $isTranslationEntity? $class::getTranslationClass() : $class;

            $qb->leftJoin($joinClass, $entityAlias.$cmsFileType, 'WITH', $entityAlias.'.id = '.$entityAlias.$cmsFileType.'.id');

            foreach ($fields as $field) {
                // check if entity itself owns the property
                $entityHasField = in_array($field, $classFields);

                if ($isTranslationEntity && !$entityHasField || !$isTranslationEntity && $entityHasField) {
                    $andXFilter = $qb->expr()->andX();

                    foreach ($searchKeywords as $key => $searchKeyword) {
                        $bindParamName = str_replace('\\', '_', $class).$field.$key;
                        $andXFilter->add($entityAlias.$cmsFileType.'.'.$field.' LIKE :searchKeyword'.$bindParamName);
                        $queryParameters[':searchKeyword'.$bindParamName] = '%'.$searchKeyword.'%';
                    }

                    $orXFilter->add($andXFilter);
                }
            }
        }

        $qb->andWhere($orXFilter);

        return $queryParameters;
    }

    private function applyPermanentFiltersOnZone(Zone $zone, $cmsFileSubclass, $locale, $cache = [])
    {
        /** @var EntityManagerInterface $em */
        $em = $this->registry->getManager();

        $zoneId = $zone->getId();
        $isVirtualZone = false;

        // fetch original zone id for virtual ones
        if (0 === strpos($zone->getZoneDefinition()->getName(), 'virtual')) {
            $zoneId = str_replace('virtual_', '', $zone->getZoneDefinition()->getName());
            $isVirtualZone = true;
        }


        // cache similar queries (same zone, same class)
        if (!isset($cache[$zoneId])) {
            $cache[$zoneId] = [];
        }
        if (!isset($cache[$zoneId][$cmsFileSubclass])) {
            // If zone accept only one type of cmsFile, run query on its subclass instead of CmsFile (this will decrease the number of SQL joins)
            $cmsFileClass = CmsFile::class;
            if ($zone->getZoneDefinition()->getAcceptedAttachmentClasses()->count() == 1) {
                $cmsFileClass = $zone->getZoneDefinition()->getAcceptedAttachmentClasses()[0];
            }

            $filteredCmsFilesInZones = $em->getRepository($cmsFileClass)
                ->findPublishedByZoneId($zoneId, null, $locale, [$cmsFileSubclass], null, null, $zone->getPermanentFilters())
            ;

            // extract CmsFile ids only
            $cache[$zoneId][$cmsFileSubclass] = (new ArrayCollection($filteredCmsFilesInZones))->map(function(CmsFile $cmsFile) {
                return $cmsFile->getId();
            })->toArray();


        }

        return $cache;
    }
}
