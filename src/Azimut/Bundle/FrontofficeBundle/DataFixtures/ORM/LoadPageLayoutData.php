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

class LoadPageLayoutData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $layout = new PageLayout();
        $layout
            ->setName('1 big column')
            ->setTemplate('demo/1column.html.twig')
            ->setOptions([
            ])
            ->createZoneDefinition('center', [
                'accepted_attachment_classes' => [
                    CmsFileArticle::class,
                    CmsFileContact::class
                ],
                'standalone_cmsfiles_routes' => true,
            ])
            ->getLayout()
            ->setTemplateOptions([
                'myOption1' => 'My option value 1',
                'myOption2' => 'My option value 2',
            ])
        ;
        $manager->persist($layout);

        $layout = new PageLayout();
        $layout
            ->setName('1 big column with contact form')
            ->setTemplate('demo/1column_with_contact_form.html.twig')
            ->createFullZoneCmsFile('intro', CmsFileText::class)
            ->getLayout()
        ;
        $manager->persist($layout);

        $layout = new PageLayout();
        $layout
            ->setName('3 columns with same width')
            ->setTemplate('demo/3columns.html.twig')
            ->setOptions([
            ])
            ->createZoneDefinition('left', [
                'accepted_attachment_classes' => [
                    CmsFileArticle::class,
                    CmsFileContact::class
                ],
                'standalone_cmsfiles_routes' => true,
            ])
            ->getLayout()
            ->createZoneDefinition('center', [
                'accepted_attachment_classes' => [
                    CmsFileArticle::class,
                    CmsFileContact::class
                ]
            ])
            ->getLayout()
            ->createZoneDefinition('right', [
                'accepted_attachment_classes' => [
                    CmsFileArticle::class,
                    CmsFileContact::class
                ]
            ])
            ->getLayout()
        ;
        $manager->persist($layout);

        $layout = new PageLayout();
        $layout
            ->setName('demo filters')
            ->setTemplate('demo/demo_filters.html.twig')
            ->setOptions([
            ])
            ->createZoneDefinition('news_title_filter', [
                'accepted_attachment_classes' => [CmsFileArticle::class],
                'standalone_cmsfiles_routes' => true,
                'auto_fill_attachments' => true,
                'permanent_filters' => [
                    new ZonePermanentStringFilter(
                        'title',
                        ZonePermanentFilter::CONTAINS,
                        'article 1'
                    ),
                ],
            ])
            ->getLayout()
            ->createZoneDefinition('news_comments_rating_filter', [
                'accepted_attachment_classes' => [CmsFileArticle::class],
                'auto_fill_attachments' => true,
                'standalone_cmsfiles_routes' => true,
                'permanent_filters' => [
                    new ZonePermanentStringFilter(
                        'comments.rating',
                        ZonePermanentFilter::GREATER_THAN_OR_EQUALS,
                        3
                    ),
                ],
            ])
            ->getLayout()
            ->createZoneDefinition('products_associated_products_filter', [
                'accepted_attachment_classes' => [CmsFileProduct::class],
                'standalone_cmsfiles_routes' => true,
                'auto_fill_attachments' => true,
                'permanent_filters' => [
                    new ZonePermanentStringFilter(
                        'associatedProducts.title',
                        ZonePermanentFilter::CONTAINS,
                        'interactive'
                    ),
                ],
            ])
            ->getLayout()
        ;
        $manager->persist($layout);

        $layout = new PageLayout();
        $layout
            ->setName('demo - products auto filled')
            ->setTemplate('demo/products.html.twig')
            ->setOptions([
            ])
            ->createZoneDefinition('products', [
                'accepted_attachment_classes' => [ CmsFileProduct::class ],
                'standalone_cmsfiles_routes' => true,
                'cms_file_path_priority' => 20,
                'auto_fill_attachments' => true,
                'filters' => [
                    new ZoneFilter('title', ZoneFilter::CONTAINS),
                    new ZoneFilter('price', ZoneFilter::GREATER_THAN_OR_EQUALS, 'min_price'),
                    new ZoneFilter('price', ZoneFilter::LOWER_THAN_OR_EQUALS, 'max_price'),
                ],
            ])
            ->getLayout()
        ;
        $manager->persist($layout);

        $layout = new PageLayout();
        $layout
            ->setName('newsletter archives')
            ->setTemplate('default/newsletter_archives.html.twig')
            ->createFullZoneCmsFile('intro', CmsFileText::class)
            ->getLayout()
        ;
        $manager->persist($layout);

        $layout = new PageLayout();
        $layout
            ->setName('azimut - home')
            ->setTemplate('azimut/home.html.twig')
            ->setOptions([
            ])
            ->createFullZoneCmsFile('title', CmsFileTitle::class)
            ->getLayout()
            ->createFullZoneCmsFile('intro', CmsFileText::class)
            ->getLayout()
            ->createZoneDefinition('products', array(
                'accepted_attachment_classes' => array( CmsFileProduct::class ),
                'standalone_cmsfiles_routes' => true,
                'max_attachments_count' => 1,
                'use_canonical_cms_file_path' => true,
            ))
            ->getLayout()
            ->createZoneDefinition('news', array(
                'accepted_attachment_classes' => [
                    CmsFileArticle::class
                ],
                'standalone_cmsfiles_routes' => true,
                'max_attachments_count' => 5,
                'auto_fill_attachments' => true,
                'use_canonical_cms_file_path' => true,
                'exclude_untranslated_cms_files' => true,
            ))
            ->getLayout()
            ->createZoneDefinition('events', [
                'accepted_attachment_classes' => [
                    CmsFileEvent::class
                ],
                'standalone_cmsfiles_routes' => true,
                'max_attachments_count' => 3,
                'auto_fill_attachments' => true,
                'permanent_filters' => [
                    new ZonePermanentDateFilter(
                        'eventStartDatetime',
                        ZonePermanentFilter::GREATER_THAN_OR_EQUALS,
                        '-3 months'
                    ),
                ],
            ])
            ->getLayout()
        ;
        $manager->persist($layout);

        $layout = new PageLayout();
        $layout
            ->setName('azimut - products')
            ->setTemplate('azimut/products.html.twig')
            ->setOptions([
            ])
            ->createFullZoneCmsFile('intro', CmsFileRichText::class)
            ->getLayout()
            ->createZoneDefinition('products', [
                'accepted_attachment_classes' => [ CmsFileProduct::class ],
                'standalone_cmsfiles_routes' => true,
                'filters' => [
                    new ZoneFilter('title', ZoneFilter::CONTAINS),
                    new ZoneFilter('title', ZoneFilter::BEGIN_WITH_FIRST_LETTER, 'title_index', ''),
                    new ZoneFilter('price', ZoneFilter::GREATER_THAN_OR_EQUALS, 'min_price'),
                    new ZoneFilter('price', ZoneFilter::LOWER_THAN_OR_EQUALS, 'max_price')
                ],
                'cms_file_path_priority' => 10,
            ])
            ->getLayout()
        ;
        $manager->persist($layout);

        $layout = new PageLayout();
        $layout
            ->setName('azimut - simple')
            ->setTemplate('azimut/simple.html.twig')
            ->createFullPageCmsFile()
        ;
        $manager->persist($layout);

        $layout = new PageLayout();
        $layout
            ->setName('azimut - simple with image header')
            ->setTemplate('azimut/simple_with_media_header.html.twig')
            ->createFullZoneCmsFile('header', CmsFileImage::class)
            ->getLayout()
            ->createFullZoneCmsFile('content', CmsFileRichText::class);
        ;
        $manager->persist($layout);

        $layout = new PageLayout();
        $layout
            ->setName('azimut - simple with video header')
            ->setTemplate('azimut/simple_with_media_header.html.twig')
            ->createFullZoneCmsFile('header', CmsFileVideo::class)
            ->getLayout()
            ->createFullZoneCmsFile('content', CmsFileRichText::class);
        ;
        $manager->persist($layout);

        $layout = new PageLayout();
        $layout
            ->setName('azimut - simple with diaporama')
            ->setTemplate('azimut/simple_with_diaporama.html.twig')
            ->createFullZoneCmsFile('diaporama', CmsFileImageGallery::class)
            ->getLayout()
            ->createFullZoneCmsFile('content', CmsFileRichText::class);
        ;
        $manager->persist($layout);

        $layout = new PageLayout();
        $layout
            ->setName('azimut - news auto filled')
            ->setTemplate('azimut/news.html.twig')
            ->setOptions([
            ])
            ->createZoneDefinition('news', [
                'accepted_attachment_classes' => [ CmsFileArticle::class ],
                'standalone_cmsfiles_routes' => true,
                'cms_file_path_priority' => 20,
                'auto_fill_attachments' => true,
            ])
            ->getLayout()
        ;
        $manager->persist($layout);

        $layout = new PageLayout();
        $layout
            ->setName('azimut - news')
            ->setTemplate('azimut/news.html.twig')
            ->setOptions([
            ])
            ->createZoneDefinition('news', [
                'accepted_attachment_classes' => [ CmsFileArticle::class ],
                'standalone_cmsfiles_routes' => true,
                'cms_file_path_priority' => 10,
            ])
            ->getLayout()
        ;
        $manager->persist($layout);

        $layout = new PageLayout();
        $layout
            ->setName('azimut - demo form')
            ->setTemplate('azimut/demo_form.html.twig')
            ->createFullZoneCmsFile('intro', CmsFileRichText::class)
            ->getLayout()
            ->createZoneDefinitionForm('form', 'AzimutFrontofficeCustomBundle:DemoForm:form')
            ->getLayout()
        ;
        $manager->persist($layout);

        $layout = new PageLayout();
        $layout
            ->setName('azimut - submit article')
            ->setTemplate('azimut/content_submit.html.twig')
            ->createFullZoneCmsFile('intro', CmsFileRichText::class)
            ->getLayout()
            ->createZoneDefinitionCmsFileBufferForm('form', CmsFileArticleBuffer::class)
            ->getLayout()
        ;
        $manager->persist($layout);

        $layout = new PageLayout();
        $layout
            ->setName('map')
            ->setTemplate('map.html.twig')
            ->createZoneDefinition('main', array(
                'accepted_attachment_classes' => array( CmsFileMapPoint::class )
            ))
            ->getLayout()
        ;
        $manager->persist($layout);

        $layout = new PageLayout();
        $layout
            ->setName('custom map')
            ->setTemplate('demo/custom_map.html.twig')
            ->createZoneDefinition('map', array(
                'accepted_attachment_classes' => array( CmsFileMapPoint::class )
            ))
            ->getLayout()
        ;
        $manager->persist($layout);

        $layout = new PageLayout();
        $layout
            ->setName('Demo subrouter')
            ->setTemplate('demo/demo_subrouter.html.twig')
            ->setOptions([
                'standalone_router_standalone_cmsfiles_routes' => true,
            ])
            ->setStandaloneRouterController('AzimutFrontofficeCustomBundle:DemoSubrouter:index')
        ;
        $manager->persist($layout);


        $layout = new PageLayout();
        $layout
            ->setName('Azimut System home')
            ->setTemplate('azimut_system/home.html.twig')
        ;
        $manager->persist($layout);




        $manager->flush();
    }

    public function getOrder()
    {
        return 1;
    }
}
