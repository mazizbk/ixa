<?php


namespace Azimut\Bundle\MontgolfiereAppBundle\Controller;


use Azimut\Bundle\MontgolfiereAppBundle\Entity\Campaign;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignAutomaticAffectation;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignSortingFactor;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignSortingFactorValue;
use Azimut\Bundle\MontgolfiereAppBundle\Form\Type\CampaignSortingFactorValueType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class BackofficeCampaignsSortingFactorsController extends AbstractBackofficeSubEntityController
{

    protected static $parentClass = Campaign::class;
    protected static $parentPropertyName = 'sortingFactors';
    protected static $subEntityClass = CampaignSortingFactor::class;
    protected static $subEntityPropertyName = 'campaign';
    protected static $listView = '@AzimutMontgolfiereApp/Backoffice/Campaigns/sorting_factors.html.twig';
    protected static $xhrOnly = true;
    protected static $xhrListSerializationGroups = ['backoffice_sorting_factors_list'];
    protected static $routesPrefix = 'azimut_montgolfiere_app_backoffice_campaigns_sorting_factors';
    protected static $translationPrefix = 'montgolfiere.backoffice.campaigns.sorting_factors';
    protected static $parentRouteParamName = 'id';
    protected static $parentRouteParamValue = 'id';
    protected static $subEntityRouteParamName = 'sorting_factor';
    protected static $subEntityRouteParamValue = 'id';

    /**
     * @param CampaignSortingFactor $subEntity
     * @param Campaign $entity
     * @return bool
     */
    protected function subEntityBelongsToEntity($subEntity, $entity)
    {
        return $subEntity->getCampaign()->getId() === $entity->getId();
    }

    /**
     * @param Campaign $campaign
     * @param CampaignSortingFactor $sortingFactor
     * @param Request $request
     * @return Response
     * @ParamConverter("sortingFactor", options={"id" = "sorting_factor"})
     */
    public function addValueAction(Campaign $campaign, CampaignSortingFactor $sortingFactor, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        if(!$this->subEntityBelongsToEntity($sortingFactor, $campaign)) {
            throw new BadRequestHttpException('$subEntity does not belong to $entity');
        }

        $value = new CampaignSortingFactorValue();
        $value->setSortingFactor($sortingFactor);

        $form = $this->createForm(CampaignSortingFactorValueType::class, $value, ['type' => 'create',]);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $doctrine = $this->getDoctrine();
            $em = $doctrine->getManager();
            $em->persist($value);
            $em->flush();

            return $this->serialize($value, $this::$xhrListSerializationGroups);
        }

        throw new BadRequestHttpException();
    }

    /**
     * @param Campaign $campaign
     * @param CampaignSortingFactor $sortingFactor
     * @param CampaignSortingFactorValue $value
     * @param Request $request
     * @return Response
     * @ParamConverter("sortingFactor", options={"id" = "sorting_factor"})
     */
    public function editValueAction(Campaign $campaign, CampaignSortingFactor $sortingFactor, CampaignSortingFactorValue $value, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        if(!$this->subEntityBelongsToEntity($sortingFactor, $campaign)) {
            throw new BadRequestHttpException('$subEntity does not belong to $entity');
        }
        if($value->getSortingFactor()->getId() !== $sortingFactor->getId()) {
            throw new BadRequestHttpException('Value does not belong to sorting factor');
        }

        $form = $this->createForm(CampaignSortingFactorValueType::class, $value, ['type' => $request->getMethod() === 'PATCH'?'position':'update',]);
        $form->submit($request->request->get($form->getName()), $request->getMethod()!=='PATCH');
        if($form->isSubmitted() && $form->isValid()) {
            $doctrine = $this->getDoctrine();
            $em = $doctrine->getManager();
            $em->flush();

            return $this->serialize($value, $this::$xhrListSerializationGroups);
        }

        throw new BadRequestHttpException();
    }

    /**
     * @param Campaign $campaign
     * @param CampaignSortingFactor $sortingFactor
     * @param CampaignSortingFactorValue $value
     * @return Response
     * @ParamConverter("sortingFactor", options={"id" = "sorting_factor"})
     */
    public function deleteValueAction(Campaign $campaign, CampaignSortingFactor $sortingFactor, CampaignSortingFactorValue $value)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        if(!$this->subEntityBelongsToEntity($sortingFactor, $campaign)) {
            throw new BadRequestHttpException('$subEntity does not belong to $entity');
        }
        if($value->getSortingFactor()->getId() !== $sortingFactor->getId()) {
            throw new BadRequestHttpException('Value does not belong to sorting factor');
        }

        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();

        $automaticAffectationsRepository = $em->getRepository(CampaignAutomaticAffectation::class);
        $automaticAffectationsRepository
            ->createQueryBuilder('aa')
            ->delete()
            ->where(':value MEMBER OF aa.sortingFactorValues')
            ->setParameter(':value', $value)
            ->getQuery()
            ->execute()
        ;
        $em->remove($value);
        $em->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
