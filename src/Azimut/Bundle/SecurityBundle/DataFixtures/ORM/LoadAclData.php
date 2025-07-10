<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2013-11-12 17:51:34
 */

namespace Azimut\Bundle\SecurityBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Azimut\Bundle\SecurityBundle\Entity\AccessRightAcl;
use Azimut\Bundle\SecurityBundle\Entity\Acl;
use Azimut\Bundle\SecurityBundle\Services\UserManager;
use Azimut\Bundle\SecurityBundle\Acl\AclService;

class LoadAclData extends AbstractFixture implements OrderedFixtureInterface
{
    private $userManager;

    private $aclService;

    public function __construct(UserManager $userManager, AclService $aclService)
    {
        $this->userManager = $userManager;
        $this->aclService = $aclService;
    }

    public function load(ObjectManager $manager)
    {
        /*
            $user2 = $this->userManager->findUserByUsername('jake.wilson@user.net');

            $site = $this->getReference('site1');
            $menu1 = $this->getReference('menu1');
            $menu2 = $this->getReference('menu2');
            $page1 = $this->getReference('page1');

            $arAcl = new AccessRightAcl();
            $manager->persist($arAcl);
            $arAcl->setUser($user2);

            $arAcl2 = new AccessRightAcl();
            $manager->persist($arAcl2);
            $arAcl2->setUser($user2);

            $acl = new Acl('Azimut\Bundle\FrontofficeBundle\Entity\Site', $site->getId());
            $acl->setNotEditable('title', true);
            $arAcl->addAcl($acl);

            $acl2 = new Acl('Azimut\Bundle\FrontofficeBundle\Entity\Menu', $menu1->getId());
            $acl2->setNotEditable('title', true);
            $acl2->setNotEditable('type', true);
            $arAcl2->addAcl($acl2);

            $acl3 = new Acl('Azimut\Bundle\FrontofficeBundle\Entity\Menu', $menu2->getId());
            $acl3->setNotEditable('title', true);
            $acl3->setNotViewable('type', true);
            $arAcl2->addAcl($acl3);

            $manager->persist($acl);
            $manager->persist($acl2);
            $manager->persist($acl3);

            // $aclService
           // $user->addAccessRight($arAcl);

            $acls = $arAcl->getAcls();

        /*
            foreach ($acls as $a) {
               echo 'is editable title, type';
               echo 'is viewable title ';
            }

            $manager->flush(); */

            return;
    }

        /**
        * {@inheritDoc}
        */
        public function getOrder()
        {
            return 12; // l'ordre dans lequel les fichiers sont chargés
        }
}
