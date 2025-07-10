<?php


namespace Azimut\Bundle\MontgolfiereAppBundle\Form\Type;


use Azimut\Bundle\MontgolfiereAppBundle\Model\VirtualThemeSettings;
use Azimut\Bundle\MontgolfiereAppBundle\Util\ThemesManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VirtualSettingsType extends AbstractType
{
    /**
     * @var ThemesManager
     */
    protected $themesManager;

    public function __construct(ThemesManager $themesManager)
    {
        $this->themesManager = $themesManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $themes = [];
        foreach ($this->themesManager->getThemes() as $theme) {
            $themes[$theme->getName()['fr']] = $theme->getId();
        }
        $builder
            ->add('parentThemesIds', ChoiceType::class, [
                'choices' => $themes,
                'multiple' => true,
                'choice_translation_domain' => false,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => VirtualThemeSettings::class,
        ]);
    }

}
