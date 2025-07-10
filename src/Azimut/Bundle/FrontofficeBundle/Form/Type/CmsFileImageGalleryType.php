<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-07-28
 */

namespace Azimut\Bundle\FrontofficeBundle\Form\Type;

use Azimut\Bundle\FrontofficeBundle\Entity\CmsFileImageGallery;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CmsFileImageGalleryType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => CmsFileImageGallery::class,
            'error_bubbling' => false
        ));
    }
}
