<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-08-21 14:46:25
 */

namespace Azimut\Bundle\CmsMapBundle\Form\Type;

use Azimut\Bundle\CmsBundle\Entity\CmsFileMap;
use Azimut\Bundle\CmsMapBundle\Entity\CmsFileMapPoint;
use Azimut\Bundle\FormExtraBundle\Form\Type\GeolocationType;
use Azimut\Bundle\FormExtraBundle\Form\Type\I18nTextType;
use Azimut\Bundle\FormExtraBundle\Form\Type\I18nTinymceConfigType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Azimut\Bundle\FormExtraBundle\Form\Type\MapPointPositionType;

class CmsFileMapPointType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', I18nTextType::class, array(
                'label' => 'title'
            ))
            ->add('geolocation', GeolocationType::class, array(
                'label' => 'geolocation'
            ))
            ->add('position', MapPointPositionType::class, array(
                'label' => 'position'
            ))
            //load tinymce config object only (as a data attribute)
            ->add('text', I18nTinymceConfigType::class, array(
                'i18n_childen_options' => array(
                    'attr' => array('rows' => '15')
                ),
                'label' => 'text'
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => CmsFileMapPoint::class,
            'error_bubbling' => false
        ));
    }
}
