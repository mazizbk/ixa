<?php


namespace Azimut\Bundle\MontgolfiereAppBundle\EventSubscriber;


use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignSegment;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignSegmentStep;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\Item;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\Question;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\Theme;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

class CampaignSegmentCreateStepsSubscriber implements EventSubscriber
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
        if (!$entity instanceof CampaignSegment) {
            return;
        }

        $em = $eventArgs->getObjectManager();

        $position = 1;

        $themes = $em->getRepository(Theme::class)->findBy(['analysisVersion' => $entity->getCampaign()->getAnalysisVersion()], ['position' => 'ASC']);
        foreach ($themes as $theme) {
            if($theme->isVirtual()) {
                continue;
            }
            $em->persist(self::createStep($entity, $position++, CampaignSegmentStep::TYPE_DIVIDER, $theme));
            foreach ($theme->getItems() as $item) {
                $em->persist(self::createStep($entity, $position++, CampaignSegmentStep::TYPE_ITEM, $theme, $item));
            }
        }
    }

    public static function createStep(CampaignSegment $segment, int $position, string $type, ?Theme $theme = null, ?Item $item = null, ?Question $question = null): CampaignSegmentStep
    {
        return (new CampaignSegmentStep())
            ->setSegment($segment)
            ->setPosition($position)
            ->setType($type)
            ->setTheme($theme)
            ->setItem($item)
            ->setQuestion($question)
        ;
    }
}
