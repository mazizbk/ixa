<?php


namespace Azimut\Bundle\MontgolfiereAppBundle\Form\Type;


use Azimut\Bundle\FormExtraBundle\Form\Type\TinymceConfigType;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\WBEText;
use Azimut\Bundle\MontgolfiereAppBundle\Util\ThemesManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WBETextType extends AbstractType
{
    use TinyMCETrait;

    /**
     * @var ThemesManager
     */
    private $themesManager;

    public function __construct(ThemesManager $themesManager)
    {
        $this->themesManager = $themesManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $tinymceConfig = $this->getTinymceConfig($this->themesManager->getLastAnalysisVersion());
        $builder
            ->add('title', TextType::class, [
                'required' => false,
                'label' => 'montgolfiere.backoffice.settings.wbe_texts.title'
            ])
            ->add('text', TinymceConfigType::class, [
                'required' => false,
                'label' => 'montgolfiere.backoffice.settings.wbe_texts.text',
                'attr' => [
                    'rows' => 10,
                ],
                'empty_data' => '',
                'configs' => $tinymceConfig,
            ])
            ->add('advice', TinymceConfigType::class, [
                'required' => false,
                'label' => 'montgolfiere.backoffice.settings.wbe_texts.advice',
                'attr' => [
                    'rows' => 10,
                ],
                'empty_data' => '',
                'configs' => $tinymceConfig,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => WBEText::class,
        ]);
    }

}
