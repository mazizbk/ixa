<?php
/**
 * Created by mikaelp on 25-Jul-18 5:51 PM
 */

namespace Azimut\Bundle\MontgolfiereAppBundle\Form\Type;


use Symfony\Component\Form\Extension\Core\Type\BaseType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

class ButtonLinkType extends BaseType
{
    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);
        $view->vars['route'] = $options['route'];
        $view->vars['routeParams'] = $options['route_params'];
        $view->vars['text'] = $options['text'];
        $view->vars['color'] = $options['color'];
        $view->vars['icon'] = $options['icon'];
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                'route' => null,
                'route_params' => [],
                'text' => null,
                'auto_initialize' => false,
                'color' => 'default',
                'icon' => null,
            ])
            ->setAllowedTypes('route', 'string')
            ->setAllowedTypes('text', 'string')
            ->setAllowedTypes('color', 'string')
            ->setAllowedTypes('icon', ['string', 'null'])
            ->setAllowedTypes('route_params', 'array')
            ->setAllowedValues('color', [
                'default', 'primary', 'danger', 'warning', 'info', 'success', 'link',
            ])
            ->setRequired(['route', 'text'])
        ;
    }

    public function getParent()
    {
    }

    public function getBlockPrefix()
    {
        return 'button_link';
    }

}
