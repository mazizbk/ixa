<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-02-10 14:30:38
 */

namespace Azimut\Bundle\FrontofficeBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Azimut\Bundle\FrontofficeBundle\Entity\ZoneCmsFileAttachment;

class ZoneCmsFileAttachmentRepository extends EntityRepository
{
    public function findOneByZoneAndCmsFile($zoneId, $cmsFileId)
    {
        return $this->getEntityManager()
            ->createQuery('SELECT a from AzimutFrontofficeBundle:ZoneCmsFileAttachment a WHERE a.zone = :zoneId and a.cmsFile = :cmsFileId')
            ->setParameter('zoneId', $zoneId)
            ->setParameter('cmsFileId', $cmsFileId)
            ->getOneOrNullResult()
        ;
    }

    public function findOneByZoneAndDisplayOrder($zoneId, $displayOrder)
    {
        return $this->getEntityManager()
            ->createQuery('SELECT a from AzimutFrontofficeBundle:ZoneCmsFileAttachment a WHERE a.zone = :zoneId and a.displayOrder = :displayOrder')
            ->setParameter('zoneId', $zoneId)
            ->setParameter('displayOrder', $displayOrder)
            ->getOneOrNullResult()
        ;
    }

    public function getMaxDisplayOrderInZone($zoneId)
    {
        return $this->getEntityManager()
            ->createQuery('SELECT max(attachment.displayOrder) from AzimutFrontofficeBundle:ZoneCmsFileAttachment attachment WHERE attachment.zone = :zoneId')
            ->setParameter('zoneId', $zoneId)
            ->getSingleResult()[1]
        ;
    }

    public function removeDuplicatesPublicationsInZoneCmsFileAttachments(array $zoneAttachmentResults)
    {
        // index attachments by cmsFile id
        $zoneAttachmentResultsIndexedByCmsFileId = [];
        foreach ($zoneAttachmentResults as $result) {
            $zoneAttachmentResultsIndexedByCmsFileId[$result->getCmsFile()->getId()][] = $result;
        }

        // identify duplicates
        $duplicatesZoneAttachmentResults = [];
        foreach ($zoneAttachmentResultsIndexedByCmsFileId as $results) {
            if (count($results) > 1) {
                // order results by zone priority, page id desc
                usort($results, function(ZoneCmsFileAttachment $a, ZoneCmsFileAttachment $b) {
                    $priorityDifference = $b->getZone()->getCmsFilePathPriority() - $a->getZone()->getCmsFilePathPriority();
                    if ($priorityDifference != 0) {
                        return $priorityDifference;
                    }
                    return $a->getZone()->getPage()->getId() - $b->getZone()->getPage()->getId();
                });

                // keep only duplicates
                array_shift($results);
                $duplicatesZoneAttachmentResults = array_merge($duplicatesZoneAttachmentResults, $results);
            }
        }

        // remove duplicates
        $zoneAttachmentResults = array_filter($zoneAttachmentResults, function($zoneAttachmentResult) use ($duplicatesZoneAttachmentResults) {
            return !in_array($zoneAttachmentResult, $duplicatesZoneAttachmentResults);
        });

        return $zoneAttachmentResults;
    }
}
