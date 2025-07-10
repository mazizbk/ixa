<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2014-05-27 09:39:51
 */

namespace Azimut\Bundle\SecurityBundle\Form\Type;

use Azimut\Bundle\FormExtraBundle\Form\Type\EntityHiddenType;
use Azimut\Bundle\SecurityBundle\Entity\AccessRight;
use Azimut\Bundle\SecurityBundle\Entity\Group;
use Azimut\Bundle\SecurityBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Azimut\Bundle\SecurityBundle\EventListener\AccessRightTypeEventSubscriber;
use Symfony\Bridge\Doctrine\RegistryInterface;

class AccessRightType extends AbstractType
{
    private $registry;
    private $roleProvider;
    private $accessRightService;

    public function __construct(RegistryInterface $registry, $roleProvider, $accessRightService)
    {
        $this->registry = $registry;
        $this->roleProvider = $roleProvider;
        $this->accessRightService = $accessRightService;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['user']) {
            $builder->add('user', EntityHiddenType::class, array(
                'class' => User::class,
                ));
        } elseif ($options['group']) {
            $builder->add('group', EntityHiddenType::class, array(
                'class' => Group::class,
                ));
        }
        if ($options['user'] && $options['group']) {
            throw new \RuntimeException('Access right form type needs either a user or either group to work, not both');
        }
        $builder
            ->add('type', HiddenType::class, array('mapped' => false)) //needed by the api to instanciate the access right object
        ;
        $builder->addEventSubscriber(new AccessRightTypeEventSubscriber($this->registry, $this->roleProvider, $this->accessRightService, $options));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => AccessRight::class,
            'user' => false, //todo change it coz it should be false so when form is called we put an option
            'group' => false,
            'access_right_type' => null
        ));
    }
}
