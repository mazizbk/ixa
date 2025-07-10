<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-10-04 15:55:58
 */

namespace Azimut\Bundle\FrontofficeSecurityBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Azimut\Component\Address\Form\Type\BaseAddressType;
use Azimut\Bundle\FrontofficeSecurityBundle\Entity\FrontofficeUserAddress;

class FrontofficeUserAddressType extends BaseAddressType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FrontofficeUserAddress::class,
        ]);
    }
}
