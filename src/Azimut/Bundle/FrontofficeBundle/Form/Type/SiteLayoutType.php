<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-01-26 15:35:00
 */

namespace Azimut\Bundle\FrontofficeBundle\Form\Type;

use Azimut\Bundle\FrontofficeBundle\Entity\SiteLayout;
use Azimut\Bundle\FrontofficeBundle\Form\Type\MenuDefinitionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class SiteLayoutType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, [
                'label' => 'name',
            ])
            ->add('template', null, [
                'label' => 'template',
            ])
            ->add('exceptionTemplatesDir', null, [
                'label' => 'exception templates directory',
            ])
            ->add('searchResultTemplate', null, [
                'label' => 'search result template',
            ])
            ->add('hasUserLogin', null, [
                'label' => 'has user login',
            ])
            ->add('isNewUserActive', null, [
                'label' => 'is new user active',
            ])
            ->add('newUserRole', null, [
                'label' => 'new user role',
            ])
            ->add('loginTemplate', null, [
                'label' => 'login template',
            ])
            ->add('lostPasswordTemplate', null, [
                'label' => 'lost password template',
            ])
            ->add('passwordResetTemplate', null, [
                'label' => 'password reset template',
            ])
            ->add('passwordChangeTemplate', null, [
                'label' => 'password change template',
            ])
            ->add('registerTemplate', null, [
                'label' => 'register template',
            ])
            ->add('editProfileTemplate', null, [
                'label' => 'edit profile template',
            ])
            ->add('postLoginTemplate', null, [
                'label' => 'post login template (home of logged in users)',
            ])
            ->add('confirmEmailTemplate', null, [
                'label' => 'confirm email template',
            ])
            ->add('uniquePasswordTemplate', null, [
                'label' => 'unique password template',
            ])
            ->add('hasShop', null, [
                'label' => 'has shop',
            ])
            ->add('basketTemplate', null, [
                'label' => 'basket template (default: SiteLayout/basket.html.twig)',
            ])
            ->add('shopLoginTemplate', null, [
                'label' => 'shop login template (default: SiteLayout/shop_login.html.twig)',
            ])
            ->add('shopRegisterTemplate', null, [
                'label' => 'shop register template',
            ])
            ->add('shopOrderAddressesTemplate', null, [
                'label' => 'shop order addresses template (default: SiteLayout/shop_order_addresses.html.twig)',
            ])
            ->add('shopDeliveryTemplate', null, [
                'label' => 'shop delivery template (default: SiteLayout/shop_delivery.html.twig)',
            ])
            ->add('shopSummaryTemplate', null, [
                'label' => 'shop summary template (default: SiteLayout/shop_summary.html.twig)',
            ])
            ->add('shopPaymentTemplate', null, [
                'label' => 'shop payment template (default: SiteLayout/shop_payment.html.twig)',
                // 'hint' => 'Be carefull that this template is necesseray even if payment form is included in order summary (it makes a post request on it)',
            ])
            ->add('shopPaymentEmbedTemplate', null, [
                'label' => 'shop payment template when included in order summary (default: SiteLayout/shop_payment_form.html.twig)',
            ])
            ->add('shopUserOrdersTemplate', null, [
                'label' => 'shop user orders in user account menu (default: SiteLayout/user_orders.html.twig))',
            ])
            ->add('shopUserOrderShowTemplate', null, [
                'label' => 'shop user order detail in user account menu (default: SiteLayout/user_order_show.html.twig))',
            ])
            ->add('menuDefinitions', CollectionType::class, [
                'entry_type' => MenuDefinitionType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'entry_options' => [
                    'label' => false,
                    'allow_extra_fields' => true
                ],
                'required' => false,
                'label' => 'menu definitions',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SiteLayout::class
        ]);
    }
}
