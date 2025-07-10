<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2013-12-06 11:31:13
 */

namespace Azimut\Bundle\CmsBundle\Entity\Repository;

use Azimut\Bundle\FrontofficeBundle\Entity\AbstractZoneFilter;
use Azimut\Bundle\FrontofficeBundle\Entity\Site;
use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr;

use Azimut\Bundle\SecurityBundle\Security\SecurityAwareRepository;
use Azimut\Bundle\CmsBundle\Entity\CmsFile;
use Azimut\Bundle\FrontofficeBundle\Entity\Zone;
use Azimut\Bundle\FrontofficeBundle\Entity\ZoneFilter;
use Azimut\Bundle\FrontofficeBundle\Entity\ZonePermanentFilter;
use Azimut\Bundle\FrontofficeBundle\Entity\ZoneDefinitionCmsFiles;
use Azimut\Bundle\FrontofficeBundle\ZoneFilter\ZoneFilterBuilder;
use Symfony\Component\HttpFoundation\ParameterBag;

class CmsFileRepository extends EntityRepository implements SecurityAwareRepository
{
    public function createInstanceFromString($name)
    {
        $metadata = $this->getClassMetadata();
        $map = $metadata->discriminatorMap;

        if (!isset($map[$name])) {
            throw new \InvalidArgumentException(sprintf('No CMS file of type "%s". Available: %s', $name, implode(', ', array_keys($map))));
        }

        $class = $map[$name];

        return new $class();
    }

    public function getClassFromName($name)
    {
        $metadata = $this->getClassMetadata();
        $map = $metadata->discriminatorMap;

        if (!isset($map[$name])) {
            throw new \InvalidArgumentException(sprintf('No CMS file of type "%s". Available: %s', $name, implode(', ', array_keys($map))));
        }

        return $map[$name];
    }

    public function getNameFromClass($className)
    {
        $metadata = $this->getClassMetadata();
        $discrMap = $metadata->discriminatorMap;

        return array_search($className, $discrMap);
    }

