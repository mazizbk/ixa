<?php


namespace Azimut\Bundle\MontgolfiereAppBundle\Controller;


use Azimut\Bundle\MontgolfiereAppBundle\Entity\Campaign;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignAutomaticAffectation;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignSegment;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignSortingFactor;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignSortingFactorValue;
use Doctrine\ORM\Query\ResultSetMapping;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BackofficeCampaignsAutomaticAffectationsController extends AbstractBackofficeSubEntityController
{

    protected static $parentClass = Campaign::class;
    protected static $parentPropertyName = 'automaticAffectations';
    protected static $subEntityClass = CampaignAutomaticAffectation::class;
    protected static $subEntityPropertyName = 'campaign';
    protected static $listView = '@AzimutMontgolfiereApp/Backoffice/Campaigns/automatic_affectations.html.twig';
    protected static $xhrOnly = true;
    protected static $xhrListSerializationGroups = ['backoffice_sorting_factors_list'];
    protected static $routesPrefix = 'azimut_montgolfiere_app_backoffice_campaigns_sorting_factors';
    protected static $translationPrefix = 'montgolfiere.backoffice.campaigns.sorting_factors';
    protected static $parentRouteParamName = 'id';
    protected static $parentRouteParamValue = 'id';
    protected static $subEntityRouteParamName = 'sorting_factor';
    protected static $subEntityRouteParamValue = 'id';

    /**
     * @param         $entity
     * @param Request $request
     * @return Response|View
     * @ParamConverter("entity", converter="azimut_backoffice_subentity")
     */
    public function listAction($entity, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->disableSoftDeleteableIfConfigured();

        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository(CampaignAutomaticAffectation::class);

        $automaticAffectations = $repo->getSortingValuesByAutomaticAffectation($entity);

        return $this->render($this::$listView, array_merge([
            'entity' => $entity,
            'automaticAffectations' => $automaticAffectations,
            'isFilteredView' => isset($isFilteredView)?$isFilteredView:null,
            'filterForm' => isset($filterForm)?$filterForm->createView():null,
        ], $this->getListAdditionalViewParameters($entity)));
    }


    public function saveAction(Campaign $campaign, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $em = $this->getDoctrine()->getManager();
        $valuesRepository = $em->getRepository(CampaignSortingFactorValue::class);

        foreach ($campaign->getAutomaticAffectations() as $automaticAffectation) {
            $em->remove($automaticAffectation);
        }

        $em->flush();
        $data = json_decode($request->request->get('data'), true);

        foreach ($data as $key) {
            $values = explode('-', $key);
            $locale = array_pop($values);
            $values = $valuesRepository->createQueryBuilder('v')->where('v.id IN (:ids)')->setParameter(':ids', $values)->getQuery()->getResult();

            $affectation = new CampaignAutomaticAffectation();
            $affectation
                ->setCampaign($campaign)
                ->setLocale($locale)
                ->setSortingFactorValues($values)
            ;
            $em->persist($affectation);
        }

        $em->flush();
        $this->addFlash('success', $this->translator->trans('montgolfiere.backoffice.campaigns.automatic_affectations.flash.affectations_were_saved'));

        return $this->redirectToRoute('azimut_montgolfiere_app_backoffice_campaigns_automatic_affectations', ['id' => $campaign->getId()]);
    }

    /**
     * @param CampaignSortingFactor $subEntity
     * @param Campaign $entity
     * @return bool
     */
    protected function subEntityBelongsToEntity($subEntity, $entity)
    {
        return $subEntity->getCampaign()->getId() === $entity->getId();
    }

}
