<?php


namespace Azimut\Bundle\MontgolfiereAppBundle\Form\Type;


use Azimut\Bundle\MontgolfiereAppBundle\Entity\Item;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class ItemType extends AbstractType
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

        foreach ($this->questionnaireLocales as $locale) {
            $builder->add('name_'.$locale, TextType::class, [
                'property_path' => 'name['.$locale.']',
                'label' => 'montgolfiere.backoffice.settings.themes.item.name_'.$locale,
            ]);
        }

        $builder
            ->add('definesEngagementProfile', CheckboxType::class, [
                'required' => false,
                'label' => 'montgolfiere.backoffice.settings.themes.item.defines_engagement_profile',
            ])
            ->add('engagementWeight', IntegerType::class, [
                'required' => false,
                'label' => 'montgolfiere.backoffice.settings.themes.item.engagement_weight',
            ])
            ->add('wellBeingWeight', IntegerType::class, [
                'required' => false,
                'label' => 'montgolfiere.backoffice.settings.themes.item.well_being_weight',
            ])
        ;
        if($this->security->isGranted('SUPER_ADMIN')) {
            $builder
                ->add('houseSettings', HouseSettingsType::class, [
                    'attr' => [
                        'class' => 'well',
                    ],
                    'item' => true,
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Item::class,
        ]);
    }

}
