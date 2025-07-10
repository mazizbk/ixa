<?php

namespace Azimut\Bundle\MediacenterBundle\Form\Type;

use Azimut\Bundle\FormExtraBundle\Form\Type\EntityHiddenType;
use Azimut\Bundle\MediacenterBundle\Entity\MediaDeclination;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MediaDeclinationEntityType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'class' => MediaDeclination::class
        ));
    }

    public function getParent()
    {
        return EntityHiddenType::class;
    }
}
