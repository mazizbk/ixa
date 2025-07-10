<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-08-18 16:00:35
 */

namespace Azimut\Bundle\FormExtraBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Azimut\Bundle\FormExtraBundle\Model\Geolocation;

class GeolocationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('latitude', $options['type'], array(
                'label' => 'latitude'
            ))
            ->add('longitude', $options['type'], array(
                'label' => 'longitude'
            ))
        ;

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $data = $event->getData();

            if (empty($data)) {
                return;
            }

            if ($data instanceof Geolocation) {
                $latitude = $data->getLatitude();
                $longitude = $data->getLongitude();
            } else {
                $latitude = $data['latitude'];
                $longitude = $data['longitude'];
            }

            $geolocation = new Geolocation($latitude, $longitude);
            $event->setData($geolocation);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Geolocation::class,
            'type' => TextType::class
        ]);
    }
}
