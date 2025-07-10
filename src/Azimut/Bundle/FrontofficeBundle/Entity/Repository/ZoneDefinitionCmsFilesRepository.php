<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-06-28 13:58:49
 */

namespace Azimut\Bundle\FrontofficeBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

use Azimut\Bundle\FrontofficeBundle\Entity\PageLayout;
use Azimut\Bundle\FrontofficeBundle\Entity\ZoneDefinitionCmsFiles;

class ZoneDefinitionCmsFilesRepository extends EntityRepository
{
    /**
     * @param PageLayout $pageLayout
     * @return ZoneDefinitionCmsFiles[]
     */
    public function findAutoFilledInPageLayout(PageLayout $pageLayout)
    {
        return $this->getEntityManager()
            ->createQuery('
                SELECT zd
                FROM AzimutFrontofficeBundle:ZoneDefinitionCmsFiles zd
                WHERE zd.layout = :pageLayout
                AND zd.autoFillAttachments = true
            ')
            ->setParameter('pageLayout', $pageLayout)
            ->getResult()
        ;
    }
}
