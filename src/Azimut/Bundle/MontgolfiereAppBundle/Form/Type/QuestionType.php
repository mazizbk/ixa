<?php

namespace Azimut\Bundle\MontgolfiereAppBundle\Form\Type;

use Azimut\Bundle\FormExtraBundle\Form\Type\ExtendedEntityType;
use Azimut\Bundle\FormExtraBundle\Form\Type\TinymceConfigType;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\Item;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\Question;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\QuestionTag;
use Azimut\Bundle\MontgolfiereAppBundle\Form\HiddenRoundedType;
use Azimut\Bundle\MontgolfiereAppBundle\Util\CampaignAnalyser;
use Azimut\Bundle\MontgolfiereAppBundle\Util\ThemesManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuestionType extends AbstractType
{
    use TinyMCETrait;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var ThemesManager
     */
    private $themesManager;

    public function __construct(RequestStack $requestStack, ThemesManager $themesManager)
    {
        $this->requestStack = $requestStack;
        $this->themesManager = $themesManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('label', TextType::class, [
                'required' => true,
                'label' => 'montgolfiere.backoffice.questions.fields.label',
                'attr' => [
                    'autocomplete' => 'off',
                ]
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'montgolfiere.backoffice.questions.fields.type',
                'choices' => [
                    'montgolfiere.backoffice.questions.types.slider_value' => Question::TYPE_SLIDER_VALUE,
                    'montgolfiere.backoffice.questions.types.open' => Question::TYPE_OPEN,
                    'montgolfiere.backoffice.questions.types.true_false' => Question::TYPE_TRUE_FALSE,
                    'montgolfiere.backoffice.questions.types.choices_multiples' => Question::TYPE_CHOICES_MULTIPLES,
                    'montgolfiere.backoffice.questions.types.choices_unique' => Question::TYPE_CHOICES_UNIQUE,
                    'montgolfiere.backoffice.questions.types.satisfaction_gauge' => Question::TYPE_SATISFACTION_GAUGE,
                ],
            ])
            ->add('gaugeMaxValue', IntegerType::class, [
                'required' => false,
                'label' => 'montgolfiere.backoffice.questions.fields.gauge_max_value',
                'attr' => [
                    'min' => 1,
                    'scale' => 1,
                ],
            ])
            ->add('gaugeInvert', CheckboxType::class, [
                'required' => false,
                'label' => 'montgolfiere.backoffice.questions.fields.gauge_invert',
            ])
            ->add('wellBeingCoefficient', IntegerType::class, [
                'required' => false,
                'label' => 'montgolfiere.backoffice.questions.fields.well_being_coefficient',
                'attr' => [
                    'min' => 1,
                    'scale' => 1,
                ],
            ])
            ->add('engagementCoefficient', IntegerType::class, [
                'required' => false,
                'label' => 'montgolfiere.backoffice.questions.fields.engagement_coefficient',
                'attr' => [
                    'min' => 1,
                    'scale' => 1,
                ],
            ])
            ->add('valuesDistribution', CollectionType::class, [
                'entry_type' => HiddenRoundedType::class,
                'by_reference' => false,
                'label' => 'montgolfiere.backoffice.questions.fields.values_distribution',
                'required' => false,
                'empty_data' => null,
            ])
            ->add('possibleValues', TextareaType::class, [
                'required' => false,
                'label' => 'montgolfiere.backoffice.questions.fields.possible_values',
                'help' => 'montgolfiere.backoffice.questions.fields.possible_values_help',
                'help_raw' => true,
            ])
            ->add('canBeSkipped', CheckboxType::class, [
                'required' => false,
                'label' => 'montgolfiere.backoffice.questions.fields.skippable',
            ])
            ->add('tags', ExtendedEntityType::class, [
                'required' => false,
                'label' => 'montgolfiere.backoffice.questions.fields.tags',
                'multiple' => true,
                'expanded' => true,
                'choice_label' => 'name',
                'class' => QuestionTag::class,
                'choice_label_attr' => function(QuestionTag $questionTag){
                    return [
                        'class' => 'label',
                        'style' => 'background-color:#'.$questionTag->getColor(),
                    ];
                }
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
                $data = $event->getData();
                if(!$data instanceof Question) {
                    throw new \LogicException();
                }
                $request = $this->requestStack->getCurrentRequest();
                $tinymceConfig = $this->getTinymceConfig($data->getAnalysisVersion() ?? $this->themesManager->getLastAnalysisVersion());
                $event->getForm()
                    ->add('question', TinymceConfigType::class, [
                        'required' => false, // Although we'd want it to be required, TinyMCE hides the <textarea>, which is not focusable, and prevents form submission
                        'label' => 'montgolfiere.backoffice.questions.fields.question',
                        'configs' => $tinymceConfig,
                    ])
                    ->add('description', TinymceConfigType::class, [
                        'required' => false,
                        'label' => 'montgolfiere.backoffice.questions.fields.description',
                        'configs' => $tinymceConfig,
                    ])
                    ->add('leftLabel', TinymceConfigType::class, [
                        'required' => false,
                        'label' => 'montgolfiere.backoffice.questions.fields.left_label',
                        'configs' => $tinymceConfig,
                    ])
                    ->add('centerLabel', TinymceConfigType::class, [
                        'required' => false,
                        'label' => 'montgolfiere.backoffice.questions.fields.center_label',
                        'configs' => $tinymceConfig,
                    ])
                    ->add('rightLabel', TinymceConfigType::class, [
                        'required' => false,
                        'label' => 'montgolfiere.backoffice.questions.fields.right_label',
                        'configs' => $tinymceConfig,
                    ])
                    ->add('tooltip', TinymceConfigType::class, [
                        'required' => false,
                        'label' => 'montgolfiere.backoffice.questions.fields.tooltip',
                        'configs' => $tinymceConfig,
                    ])
                    ->add('item', EntityType::class, [
                        'class' => Item::class,
                        'choice_label' => function(Item $item) use ($request): string {return $item->getName()[$request->getLocale()];},
                        'group_by' => function(Item $item) use ($request): string {return $item->getTheme()->getName()[$request->getLocale()];},
                        'required' => false,
                        'query_builder' => function(EntityRepository $er) use($data): QueryBuilder {
                            return $er->createQueryBuilder('i')
                                ->leftJoin('i.theme', 't')
                                ->orderBy('t.position', 'ASC')
                                ->addOrderBy('i.position', 'ASC')
                                ->where('i.analysisVersion = :analysisVersion')
                                ->setParameter(':analysisVersion', $data->getAnalysisVersion() ?? $this->themesManager->getLastAnalysisVersion())
                            ;
                        },
                    ])
                ;
            })
        ;
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Question::class
        ));
    }

}
