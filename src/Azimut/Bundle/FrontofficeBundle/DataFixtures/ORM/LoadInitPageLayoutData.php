<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-01-31 16:43:52
 */

namespace Azimut\Bundle\FrontofficeBundle\DataFixtures\ORM;

use Azimut\Bundle\FrontofficeBundle\Entity\PageLayout;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Azimut\Bundle\FrontofficeBundle\Entity\CmsFileRichText;
use Azimut\Bundle\FrontofficeBundle\Entity\CmsFileTitle;
use Azimut\Bundle\FrontofficeBundle\Entity\CmsFileText;
use Azimut\Bundle\FrontofficeBundle\Entity\CmsFileImage;
use Azimut\Bundle\FrontofficeBundle\Entity\CmsFileVideo;
use Azimut\Bundle\FrontofficeBundle\Entity\CmsFileImageGallery;
use Azimut\Bundle\CmsBundle\Entity\CmsFileArticle;
use Azimut\Bundle\CmsBundle\Entity\CmsFileProduct;
use Azimut\Bundle\CmsContactBundle\Entity\CmsFileContact;
use Azimut\Bundle\CmsMapBundle\Entity\CmsFileMapPoint;
use Azimut\Bundle\CmsBundle\Entity\CmsFileEvent;
use Azimut\Bundle\FrontofficeBundle\Entity\ZoneFilter;
use Azimut\Bundle\FrontofficeBundle\Entity\ZonePermanentFilter;
use Azimut\Bundle\FrontofficeBundle\Entity\ZonePermanentStringFilter;
use Azimut\Bundle\FrontofficeBundle\Entity\ZonePermanentDateFilter;
use Azimut\Bundle\ModerationBundle\Entity\CmsFileArticleBuffer;

class LoadInitPageLayoutData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        // CAUTION : this layout is used in ApiSiteController to auto create site's home page
        $layout = new PageLayout();
        $layout
            ->setName('simple')
            ->setTemplate('default/simple.html.twig')
            ->createFullPageCmsFile()
        ;
        $manager->persist($layout);

        $layout = new PageLayout();
        $layout
            ->setName('mentions légales')
            ->setTemplate('default/mentions.html.twig')
            ->createFullZoneCmsFile('infos-editeur', CmsFileText::class)
            ->getLayout()
            ->createFullZoneCmsFile('responsable-publication', CmsFileText::class)
            ->getLayout()
            ->createFullZoneCmsFile('complements', CmsFileText::class)
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
