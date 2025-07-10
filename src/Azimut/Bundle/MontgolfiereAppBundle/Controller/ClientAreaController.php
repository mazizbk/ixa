<?php

namespace Azimut\Bundle\MontgolfiereAppBundle\Controller;

use Azimut\Bundle\FrontofficeBundle\Entity\Page;
use Azimut\Bundle\FrontofficeBundle\Entity\Site;
use Azimut\Bundle\FrontofficeSecurityBundle\Entity\FrontofficeUser;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\Campaign;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignParticipation;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\Question;
use Azimut\Bundle\MontgolfiereAppBundle\EventSubscriber\UploadSubscriber;
use Azimut\Bundle\MontgolfiereAppBundle\Util\CampaignAnalyser;
use Azimut\Bundle\MontgolfiereAppBundle\Util\CampaignExporter;
use Azimut\Bundle\MontgolfiereAppBundle\Util\ParticipationFilterHelper;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ClientAreaController extends AbstractController
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
     * @var ParticipationFilterHelper
     */
    private $participationFilterHelper;
    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(CampaignAnalyser $campaignAnalyser, SerializerInterface $serializer, ParticipationFilterHelper $participationFilterHelper, RequestStack $requestStack){

        $this->campaignAnalyser = $campaignAnalyser;
        $this->serializer = $serializer;
        $this->participationFilterHelper = $participationFilterHelper;
        $this->requestStack = $requestStack;
    }

    public function mainAction($path, Site $site, Page $page, $pagePath)
    {
        $this->denyAccessUnlessGranted('ROLE_FRONT_USER');
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
        if($campaign->getClient()->getId() !== $user->getClientContact()->getClient()->getId()) {
            return $this->redirectToRoute('azimut_frontoffice', ['path' => 'espace-client',]);
        }

        return $this->render(':PageLayout/ixa:clientarea_campaign.html.twig', [
            'site' => $site,
            'page' => $page,
            'siteLayout' => 'SiteLayout/'.$site->getLayout()->getTemplate(),
            'pageTitle' => $page->getMetaTitle(),
            'pageDescription' => $page->getMetaDescription(),
            'pagePath' => $pagePath,
            'campaign' => $campaign,
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
        if($campaign->getClient()->getId() !== $user->getClientContact()->getClient()->getId()) {
            return $this->redirectToRoute('azimut_frontoffice', ['path' => 'espace-client',]);
        }
        $form = $this->createFormBuilder(null, ['action' => '#house'])->getForm()->createView();

        return $this->render(':PageLayout/ixa:clientarea_house.html.twig', [
            'site' => $site,
            'page' => $page,
            'siteLayout' => 'SiteLayout/'.$site->getLayout()->getTemplate(),
            'pageTitle' => $page->getMetaTitle(),
            'pageDescription' => $page->getMetaDescription(),
            'pagePath' => $pagePath,
            'campaign' => $campaign,
            'form' => $form,
        ]);
    }

    protected function campaignsAction(Site $site, Page $page, $pagePath)
    {
        $em = $this->getDoctrine()->getManager();
        $campaignRepo = $em->getRepository(Campaign::class);


        return $this->render(':PageLayout/ixa:clientarea_campaigns.html.twig', [
            'site' => $site,
            'page' => $page,
            'siteLayout' => 'SiteLayout/'.$site->getLayout()->getTemplate(),
            'pageTitle' => $page->getMetaTitle(),
            'pageDescription' => $page->getMetaDescription(),
            'pagePath' => $pagePath,
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
        if($campaign->getClient()->getId() !== $user->getClientContact()->getClient()->getId()) {
            return $this->redirectToRoute('azimut_frontoffice', ['path' => 'espace-client',]);
        }
        $participations = $this->getParticipations($campaign, $this->requestStack->getMasterRequest(), $form);
        $campaign->setParticipations($participations);

        return $this->render(':PageLayout/ixa:clientarea_additional_questions.html.twig', [
            'site' => $site,
            'page' => $page,
            'siteLayout' => 'SiteLayout/'.$site->getLayout()->getTemplate(),
            'pageTitle' => $page->getMetaTitle(),
            'pageDescription' => $page->getMetaDescription(),
            'pagePath' => $pagePath,
            'campaign' => $campaign,
            'form' => $form->createView(),
            'userType' => 'client',
        ]);
    }

    public function logoAction(UploadSubscriber $uploadSubscriber)
    {

        $user = $this->getUser();

        if(!$user instanceof FrontofficeUser || !$user->getClientContact()->getClient()->getFilename()) {
            throw $this->createNotFoundException();
        }

        return $this->file($uploadSubscriber->getUploadsDir().DIRECTORY_SEPARATOR.$uploadSubscriber->getTargetDir().DIRECTORY_SEPARATOR.$user->getClientContact()->getClient()->getFilename());
    }

    public function dataAction(Campaign $campaign)
    {
        $user = $this->getUser();
        if(!$user instanceof FrontofficeUser || $campaign->getClient() != $user->getClientContact()->getClient()){
            throw $this->createAccessDeniedException();
        }

        $participations = $campaign->getParticipations();

        $analysis = $this->campaignAnalyser->getAnalysisData($campaign, $participations);

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
        $form = $this->participationFilterHelper->getLightFilterForm($campaign, $request->getMethod(), 'azimut_montgolfiere_app_backoffice_campaigns_house');
        $form->handleRequest($request);
        if($form->isSubmitted() && !$form->isValid()) {
            throw new BadRequestHttpException();
        }
        $this->participationFilterHelper->handleFilterForm($form, $qb, $campaign);

        return $qb->getQuery()->getResult();
    }

}
