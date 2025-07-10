<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2014-06-02 14:09:51
 */

namespace Azimut\Bundle\SecurityBundle\Form\Type;

use Azimut\Bundle\SecurityBundle\Entity\AccessRight;
use Azimut\Bundle\SecurityBundle\Entity\AccessRole;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Azimut\Bundle\SecurityBundle\AccessRights\AccessRightService;

class AccessRightRolesType extends AbstractType
{
    /**
     * @var AccessRightService
     */
    private $accessRightService;

    public function __construct(AccessRightService $accessRightService)
    {
        $this->accessRightService = $accessRightService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('roles', EntityType::class, array(
            'class' => AccessRole::class,
            'choices' => $this->accessRightService->getGlobalAccessRoles(),
            'choice_label' => 'role',
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
