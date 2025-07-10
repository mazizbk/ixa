<?php
/**
 * Created by mikaelp on 02-Oct-18 10:19 AM
 */

namespace Azimut\Bundle\MontgolfiereAppBundle\Controller;


use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\FormInterface;

interface SupportsFilteringEntityController
{
    /**
     * @param $entity
     * @return FormInterface
     */
    public function getFilterForm($entity);

    public function handleFilterForm(FormInterface $filterForm, QueryBuilder $queryBuilder, $entity);

    public function isFiltered(FormInterface $filterForm);
}
