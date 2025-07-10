<?php

namespace Azimut\Bundle\MontgolfiereAppBundle\Form\Type;


use Azimut\Bundle\MontgolfiereAppBundle\Entity\Campaign;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignParticipation;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignSegment;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignSortingFactorValue;
use Azimut\Bundle\MontgolfiereAppBundle\Util\SortingFactorManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class QuestionnaireBasicInformationType extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;
    /**
     * @var SortingFactorManager
     */
    protected $sortingFactorManager;

    public function __construct(TranslatorInterface $translator, SortingFactorManager $sortingFactorManager)
    {
        // Translator holds the current questionnaire locale
        $this->translator = $translator;
        $this->sortingFactorManager = $sortingFactorManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event){
                $form = $event->getForm();
                /** @var CampaignParticipation $data */
                $data = $event->getData();
                if(!$data) {
                    return;
                }
                $campaign = $data->getCampaign();

                $segments = $campaign->getSegments()->filter(function(CampaignSegment $segment) {
                    return $segment->isValid() && $segment->getLocale() === $this->translator->getLocale();
                });
                if ($segments->count()>1) {
                    $form
                        ->add('segment', EntityType::class, [
                            'required' => true,
                            'choices' => $segments,
                            'choice_label' => 'name',
                            'class' => CampaignSegment::class,
                            'placeholder' => false,
                            'label' => 'montgolfiere.questionnaire.basic_information.segment',
                            'expanded' => true,
                            'help' => 'montgolfiere.questionnaire.basic_information.segment_help',
                            'constraints' => [
                                new NotNull(),
                            ],
                            'attr' => [
                                'data-tabindex' => empty($form->all()) ? 0 : -10,
                            ],
                        ])
                    ;
                }

                if ($campaign->getFieldStatus('managementResponsibilities') !== Campaign::FIELD_STATUS_DISABLED) {
                    $required = $campaign->getFieldStatus('managementResponsibilities') === Campaign::FIELD_STATUS_REQUIRED;
                    $constraints = [];
                    if($required) {
                        $constraints[] = new NotBlank();
                    }
                    $form
                        ->add('managementResponsibilities', ChoiceType::class, [
                            'required' => $required,
                            'label' => 'montgolfiere.questionnaire.basic_information.management_responsibilities',
                            'choices' => [
                                'montgolfiere.questionnaire.basic_information.management_responsibilities_values.none' => CampaignParticipation::MANAGEMENT_RESPONSIBILITIES_NONE,
                                'montgolfiere.questionnaire.basic_information.management_responsibilities_values.manager' => CampaignParticipation::MANAGEMENT_RESPONSIBILITIES_MANAGER,
                                'montgolfiere.questionnaire.basic_information.management_responsibilities_values.manager_of_managers' => CampaignParticipation::MANAGEMENT_RESPONSIBILITIES_MANAGER_OF_MANAGERS,
                            ],
                            'placeholder' => false,
                            'expanded' => true,
                            'help' => 'montgolfiere.questionnaire.basic_information.for_sociological_analysis',
                            'constraints' => $constraints,
                            'attr' => [
                                'data-tabindex' => empty($form->all()) ? 0 : -10,
                            ],
                        ])
                    ;
                }

                if ($campaign->getFieldStatus('seniority') !== Campaign::FIELD_STATUS_DISABLED) {
                    $required = $campaign->getFieldStatus('seniority') === Campaign::FIELD_STATUS_REQUIRED;
                    $constraints = [];
                    if($required) {
                        $constraints[] = new NotBlank();
                    }
                    $form
                        ->add('seniority', ChoiceType::class, [
                            'required' => $required,
                            'label' => 'montgolfiere.questionnaire.basic_information.seniority',
                            'choices' => [
                                'montgolfiere.questionnaire.basic_information.seniorities.less_than_two' => CampaignParticipation::SENIORITY_LESS_THAN_2,
                                'montgolfiere.questionnaire.basic_information.seniorities.less_than_five' => CampaignParticipation::SENIORITY_LESS_THAN_5,
                                'montgolfiere.questionnaire.basic_information.seniorities.less_than_ten' => CampaignParticipation::SENIORITY_LESS_THAN_10,
                                'montgolfiere.questionnaire.basic_information.seniorities.less_than_twenty' => CampaignParticipation::SENIORITY_LESS_THAN_20,
                                'montgolfiere.questionnaire.basic_information.seniorities.more_than_twenty' => CampaignParticipation::SENIORITY_MORE_THAN_20,
                            ],
                            'placeholder' => false,
                            'expanded' => true,
                            'help' => 'montgolfiere.questionnaire.basic_information.for_sociological_analysis',
                            'constraints' => $constraints,
                            'attr' => [
                                'data-tabindex' => empty($form->all()) ? 0 : -10,
                            ],
                        ])
                    ;
                }

                if ($campaign->getFieldStatus('age') !== Campaign::FIELD_STATUS_DISABLED) {
                    $required = $campaign->getFieldStatus('age') === Campaign::FIELD_STATUS_REQUIRED;
                    $constraints = [];
                    if($required) {
                        $constraints[] = new NotBlank();
                    }
                    $form
                        ->add('age', ChoiceType::class, [
                            'required' => $required,
                            'label' => 'montgolfiere.questionnaire.basic_information.age',
                            'choices' => [
                                'montgolfiere.questionnaire.basic_information.ages._15_17' => CampaignParticipation::AGE_15_17,
                                'montgolfiere.questionnaire.basic_information.ages._18_24' => CampaignParticipation::AGE_18_24,
                                'montgolfiere.questionnaire.basic_information.ages._25_34' => CampaignParticipation::AGE_25_34,
                                'montgolfiere.questionnaire.basic_information.ages._35_49' => CampaignParticipation::AGE_35_49,
                                'montgolfiere.questionnaire.basic_information.ages._50_64' => CampaignParticipation::AGE_50_64,
                                'montgolfiere.questionnaire.basic_information.ages.65_plus' => CampaignParticipation::AGE_65_PLUS,
                            ],
                            'placeholder' => false,
                            'expanded' => true,
                            'help' => 'montgolfiere.questionnaire.basic_information.for_sociological_analysis',
                            'constraints' => $constraints,
                            'attr' => [
                                'data-tabindex' => empty($form->all()) ? 0 : -10,
                            ],
                        ])
                    ;
                }

                if ($campaign->getFieldStatus('csp') !== Campaign::FIELD_STATUS_DISABLED) {
                    $required = $campaign->getFieldStatus('csp') === Campaign::FIELD_STATUS_REQUIRED;
                    $constraints = [];
                    if($required) {
                        $constraints[] = new NotBlank();
                    }
                    $form
                        ->add('csp', ChoiceType::class, [
                            'required' => $required,
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
                            'placeholder' => false,
                            'expanded' => true,
                            'help' => 'montgolfiere.questionnaire.basic_information.for_sociological_analysis',
                            'constraints' => $constraints,
                            'attr' => [
                                'data-tabindex' => empty($form->all()) ? 0 : -10,
                            ],
                        ])
                    ;
                }

                if ($campaign->getFieldStatus('gender') !== Campaign::FIELD_STATUS_DISABLED) {
                    $required = $campaign->getFieldStatus('gender') === Campaign::FIELD_STATUS_REQUIRED;
                    $constraints = [];
                    if($required) {
                        $constraints[] = new NotBlank();
                    }
                    $genderChoices = [
                        'montgolfiere.questionnaire.basic_information.genders.woman' => CampaignParticipation::GENDER_WOMAN,
                        'montgolfiere.questionnaire.basic_information.genders.man' => CampaignParticipation::GENDER_MAN,
                    ];
                    if ($campaign->isAllowOtherGender()) {
                        $genderChoices['montgolfiere.questionnaire.basic_information.genders.other'] = CampaignParticipation::GENDER_OTHER;
                    }
                    $genderChoices['montgolfiere.questionnaire.basic_information.genders.do_not_answer'] = CampaignParticipation::GENDER_DO_NOT_ANSWER;
                    $form
                        ->add('gender', ChoiceType::class, [
                            'required' => $required,
                            'label' => 'montgolfiere.questionnaire.basic_information.gender',
                            'placeholder' => false,
                            'choices' => $genderChoices,
                            'expanded' => true,
                            'help' => 'montgolfiere.questionnaire.basic_information.for_sociological_analysis',
                            'constraints' => $constraints,
                            'attr' => [
                                'data-tabindex' => empty($form->all()) ? 0 : -10,
                            ],
                        ])
                    ;
                }

                if ($campaign->getFieldStatus('maritalStatus') !== Campaign::FIELD_STATUS_DISABLED) {
                    $required = $campaign->getFieldStatus('maritalStatus') === Campaign::FIELD_STATUS_REQUIRED;
                    $constraints = [];
                    if($required) {
                        $constraints[] = new NotBlank();
                    }
                    $form
                        ->add('maritalStatus', ChoiceType::class, [
                            'required' => $required,
                            'label' => 'montgolfiere.questionnaire.basic_information.marital_status',
                            'choices' => [
                                'montgolfiere.questionnaire.basic_information.marital_statuses.single' => CampaignParticipation::MARITAL_STATUS_SINGLE,
                                'montgolfiere.questionnaire.basic_information.marital_statuses.cohabitation' => CampaignParticipation::MARITAL_STATUS_COHABITATION,
                                'montgolfiere.questionnaire.basic_information.marital_statuses.married' => CampaignParticipation::MARITAL_STATUS_MARRIED,
                                'montgolfiere.questionnaire.basic_information.marital_statuses.divorced' => CampaignParticipation::MARITAL_STATUS_DIVORCED,
                                'montgolfiere.questionnaire.basic_information.marital_statuses.widower' => CampaignParticipation::MARITAL_STATUS_WIDOWER,
                            ],
                            'placeholder' => false,
                            'expanded' => true,
                            'help' => 'montgolfiere.questionnaire.basic_information.for_sociological_analysis',
                            'constraints' => $constraints,
                            'attr' => [
                                'data-tabindex' => empty($form->all()) ? 0 : -10,
                            ],
                        ])
                    ;
                }

                if ($campaign->getFieldStatus('education') !== Campaign::FIELD_STATUS_DISABLED) {
                    $required = $campaign->getFieldStatus('education') === Campaign::FIELD_STATUS_REQUIRED;
                    $constraints = [];
                    if($required) {
                        $constraints[] = new NotBlank();
                    }
                    $form
                        ->add('education', ChoiceType::class, [
                            'required' => $required,
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
                            'placeholder' => false,
                            'expanded' => true,
                            'help' => 'montgolfiere.questionnaire.basic_information.for_sociological_analysis',
                            'constraints' => $constraints,
                            'attr' => [
                                'data-tabindex' => empty($form->all()) ? 0 : -10,
                            ],
                        ])
                    ;
                }

                if ($campaign->getFieldStatus('residenceState') !== Campaign::FIELD_STATUS_DISABLED) {
                    $states = range(1, 19);
                    $states[] = '2A';
                    $states[] = '2B';
                    $states = array_merge($states, range(21, 95));
                    $states = array_merge($states, [971, 972, 973, 974, 976]);
                    $statesLabels = array_map(function($state){return 'montgolfiere.questionnaire.basic_information.states.'.$state;}, $states);

                    $states = array_combine($statesLabels, $states);

                    $required = $campaign->getFieldStatus('residenceState') === Campaign::FIELD_STATUS_REQUIRED;
                    $constraints = [];
                    if($required) {
                        $constraints[] = new NotBlank();
                    }
                    $form
                        ->add('residenceState', ChoiceType::class, [
                            'required' => $required,
                            'label' => 'montgolfiere.questionnaire.basic_information.residence_state',
                            'choices' => $states,
                            'placeholder' => false,
                            'expanded' => true,
                            'help' => 'montgolfiere.questionnaire.basic_information.for_sociological_analysis',
                            'constraints' => $constraints,
                            'attr' => [
                                'data-tabindex' => empty($form->all()) ? 0 : -10,
                            ],
                        ])
                    ;
                }

                foreach ($campaign->getSortingFactors() as $sortingFactor) {
                    $values = $sortingFactor->getValues();
                    if(count($values)<2) {
                        continue;
                    }
                    $form->add('sorting_factor_'.$sortingFactor->getId(), EntityType::class, [
                        'required' => true,
                        'choices' => $values,
                        'choice_label' => function(CampaignSortingFactorValue $value){
                            return $this->sortingFactorManager->getSortingFactorValueName($this->translator->getLocale(), $value);
                        },
                        'choice_attr' => function(CampaignSortingFactorValue $value): array {
                            return ['data-order' => $value->getPosition(),];
                        },
                        'class' => CampaignSortingFactorValue::class,
                        'placeholder' => false,
                        'label' => $this->sortingFactorManager->getSortingFactorName($this->translator->getLocale(), $sortingFactor),
                        'mapped' => false,
                        'attr' => [
                            'data-sorting-factor-id' => $sortingFactor->getId(),
                            'data-tabindex' => empty($form->all()) ? 0 : -10,
                        ],
                        'expanded' => true,
//                        'help' => $sortingFactor->getHelpText(), // TODO
                        'help_translation_domain' => false,
                        'constraints' => [
                            new NotBlank(),
                        ],
                    ]);
                }

                if ($campaign->getFieldStatus('position') !== Campaign::FIELD_STATUS_DISABLED) {
                    $constraints = [];
                    if($campaign->getFieldStatus('position') === Campaign::FIELD_STATUS_REQUIRED) {
                        $constraints[] = new NotBlank();
                    }
                    $form
                        ->add('position', TextType::class, [
                            'required' => $campaign->getFieldStatus('position') === Campaign::FIELD_STATUS_REQUIRED,
                            'label' => 'montgolfiere.questionnaire.basic_information.position',
                            'help' => 'montgolfiere.questionnaire.basic_information.for_sociological_analysis',
                            'constraints' => $constraints,
                            'attr' => [
                                'tabindex' => empty($form->all()) ? 0 : -10,
                            ],
                        ])
                    ;
                }

                if($campaign->getFieldStatus('managerName') !== Campaign::FIELD_STATUS_DISABLED) {
                    $constraints = [];
                    if($campaign->getFieldStatus('managerName') === Campaign::FIELD_STATUS_REQUIRED) {
                        $constraints[] = new NotBlank();
                    }
                    $form
                        ->add('managerName', TextType::class, [
                            'required' => $campaign->getFieldStatus('managerName') === Campaign::FIELD_STATUS_REQUIRED,
                            'label' => 'montgolfiere.questionnaire.basic_information.manager_name',
                            'constraints' => $constraints,
                            'attr' => [
                                'placeholder' => 'Camille Martin',
                                'tabindex' => empty($form->all()) ? 0 : -10,
                            ],
                        ])
                    ;
                }

                if($campaign->getFieldStatus('firstName') !== Campaign::FIELD_STATUS_DISABLED) {
                    $constraints = [];
                    if($campaign->getFieldStatus('firstName') === Campaign::FIELD_STATUS_REQUIRED) {
                        $constraints[] = new NotBlank();
                    }
                    $form
                        ->add('firstName', TextType::class, [
                            'required' => $campaign->getFieldStatus('firstName') === Campaign::FIELD_STATUS_REQUIRED,
                            'label' => 'montgolfiere.questionnaire.basic_information.first_name',
                            'constraints' => $constraints,
                            'attr' => [
                                'placeholder' => 'Dominique',
                                'tabindex' => empty($form->all()) ? 0 : -10,
                            ],
                        ])
                    ;
                }

                if($campaign->getFieldStatus('lastName') !== Campaign::FIELD_STATUS_DISABLED) {
                    $constraints = [];
                    if($campaign->getFieldStatus('lastName') === Campaign::FIELD_STATUS_REQUIRED) {
                        $constraints[] = new NotBlank();
                    }
                    $form
                        ->add('lastName', TextType::class, [
                            'required' => $campaign->getFieldStatus('lastName') === Campaign::FIELD_STATUS_REQUIRED,
                            'label' => 'montgolfiere.questionnaire.basic_information.last_name',
                            'constraints' => $constraints,
                            'attr' => [
                                'placeholder' => 'Dupont',
                                'tabindex' => empty($form->all()) ? 0 : -10,
                            ],
                        ])
                    ;
                }

                if($campaign->getFieldStatus('phoneNumber') !== Campaign::FIELD_STATUS_DISABLED) {
                    $constraints = [];
                    if($campaign->getFieldStatus('phoneNumber') === Campaign::FIELD_STATUS_REQUIRED) {
                        $constraints[] = new NotBlank();
                    }
                    $form
                        ->add('phoneNumber', TextType::class, [
                            'required' => $campaign->getFieldStatus('phoneNumber') === Campaign::FIELD_STATUS_REQUIRED,
                            'label' => 'montgolfiere.questionnaire.basic_information.phone_number',
                            'constraints' => $constraints,
                            'attr' => [
                                'placeholder' => '06 12 34 56 78',
                                'tabindex' => empty($form->all()) ? 0 : -10,
                            ],
                        ])
                    ;
                }

                if($campaign->getFieldStatus('emailAddress') !== Campaign::FIELD_STATUS_DISABLED) {
                    $constraints = [];
                    if($campaign->getFieldStatus('emailAddress') === Campaign::FIELD_STATUS_REQUIRED) {
                        $constraints[] = new NotBlank();
                    }
                    $form
                        ->add('emailAddress', EmailType::class, [
                            'required' => $campaign->getFieldStatus('emailAddress') === Campaign::FIELD_STATUS_REQUIRED,
                            'label' => 'montgolfiere.questionnaire.basic_information.email_address',
                            'help' => 'montgolfiere.questionnaire.basic_information.email_address_help',
                            'constraints' => $constraints,
                            'attr' => [
                                'placeholder' => 'prenom.nom@orange.fr',
                                'tabindex' => empty($form->all()) ? 0 : -10,
                            ],
                        ])
                    ;
                }
            })
            ->addEventListener(FormEvents::POST_SUBMIT, function(FormEvent $event) {
                $form = $event->getForm();
                $participation = $event->getData();
                if(!$participation instanceof CampaignParticipation) {
                    return;
                }

                $campaign = $participation->getCampaign();

                foreach ($campaign->getSortingFactors() as $sortingFactor) {
                    if(!$form->has('sorting_factor_'.$sortingFactor->getId())) {
                        continue;
                    }
                    $field = $form->get('sorting_factor_'.$sortingFactor->getId());
                    $value = $field->getData();
                    $participation->setSortingFactorValue($sortingFactor, $value);
                }
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CampaignParticipation::class,
        ]);
    }

}
