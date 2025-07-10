<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2013-12-05 11:11:03
 */

namespace Azimut\Bundle\CmsBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Azimut\Bundle\CmsBundle\Entity\CmsFileArticle;
use Azimut\Bundle\CmsBundle\Entity\CmsFileMediaDeclinationAttachment;

class LoadCmsFileArticleData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $article1 = new CmsFileArticle();
        $article1
            ->setTitle('my article 1', 'en')
            ->setTitle('mon article 1', 'fr')
            ->setAuthor('Robert Ipsum')
            ->setPublishStartDateTime('2013-12-05 14:21:00')
            ->setText('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.', 'en')
            ->setText('Donec eleifend nulla ut sollicitudin congue. Vivamus id magna accumsan, eleifend tortor in, fermentum neque. Aenean fermentum, leo quis dignissim varius, libero odio ultricies elit, et fermentum sem erat eget elit. Praesent consequat ante eu rutrum tincidunt. Aenean et sollicitudin massa, sit amet porta nulla. Aliquam lacinia, neque nec gravida cursus, nisi odio tristique nibh, in feugiat ante enim non lectus. Praesent sit amet mollis orci.', 'fr')

            ->setMainAttachment(
                new CmsFileMediaDeclinationAttachment($this->getReference('image_declination-1'))
            )
            ->addSecondaryAttachment(
                new CmsFileMediaDeclinationAttachment($this->getReference('image_declination-2'))
            )
            ->addSecondaryAttachment(
                new CmsFileMediaDeclinationAttachment($this->getReference('image_declination-3'))
            )
        ;
        $manager->persist($article1);

        $article2 = new CmsFileArticle();
        $article2
            ->setTitle('my article 2', 'en')
            ->setTitle('mon article 2', 'fr')
            ->setAuthor('Jean Lorem')
            ->setText('Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur?', 'en')
            ->setText('Nunc ut fermentum lectus. Nam mauris dui, bibendum in diam quis, posuere dapibus nibh. Integer tempor convallis ullamcorper. Morbi sed porta turpis, a pharetra tellus. Quisque faucibus ac sapien eu volutpat. Donec sed ipsum non tellus tempus volutpat. Pellentesque nec metus imperdiet, accumsan nisi ut, tristique enim. Sed fringilla semper velit ut mattis.', 'fr')

            ->setMainAttachment(
                new CmsFileMediaDeclinationAttachment($this->getReference('video_declination-2'))
            )
        ;
        $manager->persist($article2);

        $article3 = new CmsFileArticle();
        $article3
            ->setTitle('My article 3', 'en')
            ->setTitle('My article 3', 'fr')
            ->setAuthor('Jean Lorem')
            ->setText('Mauris volutpat iaculis erat eget ullamcorper. Nulla a placerat augue. Proin lacus dolor, aliquam non molestie ac, euismod in dui. Sed in est libero. Cras id scelerisque eros! Donec congue eros urna, sit amet lacinia leo adipiscing non. Sed viverra lacinia aliquet? Donec euismod nibh purus, in molestie est placerat ac.', 'en')
            ->setText('Vestibulum egestas urna sem, et aliquet urna viverra non. Aenean ac lectus erat. Duis molestie aliquet ipsum ac consequat. Suspendisse ac ullamcorper urna, vitae fermentum purus. Fusce vulputate mauris sit amet felis semper tincidunt. Quisque commodo euismod nulla, quis lobortis urna tincidunt sed. Sed mattis est sit amet tellus tincidunt, et consequat turpis cursus. Nullam accumsan sapien et arcu sodales tincidunt. Nulla dignissim placerat ornare. Nulla id sem faucibus, cursus lacus ac, lobortis metus. Proin tincidunt cursus augue. Integer dolor eros, scelerisque et enim non, suscipit pretium erat. Nam consequat augue nec massa tempus ornare. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Nulla cursus in nulla in elementum. Sed ipsum magna, faucibus sit amet suscipit sed, ultrices eu metus.', 'fr')

            ->setMainAttachment(
                new CmsFileMediaDeclinationAttachment($this->getReference('video_declination-3'))
            )
        ;
        $manager->persist($article3);

        $article4 = new CmsFileArticle();
        $article4
            ->setTitle('My article 4', 'en')
            ->setTitle('My article 4', 'fr')
            ->setAuthor('Rose Lacy')
            ->setText('Donec ullamcorper turpis sit amet ipsum egestas tristique. Cras ultricies nisi vel neque sollicitudin lobortis. Duis ullamcorper sed diam at iaculis. Pellentesque mauris felis, dignissim id velit quis, pharetra tempus felis. Integer dictum arcu a aliquet fringilla. Curabitur fringilla urna eu sapien ullamcorper molestie? Sed at ante tempus, aliquam massa quis, fringilla arcu. Duis ut sapien sed erat sodales ullamcorper. Sed a consectetur eros?', 'en')
            ->setText('Proin ornare, velit id ullamcorper posuere, massa felis aliquet neque, sed consectetur arcu mi vitae tortor. Pellentesque pretium venenatis lacus ut tristique. Maecenas feugiat scelerisque lorem in rutrum. Maecenas consequat tincidunt ligula eu convallis. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Aliquam id scelerisque nibh. Cras non urna justo. Fusce a elit fringilla, sodales quam et, scelerisque lectus. Sed semper nisi at adipiscing tempor. Pellentesque vitae lobortis orci. Fusce sit amet nisi sed justo placerat convallis sit amet sed dui. Vivamus elit urna, congue ut bibendum vitae, pellentesque sed sapien. Etiam aliquam eros a condimentum fringilla. Proin porttitor egestas lorem, vel vestibulum velit lacinia vitae. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.', 'fr')
        ;
        $manager->persist($article4);

        $articleEnOnly = new CmsFileArticle();
        $articleEnOnly
            ->setTitle('My english only article', 'en')
            ->setText('Donec ullamcorper turpis sit amet ipsum egestas tristique. Cras ultricies nisi vel neque sollicitudin lobortis. Duis ullamcorper sed diam at iaculis. Pellentesque mauris felis, dignissim id velit quis, pharetra tempus felis. Integer dictum arcu a aliquet fringilla. Curabitur fringilla urna eu sapien ullamcorper molestie? Sed at ante tempus, aliquam massa quis, fringilla arcu. Duis ut sapien sed erat sodales ullamcorper. Sed a consectetur eros?', 'en')
        ;
        $manager->persist($articleEnOnly);

        $articleFrOnly = new CmsFileArticle();
        $articleFrOnly
            ->setTitle('Mon article français uniquement', 'fr')
            ->setText('Donec ullamcorper turpis sit amet ipsum egestas tristique. Cras ultricies nisi vel neque sollicitudin lobortis. Duis ullamcorper sed diam at iaculis. Pellentesque mauris felis, dignissim id velit quis, pharetra tempus felis. Integer dictum arcu a aliquet fringilla. Curabitur fringilla urna eu sapien ullamcorper molestie? Sed at ante tempus, aliquam massa quis, fringilla arcu. Duis ut sapien sed erat sodales ullamcorper. Sed a consectetur eros?', 'fr')
        ;
        $manager->persist($articleFrOnly);

        $article1
            ->addRelatedArticle($article2)
            ->addRelatedArticle($article3)
            ->addRelatedArticle($articleEnOnly)
            ->addRelatedArticle($articleFrOnly)
        ;

        $manager->flush();

        $this->addReference('cms-article1', $article1);
        $this->addReference('cms-article2', $article2);
        $this->addReference('cms-article3', $article3);
        $this->addReference('cms-article4', $article4);
        $this->addReference('cms-article-en-only', $articleEnOnly);
        $this->addReference('cms-article-fr-only', $articleFrOnly);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 4; // order in witch files are loaded
    }
}
