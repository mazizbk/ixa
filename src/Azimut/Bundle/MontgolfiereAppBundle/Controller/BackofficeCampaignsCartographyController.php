<?php

namespace Azimut\Bundle\MontgolfiereAppBundle\Controller;

use Azimut\Bundle\MontgolfiereAppBundle\Entity\Campaign;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignParticipation;
use Azimut\Bundle\MontgolfiereAppBundle\Util\CampaignExporter;
use Azimut\Bundle\MontgolfiereAppBundle\Util\ExcelExporter;
use Azimut\Bundle\MontgolfiereAppBundle\Util\ParticipationFilterHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class BackofficeCampaignsCartographyController extends AbstractController
{

    /**
     * @var ParticipationFilterHelper
     */
    private $participationFilterHelper;

    /**
     * @var CampaignExporter
     */
    private $campaignExporter;


    public function __construct(ParticipationFilterHelper $participationFilterHelper, CampaignExporter $campaignExporter)
    {
        $this->participationFilterHelper = $participationFilterHelper;
        $this->campaignExporter = $campaignExporter;
    }

    public function indexAction(Campaign $campaign, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $participations = $this->getParticipations($campaign, $request, $form);

        return $this->render('@AzimutMontgolfiereApp/Backoffice/Campaigns/cartography.html.twig', [
            'campaign' => $campaign,
            'form' => $form->createView(),
            'participations' => $participations,
        ]);
    }

    public function exportAction(Campaign $campaign, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $participations = $this->getParticipations($campaign, $request, $form);
        $asPercent = $form->get('numberAs')->getData() === 'percent';
        $format = $request->query->get('format');
        if($format !== ExcelExporter::FORMAT_HTML && $format !== ExcelExporter::FORMAT_XLSX) {
            $this->addFlash('danger', 'Le format d\'export est incorrect');
            return $this->redirectToRoute('azimut_montgolfiere_app_backoffice_campaigns_cartography', ['id' => $campaign->getId()]);
        }
        if(count($participations) === 0) {
            $this->addFlash('warning', 'Aucune participation ne correspond aux critères de recherche');
            return $this->redirectToRoute('azimut_montgolfiere_app_backoffice_campaigns_cartography', ['id' => $campaign->getId()]);
        }

        return $this->campaignExporter->exportCartography($campaign, $participations, $form, $request->getLocale(), $asPercent, $format);
    }

    /**
     * @return CampaignParticipation[]
     */
    private function getParticipations(Campaign $campaign, Request $request, ?FormInterface &$form = null): array
    {
        $repo = $this->getDoctrine()->getRepository(CampaignParticipation::class);
        $qb = $repo->getFinishedQueryWithAnswers($campaign);
        $form = $this->participationFilterHelper->getCartographyFilterForm($campaign, $request);
        $form->handleRequest($request);
        if($form->isSubmitted() && !$form->isValid()) {
            throw new BadRequestHttpException();
        }
        $this->participationFilterHelper->handleFilterForm($form, $qb, $campaign);

        return $qb->getQuery()->getResult();
    }

}
