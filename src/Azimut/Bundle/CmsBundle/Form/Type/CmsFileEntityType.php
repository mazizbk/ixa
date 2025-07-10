<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-07-01 15:30:49
 */

namespace Azimut\Bundle\CmsBundle\Form\Type;

use Azimut\Bundle\CmsBundle\Entity\CmsFile;
use Azimut\Bundle\FormExtraBundle\Form\Type\EntityJsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CmsFileEntityType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'class' => CmsFile::class
        ));
    }

    public function getParent()
    {
        return EntityJsType::class;
    }
}
