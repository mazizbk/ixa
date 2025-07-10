<?php

namespace Azimut\Bundle\MontgolfiereAppBundle\Form\Type;

use Azimut\Bundle\FormExtraBundle\Form\Type\BootstrapDatePickerType;
use Azimut\Bundle\FormExtraBundle\Form\Type\DateTimePickerType;
use Azimut\Bundle\FormExtraBundle\Form\Type\TinymceConfigType;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\AnalysisVersion;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\Campaign;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignSegmentStep;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\Client;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\Consultant;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\Question;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CampaignType extends AbstractType implements HasTypeOption
{
    /**
     * @var array
     */
    protected $questionnaireLocales;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(array $questionnaireLocales, EntityManagerInterface $entityManager)
    {
        $this->questionnaireLocales = $questionnaireLocales;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'label' => 'montgolfiere.backoffice.campaigns.fields.name',
            ])
        ;
        if($options['type'] === 'create') {
            $builder->add('client', EntityType::class, [
                'class' => Client::class,
                'label' => 'montgolfiere.backoffice.campaigns.fields.client',
                'choice_label' => 'corporateName',
            ]);
        }
        $allowedLanguages = [];
        foreach ($this->questionnaireLocales as $locale) {
            $allowedLanguages['montgolfiere.backoffice.campaigns.locale.'.$locale] = $locale;
        }
        $builder
            ->add('startDate', DateTimePickerType::class, [
                'required' => false,
                'label' => 'montgolfiere.backoffice.campaigns.fields.start_date',
                'format' => 'dd/MM/yyyy HH:mm',
                'attr' => [
                    'class' => 'datetimepicker',
                ]
            ])
            ->add('endDate', BootstrapDatePickerType::class, [
                'required' => false,
                'label' => 'montgolfiere.backoffice.campaigns.fields.end_date',
                'todayHighlight' => true,
                'attr' => [
                    'autocomplete' => 'off',
                ]
            ])
            ->add('expectedAnswers', NumberType::class, [
                'required' => false,
                'label' => 'montgolfiere.backoffice.campaigns.fields.expected_answers',
            ])
            ->add('allowedLanguages', ChoiceType::class, [
                'required' => true,
                'label' => 'montgolfiere.backoffice.campaigns.fields.allowed_languages',
                'choices' => $allowedLanguages,
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('useNewGauge', CheckboxType::class, [
                'required' => false,
                'label' => 'montgolfiere.backoffice.campaigns.fields.use_new_gauge',
            ])
            ->add('clientAreaAllowHouseView', CheckboxType::class, [
                'required' => false,
                'label' => 'montgolfiere.backoffice.campaigns.fields.client_area_allow_house_view',
            ])
            ->add('consultantAreaAllowHouseView', CheckboxType::class, [
                'required' => false,
                'label' => 'montgolfiere.backoffice.campaigns.fields.consultant_area_allow_house_view',
            ])
            ->add('consultantAreaAllowCartographyView', CheckboxType::class, [
                'required' => false,
                'label' => 'montgolfiere.backoffice.campaigns.fields.consultant_area_allow_cartography_view',
            ])
            ->add('allowOtherGender', CheckboxType::class, [
                'required' => false,
                'label' => 'montgolfiere.backoffice.campaigns.fields.allow_other_gender',
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();
                if(!$data || !$data instanceof Campaign) {
                    return;
                }
                $textColorMap = []; // textcolor_map is not associative
                $analysisVersion = $data->getAnalysisVersion() ?? $this->entityManager->getRepository(AnalysisVersion::class)->getLastVersion();
                foreach ($analysisVersion->getColors() as $i => $color) {
                    $textColorMap[] = $color;
                    $textColorMap[] = 'Workcare '.($i+1);
                }

                foreach ($data->getAllowedLanguages() as $allowedLanguage) {
                    if(!array_key_exists($allowedLanguage, $data->getIntroduction())) {
                        $data->setIntroductionLocale('', $allowedLanguage);
                    }
                    if(!array_key_exists($allowedLanguage, $data->getOpeningMessage())) {
                        $data->setOpeningMessageLocale('', $allowedLanguage);
                    }
                    $form
                        ->add('introduction_'.$allowedLanguage, TinymceConfigType::class, [
                            'required' => false,
                            'label' => 'montgolfiere.backoffice.campaigns.fields.introduction_'.$allowedLanguage,
                            'attr' => [
                                'rows' => 15,
                            ],
                            'property_path' => 'introduction['.$allowedLanguage.']',
                            'configs' => [
                                'paste_as_text' => false,
                                'textcolor_map' => $textColorMap,
                            ],
                        ])
                        ->add('opening_message_'.$allowedLanguage, TinymceConfigType::class, [
                            'required' => false,
                            'label' => 'montgolfiere.backoffice.campaigns.fields.opening_message_'.$allowedLanguage,
                            'attr' => [
                                'rows' => 2,
                            ],
                            'property_path' => 'openingMessage['.$allowedLanguage.']',
                            'configs' => [
                                'paste_as_text' => false,
                                'textcolor_map' => $textColorMap,
                            ],
                            'empty_data' => '',
                        ])
                    ;
                }

                $questions = [];
                $verbatimQuestions = [];
                foreach($data->getSegments() as $segment) {
                    foreach ($segment->getSteps() as $step) {
                        if ($step->getType() != CampaignSegmentStep::TYPE_QUESTION) {
                            continue;
                        }
                        if ($step->getQuestion()->getType() == Question::TYPE_OPEN){
                            if(!in_array($step->getQuestion()->getId(), $verbatimQuestions)){
                                $verbatimQuestions[$step->getQuestion()->getLabel()] = $step->getQuestion()->getId();
                            }
                        }
                        else {
                            if(!in_array($step->getQuestion()->getId(), $questions)){
                                $questions[$step->getQuestion()->getLabel()] = $step->getQuestion()->getId();
                            }
                        }
                    }
                }

                $form
                    ->add('additionalQuestionsAvailableForClient', ChoiceType::class, [
                        'label' => 'montgolfiere.backoffice.campaigns.fields.additional_questions_available_for_client',
                        'choices' => $questions,
                        'multiple' => true,
                        'expanded' => true,
                    ])
                    ->add('additionalQuestionsAvailableForConsultant', ChoiceType::class, [
                        'label' => 'montgolfiere.backoffice.campaigns.fields.additional_questions_available_for_consultant',
                        'choices' => $questions,
                        'multiple' => true,
                        'expanded' => true,
                    ])
                    ->add('questionsAvailableForConsultantVerbatimExport', ChoiceType::class, [
                        'label' => 'montgolfiere.backoffice.campaigns.fields.questions_available_for_consultant_verbatim_export',
                        'choices' => $verbatimQuestions,
                        'multiple' => true,
                        'expanded' => true,
                    ])
                ;
            })
        ;
        foreach (Campaign::$configurableFields as $configurableField) {
            $builder
                ->add('fieldstatus_'.$configurableField, ChoiceType::class, [
                    'property_path' => 'fieldsStatus['.$configurableField.']',
                    'choices' => [
                        'montgolfiere.backoffice.campaigns.fields_status.optional' => Campaign::FIELD_STATUS_OPTIONAL,
                        'montgolfiere.backoffice.campaigns.fields_status.required' => Campaign::FIELD_STATUS_REQUIRED,
                        'montgolfiere.backoffice.campaigns.fields_status.disabled' => Campaign::FIELD_STATUS_DISABLED,
                    ],
                    'label' => 'montgolfiere.backoffice.campaigns.fields_status.fields.'.$configurableField,
                ])
            ;
        }
        $builder->add('consultants', EntityType::class, [
            'class' => Consultant::class,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('c')
                    ->orderBy('c.lastName', 'ASC');
            },
            'choice_label' => 'lastName',
            'multiple' => true,
            'expanded' => true,
            'label' => 'montgolfiere.backoffice.campaigns.fields.consultants',
        ]);
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => Campaign::class,
            ])
            ->setDefined('type')
            ->setAllowedValues('type', ['create', 'update'])
        ;
    }

}
