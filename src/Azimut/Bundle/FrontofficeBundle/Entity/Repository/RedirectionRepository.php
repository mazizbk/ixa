<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:    2016-12-01 15:01:05
 */

namespace Azimut\Bundle\FrontofficeBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\ArrayCollection;

class RedirectionRepository extends EntityRepository
{
    public function findInSiteByAddressAndPageExcludingPage(array $criteria)
    {
        $redirections = $this->findByAddress($criteria['address']);
        $results = new ArrayCollection();

        foreach ($redirections as $redirection) {
            if ($redirection->getPage()->getId() != $criteria['page']->getId() && $redirection->getPage()->getSite() == $criteria['page']->getSite()) {
                $results->add($redirection);
            }
        }

        return $results;
    }
}
