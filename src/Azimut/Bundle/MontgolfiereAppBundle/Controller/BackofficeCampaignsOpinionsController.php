<?php
/**
 * User: goulven
 * Date: 08/08/2022
 */

namespace Azimut\Bundle\MontgolfiereAppBundle\Controller;

use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignParticipationOpinion;
use Azimut\Bundle\MontgolfiereAppBundle\Form\Type\FilterCampaignsOpinionsType;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\FormInterface;

class BackofficeCampaignsOpinionsController extends AbstractBackofficeEntityController
{
    protected static $entityClass = CampaignParticipationOpinion::class;
    protected static $listView = '@AzimutMontgolfiereApp/Backoffice/Campaigns/opinions.html.twig';
    protected static $readView = '@AzimutMontgolfiereApp/Backoffice/Campaigns/opinions_read.html.twig';
    protected static $createView = '-';
    protected static $updateView = '-';
    protected static $routePrefix = 'azimut_montgolfiere_app_backoffice_campaigns_opinions';
    protected static $routeParameterName = 'id';
    protected static $routeParameterValue = 'id';
    protected static $translationPrefix = 'montgolfiere.backoffice.campaigns.participations.options';

    /**
     * @return FormInterface
     */
    protected function getFilterForm()
    {
        return $this->createForm(FilterCampaignsOpinionsType::class);
    }

    protected function getEntityQuery()
    {
        return parent::getEntityQuery()->orderBy('e.id', 'DESC');
    }

    protected function handleFilterForm(FormInterface $filterForm, QueryBuilder $queryBuilder)
    {
        $expr = $queryBuilder->expr();

        if ($name = $filterForm->get('name')->getData()) {
            $queryBuilder
                ->join('participation', 'p')
                ->andWhere($expr->like('p.name', ':name'))
                ->setParameter(':name', '%'.$name.'%')
            ;
        }
    }

    protected function isFiltered(FormInterface $filterForm)
    {
        return !!$filterForm->get('name')->getData();
    }
}