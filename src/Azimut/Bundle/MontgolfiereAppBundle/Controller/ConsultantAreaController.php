<?php

namespace Azimut\Bundle\MontgolfiereAppBundle\Controller;

use Azimut\Bundle\FrontofficeBundle\Entity\Page;
use Azimut\Bundle\FrontofficeBundle\Entity\Site;
use Azimut\Bundle\FrontofficeSecurityBundle\Entity\FrontofficeUser;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\Campaign;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignParticipation;
use Azimut\Bundle\MontgolfiereAppBundle\Util\CampaignAnalyser;
use Azimut\Bundle\MontgolfiereAppBundle\Util\CampaignExporter;
use Azimut\Bundle\MontgolfiereAppBundle\Util\CampaignWordGenerator;
use Azimut\Bundle\MontgolfiereAppBundle\Util\ExcelExporter;
use Azimut\Bundle\MontgolfiereAppBundle\Util\ParticipationFilterHelper;
use Azimut\Bundle\MontgolfiereAppBundle\Util\WordExporter;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ConsultantAreaController extends AbstractController
{
    /**
     * @var CampaignAnalyser
     */
    private $campaignAnalyser;
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var RequestStack
     */
    private $requestStack;
    /**
     * @var CampaignWordGenerator
     */
    private $campaignWordGenerator;

    /**
     * @var ParticipationFilterHelper
     */
    private $participationFilterHelper;

    /**
     * @var CampaignExporter
     */
    private $campaignExporter;

    public function __construct(CampaignAnalyser $campaignAnalyser, CampaignWordGenerator $campaignWordGenerator, SerializerInterface $serializer, ParticipationFilterHelper $participationFilterHelper, RequestStack $requestStack, CampaignExporter $campaignExporter)
    {
        $this->campaignAnalyser = $campaignAnalyser;
        $this->serializer = $serializer;
        $this->participationFilterHelper = $participationFilterHelper;
        $this->requestStack = $requestStack;
        $this->campaignWordGenerator = $campaignWordGenerator;
        $this->campaignExporter = $campaignExporter;
    }

    public function mainAction($path, Site $site, Page $page, $pagePath)
    {
        $this->denyAccessUnlessGranted('ROLE_FRONT_CONSULTANT');
        $em = $this->getDoctrine()->getManager();
        $em->getFilters()->disable('softdeleteable');
        $parts = explode('/', $path);
        switch($parts[0]) {
            case 'campaign':
                if(isset($parts[2]) && $parts[2] == 'house') {
                    return $this->homeCampaignAction($site, $page, $pagePath, $parts[1]);
                }
                if(isset($parts[2]) && $parts[2] == 'additional-questions') {
                    return $this->additionalQuestionsAction($site, $page, $pagePath, $parts[1]);
                }
                if(isset($parts[2]) && $parts[2] == 'verbatim-export') {
                    return $this->verbatimExportAction($site, $page, $pagePath, $parts[1]);
                }
                if(isset($parts[2]) && $parts[2] == 'cartography') {
                    return $this->cartographyAction($site, $page, $pagePath, $parts[1]);
                }
                if(isset($parts[2]) && $parts[2] == 'cartography-export') {
                    return $this->cartographyExportAction($site, $page, $pagePath, $parts[1]);
                }
                if(isset($parts[1])) {
                    return $this->campaignAction($site, $page, $pagePath, $parts[1]);
                }
                return $this->campaignsAction($site, $page, $pagePath);
        }

        throw $this->createNotFoundException('No subpage found at '.$path);
    }

    protected function campaignAction(Site $site, Page $page, $pagePath, $campaignId)
    {
        $em = $this->getDoctrine()->getManager();
        $campaignRepo = $em->getRepository(Campaign::class);
        /** @var Campaign $campaign */
        $campaign = $campaignRepo->find($campaignId);
        if(!$campaign) {
            throw $this->createNotFoundException('Campaign not found');
        }
        /** @var FrontofficeUser $user */
        $user = $this->getUser();
        if(!$campaign->getConsultants()->contains($user)) {
            return $this->redirectToRoute('azimut_frontoffice', ['path' => 'espace-consultant',]);
        }

        $rpsAlertCount = $this->countRpsAlerts($campaign);

        return $this->render(':PageLayout/ixa:consultantarea_campaign.html.twig', [
            'site' => $site,
            'page' => $page,
            'siteLayout' => 'SiteLayout/'.$site->getLayout()->getTemplate(),
            'pageTitle' => $page->getMetaTitle(),
            'pageDescription' => $page->getMetaDescription(),
            'pagePath' => $pagePath,
            'campaign' => $campaign,
            'rpsAlertCount' => $rpsAlertCount,
        ]);
    }

    protected function homeCampaignAction(Site $site, Page $page, $pagePath, $campaignId)
    {
        $em = $this->getDoctrine()->getManager();
        $campaignRepo = $em->getRepository(Campaign::class);
        /** @var Campaign $campaign */
        $campaign = $campaignRepo->find($campaignId);
        if(!$campaign) {
            throw $this->createNotFoundException('Campaign not found');
        }
        /** @var FrontofficeUser $user */
        $user = $this->getUser();
        if(!$campaign->getConsultants()->contains($user)) {
            return $this->redirectToRoute('azimut_frontoffice', ['path' => 'espace-consultant',]);
        }
        $participations = $this->getParticipations($campaign, $this->requestStack->getMasterRequest(), $form);

        return $this->render(':PageLayout/ixa:consultantarea_house.html.twig', [
            'site' => $site,
            'page' => $page,
            'siteLayout' => 'SiteLayout/'.$site->getLayout()->getTemplate(),
            'pageTitle' => $page->getMetaTitle(),
            'pageDescription' => $page->getMetaDescription(),
            'pagePath' => $pagePath,
            'campaign' => $campaign,
            'participations' => $participations,
            'form' => $form->createView(),
        ]);
    }

    protected function additionalQuestionsAction(Site $site, Page $page, $pagePath, $campaignId)
    {
        $em = $this->getDoctrine()->getManager();
        $campaignRepo = $em->getRepository(Campaign::class);
        /** @var Campaign $campaign */
        $campaign = $campaignRepo->find($campaignId);
        if(!$campaign) {
            throw $this->createNotFoundException('Campaign not found');
        }
        /** @var FrontofficeUser $user */
        $user = $this->getUser();
        if(!$campaign->getConsultants()->contains($user)) {
            return $this->redirectToRoute('azimut_frontoffice', ['path' => 'espace-consultant',]);
        }
        $participations = $this->getParticipations($campaign, $this->requestStack->getMasterRequest(), $form);
        $campaign->setParticipations($participations);

        return $this->render(':PageLayout/ixa:consultantarea_additional_questions.html.twig', [
            'site' => $site,
            'page' => $page,
            'siteLayout' => ':SiteLayout/ixa:consultant_area.html.twig',
            'pageTitle' => $page->getMetaTitle(),
            'pageDescription' => $page->getMetaDescription(),
            'pagePath' => $pagePath,
            'campaign' => $campaign,
            'form' => $form->createView(),
            'userType' => 'consultant',
            'verbatimExport' => !empty($campaign->getQuestionsAvailableForConsultantVerbatimExport()),
            'request' => $this->requestStack->getMasterRequest(),
        ]);
    }

    protected function verbatimExportAction(Site $site, Page $page, $pagePath, $campaignId)
    {
        $em = $this->getDoctrine()->getManager();
        $campaignRepo = $em->getRepository(Campaign::class);
        /** @var Campaign $campaign */
        $campaign = $campaignRepo->find($campaignId);
        if(!$campaign) {
            throw $this->createNotFoundException('Campaign not found');
        }
        /** @var FrontofficeUser $user */
        $user = $this->getUser();
        if(!$campaign->getConsultants()->contains($user)) {
            return $this->redirectToRoute('azimut_frontoffice', ['path' => 'espace-consultant',]);
        }
        $participations = $this->getParticipations($campaign, $this->requestStack->getMasterRequest(), $form);

        $document = CampaignWordGenerator::exportVerbatims($campaign, $participations, $campaign->getQuestionsAvailableForConsultantVerbatimExport());

        $fileName = $this->campaignAnalyser->getFileName($campaign, $form, 'Verbatims') .'.docx';
        $fileName = str_replace(['\\', '/', '%'], '', $fileName);

        return WordExporter::makeResponse($document, $fileName);
    }

    protected function cartographyAction(Site $site, Page $page, $pagePath, $campaignId)
    {
        $em = $this->getDoctrine()->getManager();
        $campaignRepo = $em->getRepository(Campaign::class);
        /** @var Campaign $campaign */
        $campaign = $campaignRepo->find($campaignId);
        if(!$campaign) {
            throw $this->createNotFoundException('Campaign not found');
        }
        /** @var FrontofficeUser $user */
        $user = $this->getUser();
        if(!$campaign->getConsultants()->contains($user)) {
            return $this->redirectToRoute('azimut_frontoffice', ['path' => 'espace-consultant',]);
        }
        $this->getCartographyParticipations($campaign, $this->requestStack->getMasterRequest(), $form);

        return $this->render(':PageLayout/ixa:consultantarea_cartography.html.twig', [
            'site' => $site,
            'page' => $page,
            'siteLayout' => ':SiteLayout/ixa:consultant_area.html.twig',
            'pageTitle' => $page->getMetaTitle(),
            'pageDescription' => $page->getMetaDescription(),
            'pagePath' => $pagePath,
            'campaign' => $campaign,
            'form' => $form->createView(),
            'userType' => 'consultant',
            'request' => $this->requestStack->getMasterRequest(),
        ]);
    }

    protected function cartographyExportAction(Site $site, Page $page, $pagePath, $campaignId)
    {
        $em = $this->getDoctrine()->getManager();
        $campaignRepo = $em->getRepository(Campaign::class);
        /** @var Campaign $campaign */
        $campaign = $campaignRepo->find($campaignId);
        if(!$campaign) {
            throw $this->createNotFoundException('Campaign not found');
        }
        /** @var FrontofficeUser $user */
        $user = $this->getUser();
        if(!$campaign->getConsultants()->contains($user)) {
            return $this->redirectToRoute('azimut_frontoffice', ['path' => 'espace-consultant',]);
        }

        $participations = $this->getCartographyParticipations($campaign, $this->requestStack->getMasterRequest(), $form);
        $asPercent = $form->get('numberAs')->getData() === 'percent';
        $format = $this->requestStack->getMasterRequest()->query->get('format');
        if($format !== ExcelExporter::FORMAT_HTML && $format !== ExcelExporter::FORMAT_XLSX) {
            $this->addFlash('danger', 'Le format d\'export est incorrect');
            return $this->redirectToRoute('azimut_frontoffice', ['path' => 'espace-consultant',]);
        }
        if(count($participations) === 0) {
            $this->addFlash('warning', 'Aucune participation ne correspond aux critères de recherche');
            return $this->redirectToRoute('azimut_frontoffice', ['path' => 'espace-consultant',]);
        }

        return $this->campaignExporter->exportCartography($campaign, $participations, $form, $this->requestStack->getMasterRequest()->getLocale(), $asPercent, $format);
    }

    protected function campaignsAction(Site $site, Page $page, $pagePath)
    {
        $em = $this->getDoctrine()->getManager();
        $campaignRepo = $em->getRepository(Campaign::class);


        return $this->render(':PageLayout/ixa:consultantarea.html.twig', [
            'site' => $site,
            'page' => $page,
            'siteLayout' => 'SiteLayout/'.$site->getLayout()->getTemplate(),
            'pageTitle' => $page->getMetaTitle(),
            'pageDescription' => $page->getMetaDescription(),
            'pagePath' => $pagePath,
        ]);
    }

    public function dataAction(Campaign $campaign, Request $request)
    {
        $user = $this->getUser();
        if(!$campaign->getConsultants()->contains($user)) {
            throw $this->createAccessDeniedException();
        }

        $participations = $this->getParticipations($campaign, $request, $form);

        $analysis = $this->campaignAnalyser->getAnalysisData($campaign, $participations, $form, $this->campaignAnalyser->getFileName($campaign, $form, 'Maison Workcare'));

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
        $form = $this->participationFilterHelper->getFilterForm($campaign, $request->getMethod(), 'azimut_montgolfiere_app_backoffice_campaigns_house', false);
        $form->get('buttons')->remove('submit');
        $form->get('buttons')->remove('viewall');
        $form->get('buttons')->add('submit', SubmitType::class, ['label' => 'montgolfiere.backoffice.common.filter_form.search', 'attr' => ['class' => 'Btn']]);
        $form->handleRequest($request);
        if($form->isSubmitted() && !$form->isValid()) {
            throw new BadRequestHttpException();
        }
        $this->participationFilterHelper->handleFilterForm($form, $qb, $campaign);

        return $qb->getQuery()->getResult();
    }

    /**
     * @return CampaignParticipation[]
     */
    private function getCartographyParticipations(Campaign $campaign, Request $request, ?FormInterface &$form = null): array
    {
        $repo = $this->getDoctrine()->getRepository(CampaignParticipation::class);
        $qb = $repo->getFinishedQueryWithAnswers($campaign);
        $form = $this->participationFilterHelper->getCartographyFilterForm($campaign, $request);
        $form->get('buttons')->remove('submit');
        $form->get('buttons')->remove('viewall');
        $form->get('buttons')->add('submit', SubmitType::class, ['label' => 'montgolfiere.backoffice.common.filter_form.search', 'attr' => ['class' => 'Btn']]);
        $form->handleRequest($request);
        if($form->isSubmitted() && !$form->isValid()) {
            throw new BadRequestHttpException();
        }
        $this->participationFilterHelper->handleFilterForm($form, $qb, $campaign);

        return $qb->getQuery()->getResult();
    }

    private function countRpsAlerts(Campaign $campaign): int
    {
        $repo = $this->getDoctrine()->getRepository(CampaignParticipation::class);

        return (int)$repo->createQueryBuilder('cp')
            ->select('COUNT(cp.id)')
            ->where('cp.campaign = :campaign')
            ->andWhere('cp.rpsAlert = true')
            ->setParameter('campaign', $campaign)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function generateWordDocumentAction(Campaign $campaign, Request $request)
    {
        $user = $this->getUser();
        if(!$campaign->getConsultants()->contains($user)) {
            throw $this->createAccessDeniedException();
        }
        $em = $this->getDoctrine()->getManager();
        $em->getFilters()->disable('softdeleteable');

        $participations = $this->getParticipations($campaign, $request, $form);
        $analysis = $this->campaignAnalyser->getAnalysisData($campaign, $participations, $form);

        $phpWord = $this->campaignWordGenerator->generateWordDocument($campaign, $participations, $analysis, $request);
        $fileName = $this->campaignAnalyser->getFileName($campaign, $form, 'Rapport Workcare') .'.docx';
        $fileName = str_replace(['\\', '/', '%'], '', $fileName);

        return WordExporter::makeResponse($phpWord, $fileName);
    }

}
