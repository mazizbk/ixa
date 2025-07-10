<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-02-17 11:51:55
 */

namespace Azimut\Bundle\MediacenterBundle\Form\Type;

use Azimut\Bundle\FormExtraBundle\Form\Type\EntityJsType;
use Azimut\Bundle\MediacenterBundle\Entity\MediaDeclination;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MediaDeclinationAttachmentEntityType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'class' => MediaDeclination::class
        ));
    }

    public function getParent()
    {
        return EntityJsType::class;
    }
}
