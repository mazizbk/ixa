<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2013-10-31
 */

namespace Azimut\Bundle\MediacenterBundle\Entity\Repository;

use Azimut\Bundle\CmsBundle\Entity\Traits\CmsFileMainAttachmentTrait;
use Azimut\Bundle\CmsBundle\Entity\Traits\CmsFileSecondaryAttachmentsTrait;
use Azimut\Bundle\CmsBundle\Entity\Traits\CmsFileComplementaryAttachment1Trait;
use Azimut\Bundle\CmsBundle\Entity\Traits\CmsFileComplementaryAttachment2Trait;
use Azimut\Bundle\CmsBundle\Entity\Traits\CmsFileComplementaryAttachment3Trait;
use Azimut\Bundle\CmsBundle\Entity\Traits\CmsFileComplementaryAttachment4Trait;
use Azimut\Bundle\FrontofficeBundle\Entity\Zone;
use Azimut\Bundle\FrontofficeBundle\Entity\ZoneDefinitionCmsFiles;
use Doctrine\ORM\EntityRepository;
use Azimut\Component\PHPExtra\TraitHelper;
use Azimut\Bundle\FrontofficeBundle\Service\SearchEngineProviderChain;
use Azimut\Bundle\CmsBundle\Entity\CmsFile;

class MediaDeclinationRepository extends EntityRepository
{
    public function createInstanceFromString($name)
    {
        $metadata = $this->getClassMetadata();
        $map = $metadata->discriminatorMap;

        if (!isset($map[$name])) {
            throw new \InvalidArgumentException(sprintf('No media declination of type "%s". Available: %s', $name, implode(', ', array_keys($map))));
        }

        $class = $map[$name];

        return new $class();
    }

    public function findOneByNameInMedia($name, $mediaId, $locale = null)
    {
        if (!$mediaId) {
            throw new \InvalidArgumentException("mediaId argument cannot be null");
        }

        $result = $this->getEntityManager()
            ->createQuery(
                'SELECT d FROM AzimutMediacenterBundle:MediaDeclination d WHERE d.media = :mediaId AND d.name = :name'
            )
            ->setParameter('mediaId', $mediaId)
            ->setParameter('name', $name)
            ->setMaxResults(1)
            ->getResult();

        if (count($result) == 0) {
            return null;
        }

        return $result[0];
    }

