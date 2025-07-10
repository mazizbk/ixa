<?php


namespace Azimut\Bundle\MontgolfiereAppBundle\Util;


use Azimut\Bundle\MontgolfiereAppBundle\Entity\Campaign;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignAnalysisGroup;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignSegment;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignSortingFactorValue;
use Azimut\Bundle\MontgolfiereAppBundle\Form\Type\ButtonLinkType;
use Azimut\Bundle\MontgolfiereAppBundle\Form\Type\ButtonsType;
use Azimut\Bundle\MontgolfiereAppBundle\Form\Type\FilterCampaignParticipationsType;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class ParticipationFilterHelper
{
    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    public function getFilterForm(Campaign $campaign, string $method, string $route, bool $withPagination = true): FormInterface
    {
        $form = $this->formFactory->createBuilder(FormType::class, null, ['method' => $method,])
            ->add('fastSearch', FilterCampaignParticipationsType::class, [
                'campaign' => $campaign,
                'label' => false,
                'values-as-ids' => false,
            ])
            ->add('groups', EntityType::class, [
                'required' => false,
                'label' => 'montgolfiere.backoffice.common.filter_form.groups',
                'multiple' => true,
                'expanded' => true,
                'class' => CampaignAnalysisGroup::class,
                'query_builder' => function(EntityRepository $er) use($campaign) {
                    return $er->createQueryBuilder('cag')
                        ->where('cag.campaign = :campaign')
                        ->setParameter(':campaign', $campaign)
                     ;
                },
                'choice_label' => 'name',
            ])
            ->getForm()
        ;
        if($withPagination) {
            $form
                ->add('perpage', IntegerType::class, [
                    'label' => 'montgolfiere.backoffice.common.filter_form.per_page',
                    'required' => false,
                    'data' => 100,
                ]);
            }
        $form->add('buttons', ButtonsType::class);
        $buttons = $form->get('buttons');
        $buttons
            ->add('submit', SubmitType::class, ['label' => 'montgolfiere.backoffice.common.filter_form.search', 'attr' => ['class' => 'btn-primary']])
            ->add('viewall', ButtonLinkType::class, [
                'route' => $route,
                'route_params' => ['id' => $campaign->getId(),],
                'text' => 'montgolfiere.backoffice.common.filter_form.show_all',
                'color' => 'default'
            ])
        ;

        return $form;
    }

    public function getLightFilterForm(Campaign $campaign, string $method, string $route): FormInterface
    {
        $form = $this->formFactory->createBuilder(FormType::class, null, ['method' => $method,])
            ->add('fastSearch', FilterCampaignParticipationsType::class, [
                'campaign' => $campaign,
                'label' => false,
                'values-as-ids' => false,
                'with_only_sorting_factors' => true,
            ])
            ->add('submit', SubmitType::class, ['label' => 'montgolfiere.backoffice.common.filter_form.search', 'attr' => ['class' => 'Btn']])
            ->getForm()
        ;

        return $form;
    }

    public function getCartographyFilterForm(Campaign $campaign, Request $request): FormInterface
    {
        $form = $this->getFilterForm($campaign, $request->getMethod(), $request->attributes->get('_route'), false);
        $form->add('numberAs', ChoiceType::class, [
            'label' => 'montgolfiere.backoffice.campaigns.cartography.show_values_as',
            'choices' => [
                'montgolfiere.backoffice.campaigns.cartography.absolute' => 'number',
                'montgolfiere.backoffice.campaigns.cartography.percent' => 'percent',
            ],
        ]);
        $buttons = $form->get('buttons');
        $form->remove('buttons');
        $form->add($buttons);

        return $form;
    }

    public function handleFilterForm(FormInterface $filterForm, QueryBuilder $queryBuilder, Campaign $campaign): void
    {
        $expr = $queryBuilder->expr();
        $orGroup = $expr->orX();

        /** @var CampaignAnalysisGroup[] $groups */
        $groups = ($filterForm->has('groups') && $filterForm->get('groups')->getData())? $filterForm->get('groups')->getData()->toArray() : [];
        if($criteria = $filterForm->get('fastSearch')->getData()) {
            foreach ($criteria as $key => $value) {
                if(!$value || !preg_match('`^(sorting_factor_\d+|segment)+$`', $key)) {
                    continue;
                }
                assert($value instanceof CampaignSortingFactorValue || $value instanceof CampaignSegment);
                // From the real form that was submitted, we have concrete objects
                // In order to resubmit them as if it was an analysis group, we have
                // to reconvert them to their IDs
                $criteria[$key] = $value->getId();
            }
            $groups[] = (new CampaignAnalysisGroup(0))
                ->setCriteria($criteria)
                ->setCampaign($campaign)
            ;
        }
        foreach ($groups as $group) {
            $groupForm = $this->formFactory->create(FilterCampaignParticipationsType::class, null, [
                'campaign' => $campaign,
            ]);
            $groupForm->submit($group->getCriteria());
            $groupAnd = $expr->andX();
            $groupParamPrefix = 'group'.$group->getId().'_';

            foreach ($campaign->getSortingFactors() as $sortingFactor) {
                $data = $groupForm->get('sorting_factor_'.$sortingFactor->getId())->getData();
                if (!$data) {
                    continue;
                }
                $rqParameter = ':'.$groupParamPrefix.'sf_value_'.$sortingFactor->getId();
                $groupAnd->add($expr->isMemberOf($rqParameter, 'cp.sortingFactorsValues'));
                $queryBuilder->setParameter($rqParameter, $data);
            }
            if ($segment = $groupForm->get('segment')->getData()) {
                $groupAnd->add($expr->eq('cp.segment', ':'.$groupParamPrefix.'segment'));
                $queryBuilder->setParameter(':'.$groupParamPrefix.'segment', $segment);
            }
            if($groupForm->has('managerName') && $managerName = $groupForm->get('managerName')->getData()) {
                $groupAnd->add($expr->eq('cp.managerName', ':'.$groupParamPrefix.'managerName'));
                $queryBuilder->setParameter(':'.$groupParamPrefix.'managerName', $managerName);
            }
            if($groupForm->has('gender') && $gender = $groupForm->get('gender')->getData()) {
                $groupAnd->add($expr->eq('cp.gender', ':'.$groupParamPrefix.'gender'));
                $queryBuilder->setParameter(':'.$groupParamPrefix.'gender', $gender);
            }
            if($groupForm->has('seniority') && $seniority = $groupForm->get('seniority')->getData()) {
                $groupAnd->add($expr->eq('cp.seniority', ':'.$groupParamPrefix.'seniority'));
                $queryBuilder->setParameter(':'.$groupParamPrefix.'seniority', $seniority);
            }
            if($groupForm->has('education') && $education = $groupForm->get('education')->getData()) {
                $groupAnd->add($expr->eq('cp.education', ':'.$groupParamPrefix.'education'));
                $queryBuilder->setParameter(':'.$groupParamPrefix.'education', $education);
            }
            if($groupForm->has('csp') && $csp = $groupForm->get('csp')->getData()) {
                $groupAnd->add($expr->eq('cp.csp', ':'.$groupParamPrefix.'csp'));
                $queryBuilder->setParameter(':'.$groupParamPrefix.'csp', $csp);
            }
            if($groupForm->has('age') && $age = $groupForm->get('age')->getData()) {
                $groupAnd->add($expr->eq('cp.age', ':'.$groupParamPrefix.'age'));
                $queryBuilder->setParameter(':'.$groupParamPrefix.'age', $age);
            }
            if($groupForm->has('maritalStatus') && $maritalStatus = $groupForm->get('maritalStatus')->getData()) {
                $groupAnd->add($expr->eq('cp.maritalStatus', ':'.$groupParamPrefix.'maritalStatus'));
                $queryBuilder->setParameter(':'.$groupParamPrefix.'maritalStatus', $maritalStatus);
            }
            if($groupForm->has('managementResponsibilities') && $managementResponsibilities = $groupForm->get('managementResponsibilities')->getData()) {
                $groupAnd->add($expr->eq('cp.managementResponsibilities', ':'.$groupParamPrefix.'managementResponsibilities'));
                $queryBuilder->setParameter(':'.$groupParamPrefix.'managementResponsibilities', $managementResponsibilities);
            }
            if($groupForm->has('residenceState') && $residenceState = $groupForm->get('residenceState')->getData()) {
                $groupAnd->add($expr->eq('cp.residenceState', ':'.$groupParamPrefix.'residenceState'));
                $queryBuilder->setParameter(':'.$groupParamPrefix.'residenceState', $residenceState);
            }
            $orGroup->add($groupAnd);
        }
        $queryBuilder->andWhere($orGroup);
    }
}
