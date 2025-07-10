<?php

namespace Azimut\Bundle\MontgolfiereAppBundle\Controller;


use Azimut\Bundle\MontgolfiereAppBundle\Entity\Campaign;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignParticipation;
use Azimut\Bundle\MontgolfiereAppBundle\Util\CampaignAnalyser;
use Azimut\Bundle\MontgolfiereAppBundle\Util\CampaignWordGenerator;
use Azimut\Bundle\MontgolfiereAppBundle\Util\ParticipationFilterHelper;
use Azimut\Bundle\MontgolfiereAppBundle\Util\SortingFactorManager;
use Azimut\Bundle\MontgolfiereAppBundle\Util\ThemesManager;
use Azimut\Bundle\MontgolfiereAppBundle\Util\WordExporter;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Translation\TranslatorInterface;


class BackofficeCampaignsHouseController extends AbstractController
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;
    /**
     * @var ThemesManager
     */
    protected $themesManager;
    /**
     * @var SortingFactorManager
     */
    protected $sortingFactorManager;

    /**
     * @var ParticipationFilterHelper
     */
    protected $participationFilterHelper;

    /**
     * @var CampaignAnalyser
     */
    protected $campaignAnalyser;

    /**
     * @var SerializerInterface
     */
    protected $serializer;
    /**
     * @var CampaignWordGenerator
     */
    private $campaignWordGenerator;

    public function __construct(TranslatorInterface $translator, ThemesManager $themesManager, SortingFactorManager $sortingFactorManager, ParticipationFilterHelper $participationFilterHelper, CampaignAnalyser $campaignAnalyser, CampaignWordGenerator $campaignWordGenerator, SerializerInterface $serializer)
    {
        $this->translator = $translator;
        $this->themesManager = $themesManager;
        $this->sortingFactorManager = $sortingFactorManager;
        $this->participationFilterHelper = $participationFilterHelper;
        $this->campaignAnalyser = $campaignAnalyser;
        $this->serializer = $serializer;
        $this->campaignWordGenerator = $campaignWordGenerator;
    }

    public function indexAction(Campaign $campaign, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $participations = $this->getParticipations($campaign, $request, $form);
        $analysis = $this->campaignAnalyser->getAnalysisData($campaign, $participations, $form);

        return $this->render('@AzimutMontgolfiereApp/Backoffice/Campaigns/house.html.twig', [
            'campaign' => $campaign,
            'form' => $form->createView(),
            'participations' => $participations,
            'themesAnalysis' => $analysis->getThemesAnalysis(),
        ]);
    }

    public function generateWordDocumentAction(Campaign $campaign, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        /** @var EntityManagerInterface $em */
        $em = $this->getDoctrine()->getManager();
        $em->getFilters()->disable('softdeleteable');

        $participations = $this->getParticipations($campaign, $request, $form);
        $analysis = $this->campaignAnalyser->getAnalysisData($campaign, $participations, $form);

        $phpWord = $this->campaignWordGenerator->generateWordDocument($campaign, $participations, $analysis, $request);
        $fileName = $this->campaignAnalyser->getFileName($campaign, $form, 'Rapport Workcare') .'.docx';
        $fileName = str_replace(['\\', '/', '%'], '', $fileName);

        return WordExporter::makeResponse($phpWord, $fileName);
    }

    public function dataAction(Campaign $campaign, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $participations = $this->getParticipations($campaign, $request, $form);
        $analysis = $this->campaignAnalyser->getAnalysisData(
            $campaign,
            $participations,
            $form,
            $this->campaignAnalyser->getFileName($campaign, $form, 'Maison Workcare')
        );

        return new JsonResponse($this->serializer->serialize($analysis, 'json'), 200, [], true);
    }

    /**
     * @param Campaign $campaign
     * @param Request $request
     * @param FormInterface|null $form
     * @return CampaignParticipation[]
     */
    private function getParticipations(Campaign $campaign, Request $request, ?FormInterface &$form = null): array
    {
        $repo = $this->getDoctrine()->getRepository(CampaignParticipation::class);
        $qb = $repo->getFinishedQueryWithAnswers($campaign);
        $form = $this->participationFilterHelper->getFilterForm($campaign, $request->getMethod(), 'azimut_montgolfiere_app_backoffice_campaigns_house');
        $form->handleRequest($request);
        if($form->isSubmitted() && !$form->isValid()) {
            throw new BadRequestHttpException();
        }
        $this->participationFilterHelper->handleFilterForm($form, $qb, $campaign);

        return $qb->getQuery()->getResult();
    }

}
