<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-03-17 16:48:35
 */

namespace Azimut\Bundle\CmsBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Azimut\Bundle\CmsBundle\Entity\CmsFileEvent;
use Azimut\Bundle\CmsBundle\Entity\CmsFileMediaDeclinationAttachment;

class LoadCmsFileEventData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $event1 = new CmsFileEvent();
        $event1
            ->setTitle('A passed event', 'en')
            ->setTitle('Un événement passé', 'fr')
            ->setEventStartDateTime('2017-03-17 16:00:00')
            ->setText('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.', 'en')
            ->setText('Donec eleifend nulla ut sollicitudin congue. Vivamus id magna accumsan, eleifend tortor in, fermentum neque. Aenean fermentum, leo quis dignissim varius, libero odio ultricies elit, et fermentum sem erat eget elit. Praesent consequat ante eu rutrum tincidunt. Aenean et sollicitudin massa, sit amet porta nulla. Aliquam lacinia, neque nec gravida cursus, nisi odio tristique nibh, in feugiat ante enim non lectus. Praesent sit amet mollis orci.', 'fr')
            ->setMainAttachment(
                new CmsFileMediaDeclinationAttachment($this->getReference('image_declination-3'))
            )
            ->addSecondaryAttachment(
                new CmsFileMediaDeclinationAttachment($this->getReference('image_declination-2'))
            )
            ->addSecondaryAttachment(
                new CmsFileMediaDeclinationAttachment($this->getReference('image_declination-1'))
            )
        ;
        $manager->persist($event1);

        $event2 = new CmsFileEvent();
        $event2
            ->setTitle('A futur event', 'en')
            ->setTitle('Un événement futur', 'fr')
            ->setEventStartDateTime('5017-03-17 12:00:00')
            ->setEventEndDateTime('5017-03-19 18:00:00')
            ->setText('Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur?', 'en')
            ->setText('Nunc ut fermentum lectus. Nam mauris dui, bibendum in diam quis, posuere dapibus nibh. Integer tempor convallis ullamcorper. Morbi sed porta turpis, a pharetra tellus. Quisque faucibus ac sapien eu volutpat. Donec sed ipsum non tellus tempus volutpat. Pellentesque nec metus imperdiet, accumsan nisi ut, tristique enim. Sed fringilla semper velit ut mattis.', 'fr')

            ->setMainAttachment(
                new CmsFileMediaDeclinationAttachment($this->getReference('video_declination-2'))
            )
        ;
        $manager->persist($event2);


        $manager->flush();

        $this->addReference('cms-event1', $event1);
        $this->addReference('cms-event2', $event2);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 4; // order in witch files are loaded
    }
}
