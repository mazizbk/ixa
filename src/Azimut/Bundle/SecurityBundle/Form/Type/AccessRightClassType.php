<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2013-12-20 10:09:21
 */

namespace Azimut\Bundle\SecurityBundle\Form\Type;

use Azimut\Bundle\SecurityBundle\AccessRoles\RoleProviders;
use Azimut\Bundle\SecurityBundle\Entity\AccessRight;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Azimut\Bundle\SecurityBundle\Entity\AccessRole;

class AccessRightClassType extends AbstractType
{
    private $roleProvider;
    private $registry;

    public function __construct(RoleProviders $roleProvider, RegistryInterface $registry)
    {
        $this->roleProvider = $roleProvider;
        $this->registry = $registry;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entityChoices =  $this->roleProvider->getEntitiesFromProviders();
        $roleChoices = $this->roleProvider->getEntityRoles();
        $roles = [];
        foreach ($roleChoices as $role) {
            $em = $this->registry->getManager();
            $roleInDB = $em->getRepository(AccessRole::class)->findOneBy(array('role'=>$role));
            if (null === $roleInDB) {
                $roleInDB = new AccessRole();
                $roleInDB->setRole($role);
                $this->registry->getEntityManager()->persist($roleInDB);
            }
            $roles[] = $roleInDB;
        }

        $builder
        ->add('class', ChoiceType::class, array(
            'choices' => $entityChoices,
            'placeholder' => 'Choose a class'
        ))
        ->add('roles', EntityType::class, array(
            'class' => AccessRole::class,
            'choices' => $roles,
            'choice_label' => 'role',
            'placeholder' => 'Choose an option',
            'multiple' => true,
            'expanded' => true
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => AccessRight::class
        ));
    }
}
