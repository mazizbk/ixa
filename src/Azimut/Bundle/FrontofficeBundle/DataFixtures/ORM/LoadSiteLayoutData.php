<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-03-05 14:39:42
 */

namespace Azimut\Bundle\FrontofficeBundle\DataFixtures\ORM;

use Azimut\Bundle\FrontofficeBundle\Entity\SiteLayout;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadSiteLayoutData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $layout = new SiteLayout();
        $layout
            ->setName('default example')
            ->setTemplate('demo/base.html.twig')
            ->setExceptionTemplatesDir('demo')
            ->hasUserLogin(true)
            ->isNewUserActive(true)
            ->hasShop(true)
            ->setShopLoginTemplate('demo/shop_login.html.twig')
            ->setShopRegisterTemplate('demo/shop_register.html.twig')
            ->setShopOrderAddressesTemplate('demo/shop_order_addresses.html.twig')
            ->createMenuDefinition('main', 'Main menu')
            ->getLayout()
            ->createMenuDefinition('menu_2', 'Menu 2')
            ->getLayout()
        ;
        $manager->persist($layout);

        $layout = new SiteLayout();
        $layout
            ->setName('azimut')
            ->setTemplate('azimut/site.html.twig')
            ->setExceptionTemplatesDir('azimut')
            ->setSearchResultTemplate('azimut/search_result.html.twig')
            ->hasUserLogin(true)
            ->isNewUserActive(false)
            ->setLoginTemplate('azimut/login.html.twig')
            ->setLostPasswordTemplate('azimut/lost_password.html.twig')
            ->setPasswordResetTemplate('azimut/password_reset.html.twig')
            ->setPasswordResetTemplate('azimut/change_password.html.twig')
            ->setRegisterTemplate('azimut/register.html.twig')
            ->setEditProfileTemplate('azimut/edit_profile.html.twig')
            ->setPostLoginTemplate('azimut/post_login.html.twig')
            ->setConfirmEmailTemplate('azimut/confirm_email.html.twig')
            ->createMenuDefinition('main', 'Menu principal')
            ->getLayout()
            ->createMenuDefinition('footer', 'Menu pied de page')
            ->getLayout()
        ;
        $manager->persist($layout);

        $layout = new SiteLayout();
        $layout
            ->setName('azimut system')
            ->setTemplate('azimut_system/site.html.twig')
            ->createMenuDefinition('main', 'Main menu')
            ->getLayout()
        ;
        $manager->persist($layout);

        $manager->flush();
    }

    public function getOrder()
    {
        return 1;
    }
}