    public function getAvailableTypes($namespace = null)
    {
        $types = array();

        foreach ($this->getClassMetadata()->discriminatorMap as $type => $class) {
            // NB: page and image is an hidden type (used as a shortcut in frontoffice app)
            if (
                (null == $namespace || strpos($class, '\\'.$namespace.'Bundle\\')) // Restrict class to the given bundel namespace
                && !strpos($class, '\\FrontofficeBundle\\')                        // Exclude hidden types (all CmsFiles from FrontofficeBundle)
                && CmsFile::class != $class                                        // Exclude root CmsFile class as we don't use it directly
            ) {
                array_push($types, array(
                    'name' => $type
                ));
            }
        }

        return $types;
    }

//     public function findPublishedOneByPathAndPage($path, $page)
//     {
//         $qb = $this
//             ->createQueryBuilder('c')
//             ->leftJoin('c.translations', 'ct')
//             ->join('AzimutFrontofficeBundle:ZoneCmsFileAttachment', 'a', 'WITH', 'a.cmsFile = c')
//             ->leftJoin('a.zone', 'z')
//             ->leftJoin('z.pageContent', 'p')
//             ->where('p = :page')
//             ->andWhere('ct.slug = :path OR c.slug = :path')
//         ;
//         $this->queryIsVisibleCmsFile($qb);

//         $qb
//             ->setParameter('path', $path)
//             ->setParameter('page', $page)
//         ;
//         return $qb->getQuery()->getOneOrNullResult();
//     }
//
    /**
     * @param string $path
     * @param Zone $zone
     * @param string locale
     *
     * @return CmsFile|null
     *
     * NB: Locale is required to apply some translatable string based zone filters
     */
    public function findPublishedOneByPathAndZoneAndLocale($path, $zone, $locale, $requestQuery = null)
    {
        $qb = $this
            ->getQueryBuilderPublishedByZoneId($zone->getId(), null, $locale, $zone->getZoneDefinition()->getAcceptedAttachmentClasses(), null, $requestQuery, $zone->getPermanentFilters(), true) // Use force left join flag to improve performances
            ->andWhere('ct.slug = :path OR c.slug = :path')
            ->setParameter('path', $path)
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findByTypeHavingValidPublicationDates($type)
    {
        return $this->createQueryBuilder('c')
            ->where($this->getFilterFindByTypeHavingValidPublicationDates('c'))
            ->setParameter('type', $type)
            ->getQuery()->getResult()
        ;
    }

    /**
     * Needs "type" parameter binded to query
     *
     * @return Expr\Base
    */
    public function getFilterFindByTypeHavingValidPublicationDates($alias) {

        return $this->getEntityManager()->getExpressionBuilder()->andX()
            ->add('
                '.$alias.'.publishStartDatetime is null AND '.$alias.'.publishEndDatetime is null
                OR
                '.$alias.'.publishEndDatetime is null AND '.$alias.'.publishStartDatetime <= CURRENT_TIMESTAMP()
                OR
                '.$alias.'.publishStartDatetime <= CURRENT_TIMESTAMP() AND '.$alias.'.publishEndDatetime > CURRENT_TIMESTAMP()
            ')
            ->add($alias.'.trashed = false')
            ->add($alias.' INSTANCE OF :type')
        ;
    }

    public function findPublishedOne($id)
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return $this->getEntityManager()
                    ->createQuery(
                /** @lang Doctrine */
                '
                SELECT c FROM '. $this->_entityName .' c
                JOIN  AzimutFrontofficeBundle:ZoneCmsFileAttachment a WITH a.cmsFile = c
                WHERE (
                    c.publishStartDatetime is null AND c.publishEndDatetime is null
                    OR
                    c.publishEndDatetime is null AND c.publishStartDatetime <= CURRENT_TIMESTAMP()
                    OR
                    c.publishStartDatetime <= CURRENT_TIMESTAMP() AND c.publishEndDatetime > CURRENT_TIMESTAMP()
                )
                AND c.trashed = false
                AND c.id = :id
            ')
            ->setParameter('id', $id)
            ->getOneOrNullResult()
        ;
    }

    private function queryIsVisibleCmsFile(QueryBuilder $qb)
    {
        $qb
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

    /**
     * handle sorting on cmsfile fields
     *
     * @param QueryBuilder $qb
     * @param string       $orderByFieldNames
     * @param string       $locale
     * @param CmsFile[]    $cmsFileSubClasses
     * @return bool
     */
    private function queryOrderByCmsFiles(QueryBuilder $qb, $orderByFieldNames, $locale, $cmsFileSubClasses)
    {
        $useDefaultOrderBy = true;

        $orderByCmsFileFields = [];
        $orderByCmsFileFieldsDirections = [];

        $em = $this->getEntityManager();

        foreach ($cmsFileSubClasses as $subClass) {
            $subClassMetaData = $em->getClassMetadata($subClass);
            $subClassTranslationMetaData = $em->getClassMetadata($subClass::getTranslationClass());

            $subClassFields = array_keys($subClassMetaData->fieldMappings);
            $subClassTranslationFields = array_keys($subClassTranslationMetaData->fieldMappings);

            $cmsFileType = $subClassMetaData->discriminatorValue;

            foreach (explode(',', $orderByFieldNames) as $orderByFieldFullName) {
                $orderByFieldFullName = trim($orderByFieldFullName);

                if (strpos($orderByFieldFullName, ' ')) {
                    $orderByFieldName = explode(' ', $orderByFieldFullName)[0];
                } else {
                    $orderByFieldName = $orderByFieldFullName;
                }

                if (!isset($orderByCmsFileFieldsDirections[$orderByFieldName])) {
                    $orderByCmsFileFieldsDirections[$orderByFieldName] = 'ASC';
                    if (strpos(strtolower($orderByFieldFullName), ' desc')) {
                        $orderByCmsFileFieldsDirections[$orderByFieldName] = 'DESC';
                    }
                }

                isset($orderByCmsFileFields[$orderByFieldName]) ?: $orderByCmsFileFields[$orderByFieldName] = null;

                // if entity as the field on wich we want to order
                if (in_array($orderByFieldName, $subClassFields)) {
                    $orderByCmsFileFields[$orderByFieldName] .= ($orderByCmsFileFields[$orderByFieldName]?', ':'').'c'.$cmsFileType.'.'.$orderByFieldName;
                }
                // if entity translation as the field on wich we want to order
                elseif (in_array($orderByFieldName, $subClassTranslationFields)) {
                    $orderByCmsFileFields[$orderByFieldName] .= ($orderByCmsFileFields[$orderByFieldName]?', ':'').'ct'.$cmsFileType.'.'.$orderByFieldName;
                }
            }
        }

        foreach ($orderByCmsFileFields as $fieldName => $orderByCmsFileField) {
            if (null != $orderByCmsFileField) {
                $qb->addSelect('COALESCE('.$orderByCmsFileField.') as HIDDEN cmsFileOrderBy'.$fieldName);
                $useDefaultOrderBy = false;
                $qb->addOrderBy('cmsFileOrderBy'.$fieldName, $orderByCmsFileFieldsDirections[$fieldName]);
            }
        }

        return $useDefaultOrderBy;
    }

    /*
     * /!\ Be carefull when using orderByFieldNames as the variable is not protected
     * againts DQL injection. Do not include unverified value submited by users.
     *
     * Caution : this method has been made public because CmsFileManager needs it, but in the future CmsFileManager should own this method
     */
    public function getQueryBuilderPublishedByZoneId($zoneId, $orderByFieldNames = null, $locale = null, $cmsFileSubClasses = null, $filters = null, $requestQuery = null, $permanentFilters = null, $forceTranslationLeftJoinMethod = false)
    {
        $em = $this->getEntityManager();
        $zone = $em->getRepository(Zone::class)->find($zoneId);

        if (!$zone->getZoneDefinition() instanceof ZoneDefinitionCmsFiles) {
            throw new \InvalidArgumentException(sprintf('Expected zone definition of class "ZoneDefinitionCmsFiles", instance of "%s" given.', get_class($zone->getZoneDefinition())));
        }

        $isZoneContentAutomatic = $zone->getZoneDefinition()->isAutoFillAttachments();
        if (true === $isZoneContentAutomatic && 0 == count($cmsFileSubClasses)) {
            throw new \InvalidArgumentException('cmsFileSubClasses is mandatory when zone content is automatic');
        }


        if (true == $forceTranslationLeftJoinMethod) {
            $translationJoinMethod = 'leftJoin';
        }
        else {
            $translationJoinMethod = (true === $zone->getZoneDefinition()->excludeUntranslatedCmsFiles()) ? 'innerJoin' : 'leftJoin';
        }

        if (null == $cmsFileSubClasses) {
            $cmsFileSubClasses = new ArrayCollection();
        }
        if (!($cmsFileSubClasses instanceof ArrayCollection)) {
            $cmsFileSubClasses = new ArrayCollection($cmsFileSubClasses);
        }

        $qb = $this
            ->createQueryBuilder('c')
            ->$translationJoinMethod('c.translations', 'ct', 'WITH', 'ct.locale = :locale')

        ;


        if (false === $isZoneContentAutomatic) {
            // Join on zone cms file attachments
            $qb
                ->join('AzimutFrontofficeBundle:ZoneCmsFileAttachment', 'a', 'WITH', 'a.cmsFile = c')
                ->join('a.zone', 'z')
                ->andWhere('z.id = :zoneId')
                ->setParameter('zoneId', $zoneId);
            ;
        } else {
            // Join on accepted CmsFile subclasses
            $qb
                ->andWhere('c INSTANCE OF :classes')
            ;

            $types = [];
            foreach ($cmsFileSubClasses as $class) {
                $types[] = $class::getCmsFileType();
            }

            $qb->setParameter('classes', $types);
        }



        $this->queryIsVisibleCmsFile($qb);

        $useDefaultOrderBy = true;

        if (null != $orderByFieldNames || $filters && $requestQuery || $permanentFilters) {
            // if cmsFile sub classes are not restricted, use all
            if ($cmsFileSubClasses->count() == 0) {
                foreach ($this->getClassMetadata()->discriminatorMap as $class) {
                    $cmsFileSubClasses->add($class);
                }
            }
            $this->addSubClassesLeftJoins($qb, $cmsFileSubClasses->toArray());
        }

        if (null != $orderByFieldNames) {
            $useDefaultOrderBy = $this->queryOrderByCmsFiles($qb, $orderByFieldNames, $locale, $cmsFileSubClasses->toArray());
        }

        if ($useDefaultOrderBy) {
            if (false === $isZoneContentAutomatic) {
                $qb->orderBy('a.displayOrder');
            } else {
                $qb->orderBy('c.publishStartDatetime', 'DESC');
            }
        }

        if ($filters && $requestQuery) {
            $this->addZoneCmsFileFilters($qb, $filters, $requestQuery, $locale, $cmsFileSubClasses);
        }
        if ($permanentFilters) {
            $this->addZoneCmsFileFilters($qb, $permanentFilters, $requestQuery, $locale, $cmsFileSubClasses);
        }

        $qb->setParameter('locale', $locale);

        return $qb;
    }

    public function getQueryPublishedByZoneId($zoneId, $orderByFieldNames = null, $locale = null, $cmsFileSubClasses = null, $filters = null, $requestQuery = null, $permanentFilters = null)
    {
        return $this->getQueryBuilderPublishedByZoneId($zoneId, $orderByFieldNames, $locale, $cmsFileSubClasses, $filters, $requestQuery, $permanentFilters)->getQuery();
    }

    /**
     * @param QueryBuilder         $qb
     * @param AbstractZoneFilter[] $filters
     * @param ParameterBag|null    $requestQuery
     * @param string               $locale
     * @param CmsFile[]            $cmsFileSubClasses
     */
    private function addZoneCmsFileFilters(QueryBuilder $qb, $filters, ParameterBag $requestQuery = null, $locale, $cmsFileSubClasses = null)
    {
        foreach ($filters as $filter) {
            // if request query contains value for this filter or filter is permament
            if ( ($filter instanceof ZoneFilter && null != $requestQuery && null != $requestQuery->get($filter->getName())) ||  $filter instanceof ZonePermanentFilter) {

                $zoneFilterBuilder = new ZoneFilterBuilder($qb, $filter, $this->getEntityManager());


                $qb = $zoneFilterBuilder->getFilteredQueryBuilder($cmsFileSubClasses, $requestQuery);
                unset($zoneFilterBuilder);
            }
        }
    }

    /**
     * @param QueryBuilder $qb
     * @param CmsFile[]    $cmsFileSubClasses
     */
    private function addSubClassesLeftJoins(QueryBuilder $qb, $cmsFileSubClasses)
    {
        $em = $this->getEntityManager();

        foreach ($cmsFileSubClasses as $subClass) {
            $subClassMetaData = $em->getClassMetadata($subClass);

            $cmsFileType = $subClassMetaData->discriminatorValue;

            $qb->leftJoin($subClass, 'c'.$cmsFileType, 'WITH', 'c.id = c'.$cmsFileType.'.id');
            $qb->leftJoin($subClass::getTranslationClass(), 'ct'.$cmsFileType, 'WITH', 'ct.id = ct'.$cmsFileType.'.id');
        }
    }

    public function findPublishedByZoneId($zoneId, $orderByFieldNames = null, $locale = null, $cmsFileSubClasses = null, $filters = null, $requestQuery = null, $permanentFilters = null)
    {
        return $this->getQueryPublishedByZoneId($zoneId, $orderByFieldNames, $locale, $cmsFileSubClasses, $filters, $requestQuery, $permanentFilters)->getResult();

    }

    public function findOnePublishedByZoneId($cmsFileId, $zoneId, $orderByFieldNames = null, $locale = null, $cmsFileSubClasses = null, $filters = null, $requestQuery = null, $permanentFilters = null)
    {
        $em = $this->getEntityManager();
        $zone = $em->getRepository(Zone::class)->find($zoneId);

        $qb = $this
            ->getQueryBuilderPublishedByZoneId($zoneId, $orderByFieldNames, $locale, $cmsFileSubClasses, $filters, $requestQuery, $permanentFilters)
            ->andWhere('c.id = :cmsFileId')
        ;

        /** @noinspection PhpUnhandledExceptionInspection */
        return $qb
            ->setParameter('cmsFileId', $cmsFileId)
            ->getQuery()
            ->getOneOrNullResult()
        ;

    }

    public function findNotTrashed()
    {
        $cmsFiles = $this->getEntityManager()
            ->createQuery('SELECT c FROM '. $this->_entityName .' c WHERE c.trashed = false')
            ->getResult()
        ;

        // Exclude special types handled by Frontoffice bundle
        $cmsFiles = array_filter($cmsFiles, function ($cmsFile) {
            $cmsFileClass = get_class($cmsFile);
            // Detect Doctrine Proxies
            if ($cmsFile instanceof \Doctrine\ORM\Proxy\Proxy) {
                $cmsFileClass = $this->getEntityManager()->getClassMetadata($cmsFileClass)->name;
            }

            return 0 === strpos(get_class($cmsFile), CmsFile::class);
        });

        return $cmsFiles;
    }

    public function findNotTrashedByType($type)
    {
        return $this->findNotTrashedByTypes([$type]);
    }

    public function findNotTrashedByTypes(array $types)
    {
        return $this->getEntityManager()
            ->createQuery(/** @lang Doctrine */'SELECT c FROM '. $this->_entityName .' c WHERE c INSTANCE OF :types AND c.trashed = false')
            ->setParameter('types', $types)
            ->getResult();
    }

    public function findOneNotTrashed($id)
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return $this->getEntityManager()
            ->createQuery('SELECT c FROM '. $this->_entityName .' c WHERE c.trashed = false and c.id = :id')
            ->setParameter('id', $id)
            ->getOneOrNullResult()
        ;
    }

    public function findTrashed()
    {
        return $this->getEntityManager()
            ->createQuery('SELECT c FROM '. $this->_entityName .' c WHERE c.trashed = true')
            ->getResult();
    }

    public function deleteTrashed()
    {
        return $this->getEntityManager()
            ->createQuery('DELETE FROM '. $this->_entityName .' c WHERE c.trashed = true')
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function findSecurityObjects($expectedClass = null)
    {
        if ($expectedClass) {
            return $this->findNotTrashedByType($this->getNameFromClass($expectedClass));
        }
        return $this->findNotTrashed();
    }

    /**
     * @param CmsFile[] $classes
     * @return array
     */
    public function findPublishedInstanceOf($classes)
    {
        $types = [];
        foreach ($classes as $class) {
            $types[] = $class::getCmsFileType();
        }

        return $this->getEntityManager()
            ->createQuery(/** @lang Doctrine */'
                SELECT c FROM '. $this->_entityName .' c
                WHERE (
                    c.publishStartDatetime is null AND c.publishEndDatetime is null
                    OR
                    c.publishEndDatetime is null AND c.publishStartDatetime <= CURRENT_TIMESTAMP()
                    OR
                    c.publishStartDatetime <= CURRENT_TIMESTAMP() AND c.publishEndDatetime > CURRENT_TIMESTAMP()
                )
                AND c.trashed = false
                AND c INSTANCE OF :classes
            ')
            ->setParameter('classes', $types)
            ->getResult()
        ;
    }

    public function findBySlugExcludingCmsFile($slug, $cmsFile)
    {
        return $this->getEntityManager()
            ->createQuery(/** @lang Doctrine */'SELECT c FROM '. $this->_entityName .' c LEFT JOIN c.translations ct WHERE (c.slug = :slug OR ct.slug = :slug) AND ct.cmsFile != :cmsFile')
            ->setParameter('slug', $slug)
            ->setParameter('cmsFile', $cmsFile)
            ->getResult()
        ;
    }

    public function findBySlug($slug)
    {
        return $this->getEntityManager()
            ->createQuery(/** @lang Doctrine */'SELECT c FROM AzimutCmsBundle:CmsFile c LEFT JOIN c.translations ct WHERE (c.slug = :slug OR ct.slug = :slug)')
            ->setParameter('slug', $slug)
            ->getResult()
            ;
    }

    public function getCmsFilesCountByType($type)
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return $this->getEntityManager()
            ->createQuery(/** @lang Doctrine */'SELECT COUNT(c.id) FROM '. $this->_entityName .' c WHERE c INSTANCE OF :type')
            ->setParameter('type', $type)
            ->getSingleScalarResult()
        ;
    }

    /**
     * @param CmsFile $cmsFile
     * @param Site $site
     * @param string $locale
     * @return null|string
     */
    public function getCmsFileCanonicalPathInSite($cmsFile, $site, $locale, ParameterBag $query = null)
    {
        $cmsFileClass = get_class($cmsFile);
        if ($cmsFile instanceof \Doctrine\ORM\Proxy\Proxy) {
            $cmsFileClass = $this->getEntityManager()->getClassMetadata($cmsFileClass)->name;
        }

        $qb = $this->getEntityManager()->createQueryBuilder()
            ->addSelect('z')
            ->from('AzimutFrontofficeBundle:Zone', 'z')
            ->leftJoin('z.pageContent', 'p')
            ->leftJoin('z.attachments', 'a')
            ->leftJoin('AzimutFrontofficeBundle:ZoneDefinitionCmsFiles', 'zd', 'WITH', 'zd = z.zoneDefinition')
            ->where('a.cmsFile = :cmsFile AND p.site = :site AND p.active = true')
            ->orderBy('zd.cmsFilePathPriority DESC, p.id')
            ->setMaxResults(1)
        ;

        /** @noinspection PhpUnhandledExceptionInspection */
        $zone = $qb->getQuery()
            ->setParameter('cmsFile', $cmsFile)
            ->setParameter('site', $site)
            ->getOneOrNullResult()
        ;

        $page = null;
        $cmsFilePathPriority = null;

        if (null != $zone) {
            $page = $zone->getPage();
            $cmsFilePathPriority = $zone->getZoneDefinition()->getCmsFilePathPriority();
        }

        // Find zones auto publishing class of cmsfile
        $candidatesQb = $this->getEntityManager()->createQueryBuilder()
            ->addSelect('z')
            ->from(Zone::class, 'z')
            ->leftJoin('z.pageContent', 'p')
            ->leftJoin('z.attachments', 'a')
            ->leftJoin('AzimutFrontofficeBundle:ZoneDefinitionCmsFiles', 'zd', 'WITH', 'zd = z.zoneDefinition')
            ->where('zd.autoFillAttachments = true AND (zd.acceptedAttachmentClasses like :class or zd.acceptedAttachmentClasses like :class2) AND p.site = :site AND p.active = true')
            ->orderBy('zd.cmsFilePathPriority DESC, p.id')
        ;

        /** @var Zone[] $candidateZones */
        $candidateZones = $candidatesQb->getQuery()
            ->setParameter('site', $site)
            ->setParameter('class', '%"'.addslashes($cmsFileClass).'"%')
            ->setParameter('class2', '%"'.addslashes('\\'.get_class($cmsFile)).'"%')
            ->getResult()
        ;

        $autoPublishZone = null;
        $autoPublishCmsFilePathPriority = null;

        foreach ($candidateZones as $candidateZone) {
            // if zone has no permanent filters, it does contain the cmsfile we are looking for
            if (!$candidateZone->hasPermanentFilters()) {
                $autoPublishZone = $candidateZone;
                break;
            }

            // check if zone contains targetted cmsfile after executing permanent filters
            if (null != $this->findOnePublishedByZoneId($cmsFile->getId(), $candidateZone->getId(), null, $locale, $candidateZone->getZoneDefinition()->getAcceptedAttachmentClasses(), null, $query, $candidateZone->getPermanentFilters())) {
                $autoPublishZone = $candidateZone;
                break;
            }
        }

        $autoPublishPage = null;
        if (null != $autoPublishZone) {
            $autoPublishPage = $autoPublishZone->getPage();
            /** @var ZoneDefinitionCmsFiles $zoneDefinition */
            $zoneDefinition = $autoPublishZone->getZoneDefinition();
            $autoPublishCmsFilePathPriority = $zoneDefinition->getCmsFilePathPriority();
        }
        // End find zones auto publishing class of cmsfile

        if (null != $autoPublishPage && ($autoPublishCmsFilePathPriority > $cmsFilePathPriority || null == $page)) {
            $page = $autoPublishPage;
        }
        if (!$page) {
            return null;
        }

        $pageFullSlug = $page->getFullSlug();

        return $pageFullSlug.($pageFullSlug ? '/' : '').$cmsFile->getSlug();
    }
}
