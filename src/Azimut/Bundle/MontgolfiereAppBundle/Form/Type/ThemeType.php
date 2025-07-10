<?php

namespace Azimut\Bundle\MontgolfiereAppBundle\Form\Type;

use Azimut\Bundle\FormExtraBundle\Form\Type\TinymceConfigType;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\Theme;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraints\File;

class ThemeType extends AbstractType
{
    /**
     * @var array
     */
    protected $questionnaireLocales;

    /**
     * @var Security
     */
    protected $security;

    public function __construct(array $questionnaireLocales, Security $security)
    {
        $this->questionnaireLocales = $questionnaireLocales;
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $properties = ['name' => TextType::class, 'description' => TinymceConfigType::class,];

        foreach ($properties as $property => $type) {
            foreach ($this->questionnaireLocales as $locale) {
                $builder->add($property.'_'.$locale, $type, [
                    'property_path' => $property.'['.$locale.']',
                    'label' => 'montgolfiere.backoffice.settings.themes.'.$property.'_'.$locale,
                ]);
            }
        }

        $builder
            ->add('uploadedFile', FileType::class, [
                'required' => false,
                'label' => 'montgolfiere.backoffice.settings.themes.background_image',
                'help' => 'montgolfiere.backoffice.settings.themes.background_image_help',
                'constraints' => [
                    new File(['mimeTypes' => ['image/*'], 'mimeTypesMessage' => 'montgolfiere.backoffice.common.please_select_an_image'])
                ],
            ])
        ;
        if($this->security->isGranted('SUPER_ADMIN')) {
            $builder
                ->add('type', ChoiceType::class, [
                    'choices' => [
                        'fixed' => Theme::TYPE_FIXED,
                        'free' => Theme::TYPE_FREE,
                    ],
                ])
                ->add('virtual', CheckboxType::class, [
                    'required' => false,
                ])
                ->add('virtualSettings', VirtualSettingsType::class)
                ->add('skipInAnalysis', CheckboxType::class, [
                    'required' => false,
                ])
                ->add('houseSettings', HouseSettingsType::class, [
                    'attr' => [
                        'class' => 'well',
                    ]
                ])
                ->add('wordSettings', WordSettingsType::class, [
                    'attr' => [
                        'class' => 'well',
                    ]
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Theme::class,
        ]);
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['label'] = 'Theme '.$form->getData()->getId();
    }


}
