<?php


namespace Azimut\Bundle\MontgolfiereAppBundle\Repository;


use Azimut\Bundle\MontgolfiereAppBundle\Entity\Campaign;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignAutomaticAffectation;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignSortingFactorValue;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

class CampaignAutomaticAffectationRepository extends \Doctrine\ORM\EntityRepository
{
    public function getForCampaign(Campaign $campaign): array
    {
        return $this->createQueryBuilder('aa')
            ->leftJoin('aa.sortingFactorValues', 'sfv')
            ->addSelect('sfv')
            ->where('aa.campaign = :campaign')
            ->setParameter(':campaign', $campaign)
            ->getQuery()
            ->getResult()
        ;
    }

    public function deleteIncompleteAffectations(Campaign $campaign)
    {
        $sortingFactorsCount = $campaign->getSortingFactors()->count();
        $ids = $this->createQueryBuilder('aa')
            ->join('aa.sortingFactorValues', 'sfv')
            ->where('aa.campaign = :campaign')
            ->setParameter(':campaign', $campaign)
            ->select('aa.id')
            ->groupBy('aa')
            ->having('COUNT(sfv) != '. $sortingFactorsCount)
            ->getQuery()->getResult();
        $this->createQueryBuilder('aa')
            ->delete()
            ->where('aa.id in (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->execute()
            ;
    }

    public function getSortingValuesByAutomaticAffectation(Campaign $campaign)
    {
        $affectations = $this->createQueryBuilder('aa')
            ->distinct()->join('aa.sortingFactorValues', 'sfv')
            ->join('sfv.sortingFactor', 'sf')
            ->where('aa.campaign = :campaign')
            ->setParameter(':campaign', $campaign)
            ->select('sfv.id v_id')
            ->addSelect('aa.locale')
            ->addSelect('aa.id a_id')
            ->orderBy('sf.id', 'DESC') // DESC to have locale as the first element, then array_reverse in the end
            ->getQuery()
        ;
        $result = [];
        foreach ($affectations->iterate() as $affectation) {
            $affectation = array_pop($affectation);
            if(!array_key_exists($affectation['a_id'], $result)) {
                $result[$affectation['a_id']] = [];
                $result[$affectation['a_id']][] = $affectation['locale'];
            }
            $result[$affectation['a_id']][] = $affectation['v_id'];
        }

        return array_combine(
            array_map(function(array $affectation): string {return implode('-', array_reverse($affectation));}, $result),
            array_fill(0, count($result), true)
        );
    }
}
