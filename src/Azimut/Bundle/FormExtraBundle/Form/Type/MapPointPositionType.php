<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:    2016-08-24 10:39:40
 */

namespace Azimut\Bundle\FormExtraBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Azimut\Bundle\FormExtraBundle\Model\MapPointPosition;

class MapPointPositionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('x', $options['type'], array(
                'label' => 'x'
            ))
            ->add('y', $options['type'], array(
                'label' => 'y'
            ))
        ;

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $data = $event->getData();

            if (empty($data)) {
                return;
            }

            if ($data instanceof MapPointPosition) {
                $x = $data->getX();
                $y = $data->getY();
            } else {
                $x = $data['x'];
                $y = $data['y'];
            }

            $mapPointPosition = new MapPointPosition($x, $y);
            $event->setData($mapPointPosition);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MapPointPosition::class,
            'type' => TextType::class
        ]);
    }
}