    public function getDiskUsage()
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT SUM(md.size) as size FROM AzimutMediacenterBundle:MediaDeclination md'
            )
            ->getSingleResult()['size']
        ;
    }

    public function getFrontofficePublicationCount($id)
    {
        $count = 0;
        $em = $this->getEntityManager();
        $cmsFileClassMetaData = $em->getClassMetadata(CmsFile::class);

        // list CmsFile subclasses using main media attachments and secondary media attachments
        $classesUsingMainAttachment = [];
        $classesUsingSecondaryAttachment = [];
        $classesUsingComplementaryAttachment1 = [];
        $classesUsingComplementaryAttachment2 = [];
        $classesUsingComplementaryAttachment3 = [];
        $classesUsingComplementaryAttachment4 = [];

        foreach ($cmsFileClassMetaData->discriminatorMap as $class) {
            if (TraitHelper::isClassUsing($class, CmsFileMainAttachmentTrait::class)) {
                $classesUsingMainAttachment[] = $class;
            }
            if (TraitHelper::isClassUsing($class, CmsFileSecondaryAttachmentsTrait::class)) {
                $classesUsingSecondaryAttachment[] = $class;
            }
            if (TraitHelper::isClassUsing($class, CmsFileComplementaryAttachment1Trait::class)) {
                $classesUsingComplementaryAttachment1[] = $class;
            }
            if (TraitHelper::isClassUsing($class, CmsFileComplementaryAttachment2Trait::class)) {
                $classesUsingComplementaryAttachment2[] = $class;
            }
            if (TraitHelper::isClassUsing($class, CmsFileComplementaryAttachment3Trait::class)) {
                $classesUsingComplementaryAttachment3[] = $class;
            }
            if (TraitHelper::isClassUsing($class, CmsFileComplementaryAttachment4Trait::class)) {
                $classesUsingComplementaryAttachment4[] = $class;
            }
        }

        // find publications as main attachment
        foreach ($classesUsingMainAttachment as $class) {
            $qb = $em->createQueryBuilder();
            $qb
                ->select($qb->expr()->count('d'))
                ->from($class, 'c')
                ->leftJoin('c.mainAttachment', 'ma')
                ->leftJoin('ma.mediaDeclination', 'd')
                ->where('d.id = :id')
                ->setParameter('id', $id)
            ;
            $count += $qb->getQuery()->getSingleScalarResult();
        }

        // find publications as secondary attachment
        foreach ($classesUsingSecondaryAttachment as $class) {
            $qb = $em->createQueryBuilder();
            $qb
                ->select($qb->expr()->count('d'))
                ->from($class, 'c')
                ->leftJoin('c.secondaryAttachments', 'ma')
                ->leftJoin('ma.mediaDeclination', 'd')
                ->where('d.id = :id')
                ->setParameter('id', $id)
            ;
            $count += $qb->getQuery()->getSingleScalarResult();
        }

        // find publications as complementary attachment
        foreach ($classesUsingComplementaryAttachment1 as $class) {
            $qb = $em->createQueryBuilder();
            $qb
                ->select($qb->expr()->count('d'))
                ->from($class, 'c')
                ->leftJoin('c.complementaryAttachment1', 'ma')
                ->leftJoin('ma.mediaDeclination', 'd')
                ->where('d.id = :id')
                ->setParameter('id', $id)
            ;
            $count += $qb->getQuery()->getSingleScalarResult();
        }
        // find publications as complementary attachment
        foreach ($classesUsingComplementaryAttachment2 as $class) {
            $qb = $em->createQueryBuilder();
            $qb
                ->select($qb->expr()->count('d'))
                ->from($class, 'c')
                ->leftJoin('c.complementaryAttachment2', 'ma')
                ->leftJoin('ma.mediaDeclination', 'd')
                ->where('d.id = :id')
                ->setParameter('id', $id)
            ;
            $count += $qb->getQuery()->getSingleScalarResult();
        }
        // find publications as complementary attachment
        foreach ($classesUsingComplementaryAttachment3 as $class) {
            $qb = $em->createQueryBuilder();
            $qb
                ->select($qb->expr()->count('d'))
                ->from($class, 'c')
                ->leftJoin('c.complementaryAttachment3', 'ma')
                ->leftJoin('ma.mediaDeclination', 'd')
                ->where('d.id = :id')
                ->setParameter('id', $id)
            ;
            $count += $qb->getQuery()->getSingleScalarResult();
        }
        // find publications as complementary attachment
        foreach ($classesUsingComplementaryAttachment4 as $class) {
            $qb = $em->createQueryBuilder();
            $qb
                ->select($qb->expr()->count('d'))
                ->from($class, 'c')
                ->leftJoin('c.complementaryAttachment4', 'ma')
                ->leftJoin('ma.mediaDeclination', 'd')
                ->where('d.id = :id')
                ->setParameter('id', $id)
            ;
            $count += $qb->getQuery()->getSingleScalarResult();
        }

        // find publications as part of the body text (search in hidden index "embeddedMediaDeclinations")
        $qb = $em->createQueryBuilder();
        $qb
            ->select($qb->expr()->count('d'))
            ->from('Azimut\Bundle\CmsBundle\Entity\CmsFile', 'c')
            ->leftJoin('c.embeddedMediaDeclinations', 'd')
            ->where('d.id = :id')
            ->setParameter('id', $id)
        ;
        $count += $qb->getQuery()->getSingleScalarResult();

        return $count;
    }

    public function getFrontofficePublicationPages($mediaDeclinationId, SearchEngineProviderChain $searchEngineProviderChain = null)
    {
        $pages = [];
        $cmsFiles = [];
        $em = $this->getEntityManager();
        $cmsFileClassMetaData = $em->getClassMetadata(CmsFile::class);

        //--------------------------------------------------------------------------
        // find explicitly published cms files via zone attachments

        // list CmsFile subclasses using main media attachments and secondary media attachments
        $classesUsingMainAttachment = [];
        $classesUsingSecondaryAttachment = [];
        $classesUsingComplementaryAttachment1 = [];
        $classesUsingComplementaryAttachment2 = [];
        $classesUsingComplementaryAttachment3 = [];
        $classesUsingComplementaryAttachment4 = [];

        foreach ($cmsFileClassMetaData->discriminatorMap as $class) {
            if (TraitHelper::isClassUsing($class, CmsFileMainAttachmentTrait::class)) {
                $classesUsingMainAttachment[] = $class;
            }
            if (TraitHelper::isClassUsing($class, CmsFileSecondaryAttachmentsTrait::class)) {
                $classesUsingSecondaryAttachment[] = $class;
            }
            if (TraitHelper::isClassUsing($class, CmsFileComplementaryAttachment1Trait::class)) {
                $classesUsingComplementaryAttachment1[] = $class;
            }
            if (TraitHelper::isClassUsing($class, CmsFileComplementaryAttachment2Trait::class)) {
                $classesUsingComplementaryAttachment2[] = $class;
            }
            if (TraitHelper::isClassUsing($class, CmsFileComplementaryAttachment3Trait::class)) {
                $classesUsingComplementaryAttachment3[] = $class;
            }
            if (TraitHelper::isClassUsing($class, CmsFileComplementaryAttachment4Trait::class)) {
                $classesUsingComplementaryAttachment4[] = $class;
            }
        }

        // find cms files containing media declination

        // find publications in CmsFiles as main attachment
        foreach ($classesUsingMainAttachment as $class) {
            $qb = $em->createQueryBuilder();
            $qb
                ->select('c')
                ->from($class, 'c')
                ->leftJoin('c.mainAttachment', 'mda')
                ->leftJoin('mda.mediaDeclination', 'md')
                ->leftJoin('md.media', 'm')
                ->where('md.id = :id')
                ->andWhere('m.trashed = false')
                ->andWhere('c.trashed = false')
                ->setParameter('id', $mediaDeclinationId)
            ;
            $cmsFiles = array_merge($cmsFiles, $qb->getQuery()->getResult());
        }

        // find publications in CmsFiles as secondary attachment
        foreach ($classesUsingSecondaryAttachment as $class) {
            $qb = $em->createQueryBuilder();
            $qb
                ->select('c')
                ->from($class, 'c')
                ->leftJoin('c.secondaryAttachments', 'mda')
                ->leftJoin('mda.mediaDeclination', 'md')
                ->leftJoin('md.media', 'm')
                ->where('md.id = :id')
                ->andWhere('m.trashed = false')
                ->andWhere('c.trashed = false')
                ->setParameter('id', $mediaDeclinationId)
            ;
            $cmsFiles = array_merge($cmsFiles, $qb->getQuery()->getResult());
        }
        // find publications in CmsFiles as complementary attachment 1
        foreach ($classesUsingComplementaryAttachment1 as $class) {
            $qb = $em->createQueryBuilder();
            $qb
                ->select('c')
                ->from($class, 'c')
                ->leftJoin('c.complementaryAttachment1', 'mda')
                ->leftJoin('mda.mediaDeclination', 'md')
                ->leftJoin('md.media', 'm')
                ->where('md.id = :id')
                ->andWhere('m.trashed = false')
                ->andWhere('c.trashed = false')
                ->setParameter('id', $mediaDeclinationId)
            ;
            $cmsFiles = array_merge($cmsFiles, $qb->getQuery()->getResult());
        }
        // find publications in CmsFiles as complementary attachment 2
        foreach ($classesUsingComplementaryAttachment2 as $class) {
            $qb = $em->createQueryBuilder();
            $qb
                ->select('c')
                ->from($class, 'c')
                ->leftJoin('c.complementaryAttachment2', 'mda')
                ->leftJoin('mda.mediaDeclination', 'md')
                ->leftJoin('md.media', 'm')
                ->where('md.id = :id')
                ->andWhere('m.trashed = false')
                ->andWhere('c.trashed = false')
                ->setParameter('id', $mediaDeclinationId)
            ;
            $cmsFiles = array_merge($cmsFiles, $qb->getQuery()->getResult());
        }
        // find publications in CmsFiles as complementary attachment 3
        foreach ($classesUsingComplementaryAttachment3 as $class) {
            $qb = $em->createQueryBuilder();
            $qb
                ->select('c')
                ->from($class, 'c')
                ->leftJoin('c.complementaryAttachment3', 'mda')
                ->leftJoin('mda.mediaDeclination', 'md')
                ->leftJoin('md.media', 'm')
                ->where('md.id = :id')
                ->andWhere('m.trashed = false')
                ->andWhere('c.trashed = false')
                ->setParameter('id', $mediaDeclinationId)
            ;
            $cmsFiles = array_merge($cmsFiles, $qb->getQuery()->getResult());
        }
        // find publications in CmsFiles as complementary attachment 4
        foreach ($classesUsingComplementaryAttachment4 as $class) {
            $qb = $em->createQueryBuilder();
            $qb
                ->select('c')
                ->from($class, 'c')
                ->leftJoin('c.complementaryAttachment4', 'mda')
                ->leftJoin('mda.mediaDeclination', 'md')
                ->leftJoin('md.media', 'm')
                ->where('md.id = :id')
                ->andWhere('m.trashed = false')
                ->andWhere('c.trashed = false')
                ->setParameter('id', $mediaDeclinationId)
            ;
            $cmsFiles = array_merge($cmsFiles, $qb->getQuery()->getResult());
        }

        // find publications in CmsFiles as part of the body text (search in hidden index "embeddedMediaDeclinations")
        $qb = $em->createQueryBuilder();
        $qb
            ->select('c')
            ->from(CmsFile::class, 'c')
            ->leftJoin('c.embeddedMediaDeclinations', 'md')
            ->leftJoin('md.media', 'm')
            ->where('md.id = :id')
            ->andWhere('m.trashed = false')
            ->andWhere('c.trashed = false')
            ->setParameter('id', $mediaDeclinationId)
        ;
        $cmsFiles = array_merge($cmsFiles, $qb->getQuery()->getResult());

        if (0 == count($cmsFiles)) {
            return [];
        }


        // find zones publishing cms files

        $qb = $em->createQueryBuilder();
        $qb
            ->select('z')
            ->from(ZoneDefinitionCmsFiles::class, 'zd')
            ->leftJoin(Zone::class, 'z', 'WITH', 'z.zoneDefinition = zd')
            ->leftJoin('z.attachments', 'a')
            ->leftJoin('z.pageContent', 'p')
            ->leftJoin('p.site', 's')
            ->leftJoin('a.cmsFile', 'c')
            ->where('c IN (:cmsFiles)')
            ->andWhere('p.active = true')
            ->andWhere('s.active = true')
            ->setParameter('cmsFiles', $cmsFiles)
        ;
        /** @var Zone[] $zones */
        $zones = $qb->getQuery()->getResult();


        //--------------------------------------------------------------------------
        // find auto published cms files

        /** @var Zone[] $autoPublishedZones */
        $autoPublishedZones = $em
            ->createQueryBuilder()
            ->addSelect('z')
            ->from(ZoneDefinitionCmsFiles::class, 'zd')
            ->leftJoin(Zone::class, 'z', 'WITH', 'z.zoneDefinition = zd')
            ->leftJoin('z.pageContent', 'p')
            ->leftJoin('p.site', 's')
            ->where('zd.autoFillAttachments = true')
            ->andWhere('p.active = true')
            ->andWhere('s.active = true')
            ->getQuery()
            ->getResult()
        ;

        foreach ($autoPublishedZones as $zone) {
            $zoneDefinition = $zone->getZoneDefinition();
            if(!$zoneDefinition instanceof ZoneDefinitionCmsFiles) {
                // Just in case, should not happen
                continue;
            }
            foreach ($zoneDefinition->getAcceptedAttachmentClasses() as $class) {
                foreach ($cmsFiles as $cmsFile) {
                    if ($cmsFile instanceof $class) {
                        array_push($zones, $zone);
                        break;
                    }
                }

            }
        }


        //--------------------------------------------------------------------------
        // search in specific published content

        if ($searchEngineProviderChain && $searchEngineProviderChain->hasProviders()) {
            foreach ($searchEngineProviderChain->getProviders() as $provider) {
                if ($provider->getProvidedClass() == CmsFile::class) {
                    // at this level we don't know wich page uses the provider
                    // @TODO: find the pages using the controller represented by the provider
                    // /!\ HACK : create a public page instead
                    //array_push($pages, new PageContent());
                    break;
                }
            }
        }


        // @TODO: apply permanent zone filters
        // ...


        foreach ($zones as $zone) {
            array_push($pages, $zone->getPage());
        }


        return $pages;
    }
}
