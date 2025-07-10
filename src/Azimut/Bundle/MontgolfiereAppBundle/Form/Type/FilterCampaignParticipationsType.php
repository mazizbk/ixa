<?php
namespace Azimut\Bundle\MontgolfiereAppBundle\Form\Type;


use Azimut\Bundle\MontgolfiereAppBundle\Entity\Campaign;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignParticipation;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignSegment;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignSortingFactorValue;
use Azimut\Bundle\MontgolfiereAppBundle\Util\SortingFactorManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\ChoiceList\DoctrineChoiceLoader;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class FilterCampaignParticipationsType extends AbstractType
{

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var SortingFactorManager
     */
    protected $sortingFactorManager;

    /**
     * @var PropertyAccessor
     */
    protected $propertyAccessor;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    public function __construct(RequestStack $requestStack, SortingFactorManager $sortingFactorManager, PropertyAccessor $propertyAccessor, EntityManagerInterface $entityManager)
    {
        $this->request = $requestStack->getMasterRequest();
        $this->sortingFactorManager = $sortingFactorManager;
        $this->propertyAccessor = $propertyAccessor;
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Campaign $campaign */
        $campaign = $options['campaign'];
        // locale from Request::getLocale is not reliable (!)
        $locale = $this->request->attributes->getAlpha('_locale');

        foreach ($campaign->getSortingFactors() as $sortingFactor) {
            $builder->add('sorting_factor_'.$sortingFactor->getId(), EntityType::class, [
                'class' => CampaignSortingFactorValue::class,
                'choices' => $sortingFactor->getValues(),
                'choice_label' => function(CampaignSortingFactorValue $value) use($locale): string {
                    return $this->sortingFactorManager->getSortingFactorValueName($locale, $value);
                },
                'required' => false,
                'label' => $this->sortingFactorManager->getSortingFactorName($locale, $sortingFactor),
                'placeholder' => 'montgolfiere.backoffice.common.filter_form.placeholder_all',
            ]);
        }

        $builder
            ->add('segment', EntityType::class, [
                'class' => CampaignSegment::class,
                'choices' => $campaign->getSegments(),
                'choice_label' => 'name',
                'required' => false,
                'label' => 'montgolfiere.questionnaire.basic_information.segment',
                'placeholder' => 'montgolfiere.backoffice.common.filter_form.placeholder_all',
            ])
        ;
        if($options['with_only_sorting_factors'] == false) {
            if ($campaign->getFieldStatus('managerName') === Campaign::FIELD_STATUS_REQUIRED) {
                $builder
                    ->add('managerName', ChoiceType::class, [
                        'required' => false,
                        'label' => 'montgolfiere.questionnaire.basic_information.manager_name',
                        'choice_loader' => new CallbackChoiceLoader(function () use ($campaign) {
                            $managers = array_map(function (CampaignParticipation $participation) {
                                return $participation->getManagerName();
                            }, $campaign->getParticipations());
                            $managers = array_unique(array_filter($managers));
                            $managers = array_values($managers);

                            return array_combine($managers, $managers);
                        }),
                        'placeholder' => 'montgolfiere.backoffice.common.filter_form.placeholder_all',
                    ]);
            }
            if ($campaign->getFieldStatus('gender') === Campaign::FIELD_STATUS_REQUIRED) {
                $genderChoices = [
                    'montgolfiere.questionnaire.basic_information.genders.man' => CampaignParticipation::GENDER_MAN,
                    'montgolfiere.questionnaire.basic_information.genders.woman' => CampaignParticipation::GENDER_WOMAN,
                ];
                if ($campaign->isAllowOtherGender()) {
                    $genderChoices['montgolfiere.questionnaire.basic_information.genders.other'] = CampaignParticipation::GENDER_OTHER;
                }
                $genderChoices['montgolfiere.questionnaire.basic_information.genders.do_not_answer'] = CampaignParticipation::GENDER_DO_NOT_ANSWER;
                $builder
                    ->add('gender', ChoiceType::class, [
                        'required' => false,
                        'label' => 'montgolfiere.questionnaire.basic_information.gender',
                        'choices' => $genderChoices,
                        'placeholder' => 'montgolfiere.backoffice.common.filter_form.placeholder_all',
                    ]);
            }
            if ($campaign->getFieldStatus('seniority') === Campaign::FIELD_STATUS_REQUIRED) {
                $builder
                    ->add('seniority', ChoiceType::class, [
                        'required' => false,
                        'label' => 'montgolfiere.questionnaire.basic_information.seniority',
                        'choices' => [
                            'montgolfiere.questionnaire.basic_information.seniorities.less_than_two' => CampaignParticipation::SENIORITY_LESS_THAN_2,
                            'montgolfiere.questionnaire.basic_information.seniorities.less_than_five' => CampaignParticipation::SENIORITY_LESS_THAN_5,
                            'montgolfiere.questionnaire.basic_information.seniorities.less_than_ten' => CampaignParticipation::SENIORITY_LESS_THAN_10,
                            'montgolfiere.questionnaire.basic_information.seniorities.less_than_twenty' => CampaignParticipation::SENIORITY_LESS_THAN_20,
                            'montgolfiere.questionnaire.basic_information.seniorities.more_than_twenty' => CampaignParticipation::SENIORITY_MORE_THAN_20,
                        ],
                        'placeholder' => 'montgolfiere.backoffice.common.filter_form.placeholder_all',
                    ]);
            }

            if ($campaign->getFieldStatus('education') === Campaign::FIELD_STATUS_REQUIRED) {
                $builder
                    ->add('education', ChoiceType::class, [
                        'required' => false,
                        'label' => 'montgolfiere.questionnaire.basic_information.education',
                        'choices' => [
                            'montgolfiere.questionnaire.basic_information.educations.cap_bep' => CampaignParticipation::EDUCATION_LEVEL_CAP_BEP,
                            'montgolfiere.questionnaire.basic_information.educations.bac' => CampaignParticipation::EDUCATION_BAC,
                            'montgolfiere.questionnaire.basic_information.educations.bac2' => CampaignParticipation::EDUCATION_BAC2,
                            'montgolfiere.questionnaire.basic_information.educations.bac3' => CampaignParticipation::EDUCATION_BAC3,
                            'montgolfiere.questionnaire.basic_information.educations.bac4' => CampaignParticipation::EDUCATION_BAC4,
                            'montgolfiere.questionnaire.basic_information.educations.bac5' => CampaignParticipation::EDUCATION_BAC5,
                            'montgolfiere.questionnaire.basic_information.educations.bac8' => CampaignParticipation::EDUCATION_BAC8,
                            'montgolfiere.questionnaire.basic_information.educations.other' => CampaignParticipation::EDUCATION_OTHER,
                        ],
                        'placeholder' => 'montgolfiere.backoffice.common.filter_form.placeholder_all',
                    ]);
            }

            if ($campaign->getFieldStatus('csp') === Campaign::FIELD_STATUS_REQUIRED) {
                $builder
                    ->add('csp', ChoiceType::class, [
                        'required' => false,
                        'label' => 'montgolfiere.questionnaire.basic_information.csp',
                        'choices' => [
                            'montgolfiere.questionnaire.basic_information.csps.operating_farmer' => CampaignParticipation::CSP_OPERATING_FARMER,
                            'montgolfiere.questionnaire.basic_information.csps.artisan_merchant_company_director' => CampaignParticipation::CSP_ARTISAN_MERCHANT_COMPANY_DIRECTOR,
                            'montgolfiere.questionnaire.basic_information.csps.executive_intellectual_profession' => CampaignParticipation::CSP_EXECUTIVE_INTELLECTUAL_PROFESSION,
                            'montgolfiere.questionnaire.basic_information.csps.intermediate_profession' => CampaignParticipation::CSP_INTERMEDIATE_PROFESSION,
                            'montgolfiere.questionnaire.basic_information.csps.qualified_employee' => CampaignParticipation::CSP_QUALIFIED_EMPLOYEE,
                            'montgolfiere.questionnaire.basic_information.csps.unqualified_employee' => CampaignParticipation::CSP_UNQUALIFIED_EMPLOYEE,
                            'montgolfiere.questionnaire.basic_information.csps.skilled_worker' => CampaignParticipation::CSP_SKILLED_WORKER,
                            'montgolfiere.questionnaire.basic_information.csps.unskilled_worker' => CampaignParticipation::CSP_UNSKILLED_WORKER,
                        ],
                        'placeholder' => 'montgolfiere.backoffice.common.filter_form.placeholder_all',
                    ]);
            }

            if ($campaign->getFieldStatus('age') === Campaign::FIELD_STATUS_REQUIRED) {
                $builder
                    ->add('age', ChoiceType::class, [
                        'required' => false,
                        'label' => 'montgolfiere.questionnaire.basic_information.age',
                        'choices' => [
                            'montgolfiere.questionnaire.basic_information.ages._15_17' => CampaignParticipation::AGE_15_17,
                            'montgolfiere.questionnaire.basic_information.ages._18_24' => CampaignParticipation::AGE_18_24,
                            'montgolfiere.questionnaire.basic_information.ages._25_34' => CampaignParticipation::AGE_25_34,
                            'montgolfiere.questionnaire.basic_information.ages._35_49' => CampaignParticipation::AGE_35_49,
                            'montgolfiere.questionnaire.basic_information.ages._50_64' => CampaignParticipation::AGE_50_64,
                            'montgolfiere.questionnaire.basic_information.ages.65_plus' => CampaignParticipation::AGE_65_PLUS,
                        ],
                        'placeholder' => 'montgolfiere.backoffice.common.filter_form.placeholder_all',
                    ]);
            }

            if ($campaign->getFieldStatus('maritalStatus') === Campaign::FIELD_STATUS_REQUIRED) {
                $builder
                    ->add('maritalStatus', ChoiceType::class, [
                        'required' => false,
                        'label' => 'montgolfiere.questionnaire.basic_information.marital_status',
                        'choices' => [
                            'montgolfiere.questionnaire.basic_information.marital_statuses.single' => CampaignParticipation::MARITAL_STATUS_SINGLE,
                            'montgolfiere.questionnaire.basic_information.marital_statuses.cohabitation' => CampaignParticipation::MARITAL_STATUS_COHABITATION,
                            'montgolfiere.questionnaire.basic_information.marital_statuses.married' => CampaignParticipation::MARITAL_STATUS_MARRIED,
                            'montgolfiere.questionnaire.basic_information.marital_statuses.divorced' => CampaignParticipation::MARITAL_STATUS_DIVORCED,
                            'montgolfiere.questionnaire.basic_information.marital_statuses.widower' => CampaignParticipation::MARITAL_STATUS_WIDOWER,
                        ],
                        'placeholder' => 'montgolfiere.backoffice.common.filter_form.placeholder_all',
                    ]);
            }

            if ($campaign->getFieldStatus('managementResponsibilities') === Campaign::FIELD_STATUS_REQUIRED) {
                $builder
                    ->add('managementResponsibilities', ChoiceType::class, [
                        'required' => false,
                        'label' => 'montgolfiere.questionnaire.basic_information.management_responsibilities',
                        'choices' => [
                            'montgolfiere.questionnaire.basic_information.management_responsibilities_values.none' => CampaignParticipation::MANAGEMENT_RESPONSIBILITIES_NONE,
                            'montgolfiere.questionnaire.basic_information.management_responsibilities_values.manager' => CampaignParticipation::MANAGEMENT_RESPONSIBILITIES_MANAGER,
                            'montgolfiere.questionnaire.basic_information.management_responsibilities_values.manager_of_managers' => CampaignParticipation::MANAGEMENT_RESPONSIBILITIES_MANAGER_OF_MANAGERS,
                        ],
                        'placeholder' => 'montgolfiere.backoffice.common.filter_form.placeholder_all',
                    ]);
            }

            if ($campaign->getFieldStatus('residenceState') === Campaign::FIELD_STATUS_REQUIRED) {
                $states = range(1, 19);
                $states[] = '2A';
                $states[] = '2B';
                $states = array_merge($states, range(21, 95));
                $states = array_merge($states, [971, 972, 973, 974, 976]);
                $statesLabels = array_map(function ($state) {
                    return 'montgolfiere.questionnaire.basic_information.states.'.$state;
                }, $states);

                $states = array_combine($statesLabels, $states);

                $builder
                    ->add('residenceState', ChoiceType::class, [
                        'required' => false,
                        'label' => 'montgolfiere.questionnaire.basic_information.residence_state',
                        'choices' => $states,
                        'placeholder' => 'montgolfiere.backoffice.common.filter_form.placeholder_all',
                    ]);
            }
        }

        if($options['values-as-ids']) {
            // Because we want to store IDs and not full objects, we remove existing EntityType fields and replace them with ChoiceType ones
            /**
             * @var string $name
             * @var FormBuilderInterface $config */
            foreach ($builder->all() as $name => $config) {
                if(!$config->getType()->getInnerType() instanceof EntityType) {
                    continue;
                }
                /** @var DoctrineChoiceLoader|null $choiceLoader */
                $choiceLoader = $config->getOption('choice_loader');
                if($choiceLoader) {
                    $choices = $choiceLoader->loadChoiceList()->getChoices();
                }
                else {
                    $choices = $config->getOption('choices');
                }
                $choices = is_array($choices)?new ArrayCollection($choices):$choices;
                $choiceName = $config->getOption('choice_label');

                $choicesNames = $choices->map(function($choice) use($choiceName) {
                    return is_callable($choiceName)?$choiceName($choice):$this->propertyAccessor->getValue($choice, $choiceName);
                })->toArray();
                $choicesIds = $choices->map(function($choice) {
                    $classMetadata = $this->entityManager->getClassMetadata(get_class($choice));
                    $identifiers = $classMetadata->getIdentifier();
                    if(count($identifiers) !== 1) {
                        throw new \RuntimeException('Classes with composite IDs are not supported');
                    }

                    return $classMetadata->getIdentifierValues($choice)[$identifiers[0]];
                })->toArray();
                $choices = array_combine($choicesNames, $choicesIds);

                $builder
                    ->remove($name)
                    ->add($name, ChoiceType::class, [
                        'label' => $config->getOption('label'),
                        'placeholder' => $config->getOption('placeholder'),
                        'required' => $config->getRequired(),
                        'choice_translation_domain' => false,
                        'choices' => $choices,
                    ])
                ;
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'csrf_protection' => false,
                'method' => 'GET',
                'campaign' => null,
                'values-as-ids' => false,
                'with_only_sorting_factors' => false,
            ])
            ->setAllowedTypes('campaign', Campaign::class)
            ->setAllowedTypes('values-as-ids', 'bool')
        ;
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
