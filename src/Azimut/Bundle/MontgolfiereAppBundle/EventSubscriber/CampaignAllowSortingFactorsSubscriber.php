<?php


namespace Azimut\Bundle\MontgolfiereAppBundle\EventSubscriber;

use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignAutomaticAffectation;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignSortingFactor;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignSortingFactorValue;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

/**
 * Creates required CampaignAutomaticAffectation when a new CampaignSortingFactorValue is created so that all its combination are possible by default
 */
class CampaignAllowSortingFactorsSubscriber implements EventSubscriber
{

    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
        ];
    }

    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getObject();
        if (!$entity instanceof CampaignSortingFactorValue) {
            return;
        }

        $em = $eventArgs->getObjectManager();

        $sortingFactor = $entity->getSortingFactor();
        $campaign = $sortingFactor->getCampaign();

        $sortingFactors = $campaign->getSortingFactors();
        //Compute total combination including current sorting factor. Do nothing if too many combinations
        $totalValues = $sortingFactors->map(function(CampaignSortingFactor $sortingFactor){return $sortingFactor->getValues()->toArray();})->toArray();
        $totalCombinations = self::getCombinations($totalValues);
        if (count($totalCombinations) > 100000){
            return;
        }
        //Compute new combinations excluding current sorting factor
        $sortingFactors->removeElement($sortingFactor);

        $values = $sortingFactors->map(function(CampaignSortingFactor $sortingFactor){return $sortingFactor->getValues()->toArray();})->toArray();
        $combinations = self::getCombinations($values);
        foreach ($campaign->getAllowedLanguages() as $language) {
            foreach ($combinations as $combination) {
                $affectation = new CampaignAutomaticAffectation();
                $affectation
                    ->setCampaign($campaign)
                    ->setLocale($language)
                    ->addSortingFactorValue($entity)
                ;
                foreach ($combination as $item) {
                    $affectation->addSortingFactorValue($item);
                }
                $em->persist($affectation);
            }
        }
    }

    /**
     * @param array $arrays
     * @return array|array[]
     * @see https://stackoverflow.com/a/8567199/2898156
     */
    private static function getCombinations(array $arrays): array
    {
        $result = [[]];
        foreach ($arrays as $property => $property_values) {
            $tmp = [];
            foreach ($result as $result_item) {
                foreach ($property_values as $property_value) {
                    $tmp[] = array_replace($result_item, [$property => $property_value]);
                }
            }
            $result = $tmp;
        }
        return $result;
    }

}
