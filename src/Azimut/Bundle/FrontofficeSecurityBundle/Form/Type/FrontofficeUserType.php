<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-05-04 10:52:55
 */

namespace Azimut\Bundle\FrontofficeSecurityBundle\Form\Type;

use Azimut\Bundle\FrontofficeSecurityBundle\Entity\FrontofficeUser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class FrontofficeUserType extends AbstractType
{
    private $roles;

    public function __construct(array $roles)
    {
        $this->roles = $roles;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (true === $options['with_email']) {
            $builder->add('email', EmailType::class, [
                'label' => 'email',
            ]);
        }

        $builder
            ->add('firstName', TextType::class, [
                'label' => 'first.name',
            ])
            ->add('lastName', TextType::class, [
                'label' => 'last.name',
            ])
        ;

        if (true === $options['with_access_rights'] && count($this->roles) > 0) {
            $builder
                ->add('roles', ChoiceType::class, [
                    'label'        => 'user.roles',
                    'choices'      => $this->roles,
                    'expanded'     => true,
                    'multiple'     => true,
                    'choice_label' => function ($value) {
                        return $value;
                    },
                    'by_reference' => false,
                ])
                ->add('isActive', CheckboxType::class, [
                    'label'    => 'is.active',
                    'required' => false,
                ])
            ;
        }

        if (true === $options['with_password']) {
            $builder->add('plainPassword', PasswordType::class, [
                'label' => 'password',
            ]);
        }
        else if (true === $options['with_repeated_password']) {
            $builder->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options'  => ['label' => 'password'],
                'second_options' => ['label' => 'confirm.password'],
            ]);
        }

        if (true === $options['with_address']) {
            $builder->add('address', FrontofficeUserAddressType::class, [
                'label' => 'address',
            ]);
        }

        if (true === $options['with_delivery_address']) {
            $builder->add('deliveryAddress', FrontofficeUserAddressType::class, [
                'label' => 'delivery.address',
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'             => FrontofficeUser::class,
            'with_password'          => false,
            'with_repeated_password' => false,
            'with_access_rights'     => false,
            'with_email'             => true,
            'with_address'           => false,
            'with_delivery_address'  => false,
        ]);
    }
}
