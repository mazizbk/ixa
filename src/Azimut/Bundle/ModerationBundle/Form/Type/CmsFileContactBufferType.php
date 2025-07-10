<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-07-12 17:25:15
 */

namespace Azimut\Bundle\ModerationBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

use Azimut\Bundle\ModerationBundle\Entity\CmsFileContactBuffer;
use Azimut\Bundle\FormExtraBundle\Form\Type\TinymceConfigType;

class CmsFileContactBufferType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', null, [
                'label' => 'first.name'
            ])
            ->add('lastName', null, [
                'label' => 'last.name'
            ])
            ->add('address', TextareaType::class, [
                'label' => 'address'
            ])
            ->add('zipCode', null, [
                'label' => 'zip.code'
            ])
            ->add('city', null, [
                'label' => 'city'
            ])
            ->add('country', null, [
                'label' => 'country'
            ])
            ->add('phone', null, [
                'label' => 'phone'
            ])
            ->add('email', null, [
                'label' => 'email'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CmsFileContactBuffer::class,
            'error_bubbling' => false
        ]);
    }
}
