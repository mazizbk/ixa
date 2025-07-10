<?php
/**
 * Created by PhpStorm.
 * User: gerdald
 * Date: 08/07/14
 * Time: 12:07
 */

namespace Azimut\Bundle\SecurityBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;

class AclType extends AbstractType
{
    private $registry;

    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('objectClass')
            ->add('objectId')
            ->add('field', TextType::class, array(
                'mapped' => false,
            ))
            ->add('no_edit', CheckboxType::class, array(
                'mapped' => false,
            ))
            ->add('no_view', CheckboxType::class, array(
                'mapped' => false,
            ))
        ;
    }
}
