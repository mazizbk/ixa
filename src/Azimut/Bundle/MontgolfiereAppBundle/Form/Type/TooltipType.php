<?php


namespace Azimut\Bundle\MontgolfiereAppBundle\Form\Type;


use Azimut\Bundle\FormExtraBundle\Form\Type\TinymceConfigType;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\Tooltip;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TooltipType extends AbstractType
{

    use TinyMCETrait;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
                $data = $event->getData();
                if(!$data instanceof Tooltip) {
                    throw new \Exception('This FormType cannot work without its data');
                }
                $request = $this->requestStack->getCurrentRequest();
                $tinymceConfig = $this->getTinymceConfig($data->getItem()->getAnalysisVersion());

                $label = $data->getItem()->getTheme()->getName()[$request->getLocale()];
                $label.= ' - '.$data->getItem()->getName()[$request->getLocale()];
                $event->getForm()->add('text', TinymceConfigType::class, [
                    'required' => false,
                    'label' => $label,
                    'translation_domain' => false,
                    'configs' => $tinymceConfig,
                ]);
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Tooltip::class,
        ]);
    }

}
