<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2015-09-11 14:57:39
 */

namespace Azimut\Bundle\SecurityBundle\Form\Type;

use Azimut\Bundle\SecurityBundle\Entity\Group;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupAccessRightType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['include_access_rights']) {
            $builder
                ->add('accessRights', CollectionType::class, array(
                    'entry_type' => AccessRightType::class,
                    'allow_add'    => true,
                    'allow_delete'    => true,
                    'by_reference' => false
                ))
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Group::class,
            'error_bubbling' => false,
            'include_access_rights' => true,
        ));
    }
}
