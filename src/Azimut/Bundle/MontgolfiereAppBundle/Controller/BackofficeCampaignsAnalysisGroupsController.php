<?php


namespace Azimut\Bundle\MontgolfiereAppBundle\Controller;


use Azimut\Bundle\MontgolfiereAppBundle\Entity\Campaign;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignAnalysisGroup;
use JMS\Serializer\SerializerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

class BackofficeCampaignsAnalysisGroupsController extends AbstractBackofficeSubEntityController
{

    protected static $parentClass = Campaign::class;
    protected static $parentPropertyName = 'analysisGroups';
    protected static $subEntityClass = CampaignAnalysisGroup::class;
    protected static $subEntityPropertyName = 'campaign';
    protected static $listView = '@AzimutMontgolfiereApp/Backoffice/Campaigns/analysis_groups.html.twig';
    protected static $createView = '@AzimutMontgolfiereApp/Backoffice/Campaigns/analysis_groups_new.html.twig';
    protected static $updateView = '@AzimutMontgolfiereApp/Backoffice/Campaigns/analysis_groups_new.html.twig';
    protected static $routesPrefix = 'azimut_montgolfiere_app_backoffice_campaigns_analysis_groups';
    protected static $translationPrefix = 'montgolfiere.backoffice.campaigns.analysis_groups';
    protected static $parentRouteParamName = 'id';
    protected static $parentRouteParamValue = 'id';
    protected static $subEntityRouteParamName = 'analysis_group';
    protected static $subEntityRouteParamValue = 'id';

    public function __construct(RouterInterface $router, TranslatorInterface $translator, PropertyAccessorInterface $propertyAccessor, PaginatorInterface $paginator, SerializerInterface $serializer)
    {
        parent::__construct($router, $translator, $propertyAccessor, $paginator, $serializer);
    }
    /**
     * @param Campaign        $entity
     * @param CampaignAnalysisGroup $subEntity
     * @return bool
     */
    protected function subEntityBelongsToEntity($subEntity, $entity)
    {
        return $entity->getId() === $subEntity->getCampaign()->getId();
    }

    protected function getEditFormOptions($subEntity, $entity, string $type): array
    {
        return [
            'campaign' => $entity,
        ];
    }

}
