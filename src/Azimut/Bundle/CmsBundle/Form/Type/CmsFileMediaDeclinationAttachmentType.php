<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2013-12-06 13:51:01
 */

namespace Azimut\Bundle\CmsBundle\Form\Type;

use Azimut\Bundle\CmsBundle\Entity\CmsFileMediaDeclinationAttachment;
use Azimut\Bundle\MediacenterBundle\Form\Type\MediaDeclinationAttachmentEntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CmsFileMediaDeclinationAttachmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('mediaDeclination', MediaDeclinationAttachmentEntityType::class, array(
                'label' => false
            ))
            ->add('cropping', HiddenType::class)
            ->add('displayOrder', HiddenType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => CmsFileMediaDeclinationAttachment::class,
            'error_bubbling' => false
        ));
    }
}
