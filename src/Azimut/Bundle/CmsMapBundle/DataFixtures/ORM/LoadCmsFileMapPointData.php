<?php

namespace Azimut\Bundle\CmsMapBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Azimut\Bundle\CmsMapBundle\Entity\CmsFileMapPoint;
use Azimut\Bundle\CmsMapBundle\Entity\CmsFileMediaDeclinationAttachment;
use Azimut\Bundle\FormExtraBundle\Model\Geolocation;
use Azimut\Bundle\FormExtraBundle\Model\MapPointPosition;

class LoadCmsFileMapPointData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $cmsFileMapPoint1 = new CmsFileMapPoint();
        $cmsFileMapPoint1
            ->setTitle('my map point 1', 'en')
            ->setTitle('mon point carte 1', 'fr')
            ->setGeolocation(new Geolocation('47.753205897019605', '-3.3716550531249823'))
            ->setText('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.', 'en')
            ->setText('Donec eleifend nulla ut sollicitudin congue. Vivamus id magna accumsan, eleifend tortor in, fermentum neque. Aenean fermentum, leo quis dignissim varius, libero odio ultricies elit, et fermentum sem erat eget elit. Praesent consequat ante eu rutrum tincidunt. Aenean et sollicitudin massa, sit amet porta nulla. Aliquam lacinia, neque nec gravida cursus, nisi odio tristique nibh, in feugiat ante enim non lectus. Praesent sit amet mollis orci.', 'fr')
        ;
        $manager->persist($cmsFileMapPoint1);

        $cmsFileMapPoint2 = new CmsFileMapPoint();
        $cmsFileMapPoint2
            ->setTitle('my map point 2', 'en')
            ->setTitle('mon point carte 2', 'fr')
            ->setGeolocation(new Geolocation('48.39185678753918', '-4.481274193749982'))
            ->setText('Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur?', 'en')
            ->setText('Nunc ut fermentum lectus. Nam mauris dui, bibendum in diam quis, posuere dapibus nibh. Integer tempor convallis ullamcorper. Morbi sed porta turpis, a pharetra tellus. Quisque faucibus ac sapien eu volutpat. Donec sed ipsum non tellus tempus volutpat. Pellentesque nec metus imperdiet, accumsan nisi ut, tristique enim. Sed fringilla semper velit ut mattis.', 'fr')
        ;
        $manager->persist($cmsFileMapPoint2);

        $cmsFileMapPoint3 = new CmsFileMapPoint();
        $cmsFileMapPoint3
            ->setTitle('my map point 3', 'en')
            ->setTitle('mon point carte 3', 'fr')
            ->setPosition(new MapPointPosition(1300, 350))
            ->setText('Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur?', 'en')
            ->setText('Morbi sed porta turpis, a pharetra tellus. Quisque faucibus ac sapien eu volutpat. Donec sed ipsum non tellus tempus volutpat. Pellentesque nec metus imperdiet, accumsan nisi ut, tristique enim. Sed fringilla semper velit ut mattis.', 'fr')
        ;
        $manager->persist($cmsFileMapPoint3);

        $manager->flush();

        $this->addReference('cms-map-point1', $cmsFileMapPoint1);
        $this->addReference('cms-map-point2', $cmsFileMapPoint2);
        $this->addReference('cms-map-point3', $cmsFileMapPoint3);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 4; // order in witch files are loaded
    }
}
