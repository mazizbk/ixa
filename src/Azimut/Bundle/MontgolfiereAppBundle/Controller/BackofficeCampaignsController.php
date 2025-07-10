<?php

namespace Azimut\Bundle\MontgolfiereAppBundle\Controller;

use Azimut\Bundle\MontgolfiereAppBundle\Entity\Campaign;
use Azimut\Bundle\MontgolfiereAppBundle\Form\Type\FilterCampaignsType;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\FormInterface;

class BackofficeCampaignsController extends AbstractBackofficeEntityController
{
    protected static $entityClass = Campaign::class;
    protected static $listView = '@AzimutMontgolfiereApp/Backoffice/Campaigns/index.html.twig';
    protected static $readView = '@AzimutMontgolfiereApp/Backoffice/Campaigns/read.html.twig';
    protected static $createView = '@AzimutMontgolfiereApp/Backoffice/Campaigns/new.html.twig';
    protected static $updateView = '@AzimutMontgolfiereApp/Backoffice/Campaigns/edit.html.twig';
    protected static $routePrefix = 'azimut_montgolfiere_app_backoffice_campaigns';
    protected static $routeParameterName = 'id';
    protected static $routeParameterValue = 'id';
    protected static $translationPrefix = 'montgolfiere.backoffice.campaigns';
    protected static $xhrReadSerializationGroups = ['backoffice_campaigns_read'];

    /**
     * @return FormInterface
     */
    protected function getFilterForm()
    {
        return $this->createForm(FilterCampaignsType::class, ['showUpcoming' => true,]);
    }

    protected function getEntityQuery()
    {
        return parent::getEntityQuery()->orderBy('e.startDate', 'ASC');
    }

    protected function handleFilterForm(FormInterface $filterForm, QueryBuilder $queryBuilder)
    {
        $expr = $queryBuilder->expr();

        if ($name = $filterForm->get('name')->getData()) {
            $queryBuilder
                ->andWhere($expr->like('e.name', ':name'))
                ->setParameter(':name', '%'.$name.'%')
            ;
        }

        $needsToday = false;
        $showExpired = $filterForm->get('showExpired')->getData();
        if(!$showExpired) {
            $queryBuilder
                ->andWhere(
                    $expr->orX(
                        $expr->gte('e.endDate', ':today'),
                        $expr->isNull('e.endDate')
                    )
                )
            ;
            $needsToday = true;
        }
        $showUpcoming = $filterForm->get('showUpcoming')->getData();
        if(!$showUpcoming) {
            $queryBuilder
                ->andWhere(
                    $expr->orX(
                        $expr->lte('e.startDate', ':today'),
                        $expr->isNull('e.startDate')
                    )
                )
            ;
            $needsToday = true;
        }
        if($needsToday) {
            $queryBuilder
                ->setParameter(':today', new \DateTime, Type::DATE)
            ;
        }
    }

    protected function filterDefault(QueryBuilder $queryBuilder)
    {
        $expr = $queryBuilder->expr();

        $queryBuilder
            ->andWhere(
                $expr->orX(
                    $expr->between(':today', 'e.startDate', 'e.endDate'),
                    $expr->isNull('e.startDate'),
                    $expr->isNull('e.endDate')
                )
            )
            ->setParameter(':today', new \DateTime, Type::DATE)
        ;
    }


    protected function isFiltered(FormInterface $filterForm)
    {
        return !!$filterForm->get('name')->getData();
    }
}
