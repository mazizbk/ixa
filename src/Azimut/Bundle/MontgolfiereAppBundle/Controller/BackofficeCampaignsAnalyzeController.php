<?php
/**
 * Created by mikaelp on 2018-10-11 9:40 AM
 */

namespace Azimut\Bundle\MontgolfiereAppBundle\Controller;


use Azimut\Bundle\MontgolfiereAppBundle\Entity\Campaign;
use Azimut\Bundle\MontgolfiereAppBundle\Util\CampaignExporter;
use Azimut\Bundle\MontgolfiereAppBundle\Util\ThemesManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BackofficeCampaignsAnalyzeController extends AbstractController
{
    /**
     * @var CampaignExporter
     */
    protected $exporter;

    /**
     * @var ThemesManager
     */
    protected $themesManager;

    public function __construct(CampaignExporter $exporter, ThemesManager $themesManager)
    {
        $this->exporter = $exporter;
        $this->themesManager = $themesManager;
    }


    public function indexAction(Campaign $campaign)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        return $this->render('@AzimutMontgolfiereApp/Backoffice/Campaigns/analyze.html.twig', [
            'campaign' => $campaign,
            'themes' => $this->themesManager->getThemes($campaign->getAnalysisVersion()),
        ]);
    }

    public function rawDataAction(Campaign $campaign)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        return $this->exporter->exportCampaign($campaign);
    }
}
