<?php

namespace Azimut\Bundle\MontgolfiereAppBundle\Controller;

use Azimut\Bundle\MontgolfiereAppBundle\Entity\Campaign;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignParticipation;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignSegment;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignSegmentStep;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\Question;
use Azimut\Bundle\MontgolfiereAppBundle\Form\Type\CampaignSegmentStepType;
use Azimut\Bundle\MontgolfiereAppBundle\Util\ThemesManager;
use JMS\Serializer\SerializerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

class BackofficeCampaignsSegmentsController extends AbstractBackofficeSubEntityController
{
    protected static $parentClass = Campaign::class;
    protected static $parentPropertyName = 'segments';
    protected static $subEntityClass = CampaignSegment::class;
    protected static $subEntityPropertyName = 'campaign';
    protected static $listView = '@AzimutMontgolfiereApp/Backoffice/Campaigns/segments.html.twig';
    protected static $createView = '@AzimutMontgolfiereApp/Backoffice/Campaigns/segments_new.html.twig';
    protected static $updateView = '@AzimutMontgolfiereApp/Backoffice/Campaigns/segments_new.html.twig';
    protected static $routesPrefix = 'azimut_montgolfiere_app_backoffice_campaigns_segments';
    protected static $translationPrefix = 'montgolfiere.backoffice.campaigns.segments';
    protected static $parentRouteParamName = 'id';
    protected static $parentRouteParamValue = 'id';
    protected static $subEntityRouteParamName = 'segment';
    protected static $subEntityRouteParamValue = 'id';
    protected static $xhrListSerializationGroups = ['backoffice_segments_list'];
    protected static $disableSoftdeleteable = true;

    /**
     * @var ThemesManager
     */
    protected $themesManager;

    public function __construct(
        RouterInterface $router,
        TranslatorInterface $translator,
        PropertyAccessorInterface $propertyAccessor,
        PaginatorInterface $paginator,
        SerializerInterface $serializer,
        ThemesManager $themesManager
    ) {
        parent::__construct($router, $translator, $propertyAccessor, $paginator, $serializer);
        $this->themesManager = $themesManager;
    }


    /**
     * @param Campaign        $entity
     * @param CampaignSegment $subEntity
     * @return bool
     */
    protected function subEntityBelongsToEntity($subEntity, $entity)
    {
        return $entity->getId() === $subEntity->getCampaign()->getId();
    }

    protected function getListAdditionalViewParameters($entity)
    {
        assert($entity instanceof Campaign);
        $em = $this->getDoctrine()->getRepository(CampaignParticipation::class);
        $rawCounts = $em->createQueryBuilder('cp')
            ->select('s.id, COUNT(cp) AS nb')
            ->leftJoin('cp.segment', 's')
            ->where('s.campaign = :campaign')
            ->setParameter(':campaign', $entity)
            ->groupBy('s.id')
            ->getQuery()
            ->getArrayResult()
        ;
        $counts = [];
        foreach ($rawCounts as $rawCount) {
            $counts[$rawCount['id']] = $rawCount['nb'];
        }

        return [
            'participations_counts' => $counts,
        ];
    }

    public function getThemesAction(Campaign $campaign)
    {
        $themes = $this->themesManager->getThemes($campaign->getAnalysisVersion());

        return $this->serialize($themes);
    }

    public function getQuestionsAction(Campaign $campaign)
    {
        $questions = $this->getDoctrine()->getRepository(Question::class)->findBy(['analysisVersion' => $campaign->getAnalysisVersion()]);

        return $this->serialize($questions);
    }

    public function createStepAction(Campaign $campaign, CampaignSegment $segment, Request $request)
    {
        $step = new CampaignSegmentStep();
        $step->setSegment($segment);
        $form = $this->createForm(CampaignSegmentStepType::class, $step);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($step);
            $em->flush();

            return $this->serialize($step, self::$xhrListSerializationGroups);
        }

        return $form;
    }

    public function updateStepAction(Campaign $campaign, CampaignSegment $segment, CampaignSegmentStep $step, Request $request)
    {
        $form = $this->createForm(CampaignSegmentStepType::class, $step, ['method' => 'PATCH']);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->serialize($step, self::$xhrListSerializationGroups);
        }

        return $form;
    }

    public function deleteStepAction(Campaign $campaign, CampaignSegment $segment, CampaignSegmentStep $step, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($step);
        $em->flush();
        return new JsonResponse();
    }
}
