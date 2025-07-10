<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-07-12 17:29:22
 */

namespace Azimut\Bundle\ModerationBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Azimut\Bundle\ModerationBundle\Entity\CmsFileMapPointBuffer;
use Azimut\Bundle\FormExtraBundle\Form\Type\TinymceConfigType;
use Azimut\Bundle\FormExtraBundle\Form\Type\MapPointPositionType;
use Azimut\Bundle\FormExtraBundle\Form\Type\GeolocationType;

class CmsFileMapPointBufferType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', null, [
                'label' => 'title'
            ])
            ->add('geolocation', GeolocationType::class, [
                'label' => 'geolocation'
            ])
            ->add('position', MapPointPositionType::class, [
                'label' => 'position'
            ])
            ->add('text', TinymceConfigType::class, [
                'attr' => ['rows' => '15'],
                'label' => 'text'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CmsFileMapPointBuffer::class,
            'error_bubbling' => false
        ]);
    }
}
