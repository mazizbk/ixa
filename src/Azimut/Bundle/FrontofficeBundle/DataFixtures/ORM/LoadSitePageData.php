<?php

namespace Azimut\Bundle\FrontofficeBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Azimut\Bundle\FrontofficeBundle\Entity\Site;
use Azimut\Bundle\FrontofficeBundle\Entity\PageContent;
use Azimut\Bundle\FrontofficeBundle\Entity\PagePlaceholder;
use Azimut\Bundle\FrontofficeBundle\Entity\PageLink;
use Azimut\Bundle\FrontofficeBundle\Entity\DomainName;
use Azimut\Bundle\FrontofficeBundle\Entity\Redirection;

class LoadSitePageData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * @var string
     */
    private $fixturesFrontofficeSitesGtld;

    /**
     * @param string $fixturesFrontofficeSitesGtld
     */
    public function __construct($fixturesFrontofficeSitesGtld)
    {
        $this->fixturesFrontofficeSitesGtld = $fixturesFrontofficeSitesGtld;
    }

    /**
    * {@inheritDoc}
    */
    public function load(ObjectManager $manager)
    {
        $layoutSiteRepository = $manager->getRepository('AzimutFrontofficeBundle:SiteLayout');
        $layoutPageRepository = $manager->getRepository('AzimutFrontofficeBundle:PageLayout');

        $siteLayoutDefault = $layoutSiteRepository->findOneByName('default example');
        $siteLayoutAzimutSystem = $layoutSiteRepository->findOneByName('azimut system');

        $pageOneCol = $layoutPageRepository->findOneByName('1 big column');
        $pageOneColWithContactForm = $layoutPageRepository->findOneByName('1 big column with contact form');
        $layoutPageThreeCol = $layoutPageRepository->findOneByName('3 columns with same width');
        $layoutPageMapLayout = $layoutPageRepository->findOneByName('map');
        $layoutPageCustomMapLayout = $layoutPageRepository->findOneByName('custom map');
        $demoFiltersPageLayout = $layoutPageRepository->findOneByName('demo filters');
        $layoutMentions = $layoutPageRepository->findOneByName('mentions légales');
        $layoutPageProduct = $layoutPageRepository->findOneByName('demo - products auto filled');
        $layoutPageHomeAzimutSystem = $layoutPageRepository->findOneByName('Azimut System home');
        $layoutPageDemoSubrouter = $layoutPageRepository->findOneByName('Demo subrouter');
        $layoutNewsletterArchives = $layoutPageRepository->findOneByName('newsletter archives');

        $site = new Site();
        $site
            ->setName('Site1')
            ->setPublisherName('My organization')
            ->setTitle('My site 1', 'en')
            ->setTitle('Mon site 1', 'fr')
            ->setLayout($siteLayoutDefault)
            ->setCommentsActive(true)
            ->setCommentRatingActive(true)
            ->setCommentModerationActive(false)
            ->setMainDomainName(new DomainName('system.vm'))
            ->addSecondaryDomainName(new DomainName('myalias.system.vm'))
            ->addSecondaryDomainName(new DomainName('myotheralias.system.vm'))
            ->setMainDomainName(new DomainName('system.'.$this->fixturesFrontofficeSitesGtld))
            ->addSecondaryDomainName(new DomainName('myalias.system.'.$this->fixturesFrontofficeSitesGtld))
            ->addSecondaryDomainName(new DomainName('myotheralias.system.'.$this->fixturesFrontofficeSitesGtld))
        ;
        $manager->persist($site);
        $this->setReference('site1', $site);

        $demoSiteMainMenu = $site->getMenu('main');
        $demoSiteMenu2 = $site->getMenu('menu_2');

        $this->setReference('menu1', $demoSiteMainMenu);
        $this->setReference('menu2', $demoSiteMenu2);

        $manager->flush();

        $page = new PageContent();
        $page
            ->setLayout($layoutPageThreeCol)
            ->setMenuTitle('Home', 'en')
            ->setMenuTitle('Accueil', 'fr')
            ->setAutoSlug(false)
            ->setSlug('', 'en')
            ->setSlug('', 'fr')
            ->setMenu($demoSiteMainMenu)
            ->getZone('center')
                ->setTitle('my center zone', 'en')
                ->setTitle('ma zone centrale', 'fr')
                ->getPage()
            ->addRedirection(new Redirection('my/page/redirection'))
        ;

        $manager->persist($page);
        $this->setReference('page1', $page);
        $manager->flush();

        $productPage = new PageContent();
        $productPage
            ->setLayout($layoutPageProduct)
            ->setMenuTitle('Products', 'en')
            ->setMenuTitle('Produits', 'fr')
            ->setMenu($demoSiteMainMenu)
        ;
        $manager->persist($productPage);
        $this->setReference('productPage', $productPage);
        $manager->flush();

        $page2 = new PageContent();
        $page2
            ->setLayout($pageOneCol)
            ->setMenuTitle('My page 2', 'en')
            ->setMenuTitle('Ma page 2', 'fr')
            ->setMenu($demoSiteMainMenu)
        ;
        $manager->persist($page2);
        $this->setReference('page2', $page2);
        $manager->flush();

        $page3 = new PageContent();
        $page3
            ->setLayout($layoutPageThreeCol)
            ->setMenuTitle('My page 3', 'en')
            ->setMenuTitle('Ma page 3', 'fr')
            ->setMenu($demoSiteMainMenu)
        ;
        $manager->persist($page3);
        $this->setReference('page3', $page2);
        $manager->flush();

        $page4 = new PageContent();
        $page4
            ->setLayout($layoutPageThreeCol)
            ->setMenuTitle('My page 4', 'en')
            ->setMenuTitle('Ma page 4', 'fr')
            ->setParentPage($page3)
        ;
        $manager->persist($page4);
        $this->setReference('page4', $page4);
        $manager->flush();

        $page5 = new PageContent();
        $page5
            ->setLayout($layoutPageThreeCol)
            ->setMenuTitle('My page 5', 'en')
            ->setMenuTitle('Ma page 5', 'fr')
            ->setMenu($demoSiteMenu2)
        ;
        $manager->persist($page5);
        $this->setReference('page5', $page5);
        $manager->flush();

        $page6 = new PageContent();
        $page6
            ->setLayout($layoutPageThreeCol)
            ->setMenuTitle('My page 6', 'en')
            ->setMenuTitle('Ma page 6', 'fr')
            ->setMenu($demoSiteMenu2)
        ;
        $manager->persist($page6);
        $this->setReference('page6', $page6);
        $manager->flush();

        $pageMentions = new PageContent();
        $pageMentions
            ->setLayout($layoutMentions)
            ->setMenuTitle('Legal Mentions', 'en')
            ->setMenuTitle('Mentions légales', 'fr')
            ->setMenu($demoSiteMenu2)
        ;
        $manager->persist($page6);
        $this->setReference('pageMentions', $pageMentions);
        $manager->flush();

        $pagePlaceholder = new PagePlaceholder();
        $pagePlaceholder
            ->setMenuTitle('Placeholder', 'en')
            ->setMenuTitle('Emplacement', 'fr')
            ->setMenu($demoSiteMainMenu)
        ;
        $manager->persist($pagePlaceholder);
        $manager->flush();

            $page = new PageLink();
            $page
                ->setTargetPage($pageMentions)
                ->setMenuTitle('Demo link', 'fr')
                ->setMenuTitle('Lien démo', 'fr')
                ->setParentPage($pagePlaceholder)
            ;
            $manager->persist($page);

        $page7 = new PageContent();
        $page7
            ->setLayout($pageOneColWithContactForm)
            ->setMenuTitle('Contact', 'en')
            ->setMenuTitle('Contact', 'fr')
            ->setMenu($demoSiteMainMenu)
        ;
        $manager->persist($page7);
        $this->setReference('page7', $page7);
        $manager->flush();

        $pageMap = new PageContent();
        $pageMap
            ->setLayout($layoutPageMapLayout)
            ->setMenuTitle('Map', 'en')
            ->setMenuTitle('Carte', 'fr')
            ->setMenu($demoSiteMainMenu)
        ;
        $manager->persist($pageMap);
        $this->setReference('pageMap', $pageMap);
        $manager->flush();

        $pageCustomMap = new PageContent();
        $pageCustomMap
            ->setLayout($layoutPageCustomMapLayout)
            ->setMenuTitle('Custom map', 'en')
            ->setMenuTitle('Carte personnalisée', 'fr')
            ->setMenu($demoSiteMainMenu)
        ;
        $manager->persist($pageCustomMap);
        $this->setReference('pageCustomMap', $pageCustomMap);
        $manager->flush();

        $pageDemoSubrouter = new PageContent();
        $pageDemoSubrouter
            ->setLayout($layoutPageDemoSubrouter)
            ->setMenuTitle('Demo subrouter', 'en')
            ->setMenuTitle('Demo subrouter', 'fr')
            ->setMenu($demoSiteMainMenu)
        ;
        $manager->persist($pageDemoSubrouter);
        $this->setReference('pageDemoSubrouter', $pageDemoSubrouter);
        $manager->flush();

        $pageDemoFilters = new PageContent();
        $pageDemoFilters
            ->setLayout($demoFiltersPageLayout)
            ->setMenuTitle('Demo filters', 'en')
            ->setMenuTitle('Demo filters', 'fr')
            ->setMenu($demoSiteMainMenu)
        ;
        $manager->persist($pageDemoFilters);

        $pageNewsletterArchives = new PageContent();
        $pageNewsletterArchives
            ->setLayout($layoutNewsletterArchives)
            ->setMenuTitle('Newsletter', 'en')
            ->setMenuTitle('Newsletter', 'fr')
            ->setMenu($demoSiteMenu2)
        ;
        $manager->persist($pageNewsletterArchives);

        $manager->flush();


        // azimut demo website

        $siteLayoutAzimut= $layoutSiteRepository->findOneByName('azimut');

        $azimutPageLayoutHome = $layoutPageRepository->findOneByName('azimut - home');
        $azimutPageLayoutProducts = $layoutPageRepository->findOneByName('azimut - products');
        $azimutPageLayoutSimple = $layoutPageRepository->findOneByName('azimut - simple');
        $azimutPageLayoutNews = $layoutPageRepository->findOneByName('azimut - news');
        $azimutPageLayoutNewsAutoFilled = $layoutPageRepository->findOneByName('azimut - news auto filled');
        $azimutPageLayoutDemoForm = $layoutPageRepository->findOneByName('azimut - demo form');
        $azimutPageLayoutSubmitArticle = $layoutPageRepository->findOneByName('azimut - submit article');

        $azimutSite = new Site();
        $azimutSite
            ->setName('Azimut')
            ->setPublisherName('Azimut')
            ->setTitle('Azimut', 'en')
            ->setTitle('Azimut', 'fr')
            ->setLayout($siteLayoutAzimut)
            ->setMainDomainName(new DomainName('azimut.system.vm'))
            ->setCommentsActive(true)
            ->setCommentRatingActive(true)
            ->setMainDomainName(new DomainName('azimut.system.'.$this->fixturesFrontofficeSitesGtld))
        ;
        $manager->persist($azimutSite);
        $this->setReference('azimutSite', $azimutSite);


        $azimutSiteMainMenu = $azimutSite->getMenu('main');
        $azimutSiteFooterMenu = $azimutSite->getMenu('footer');

        $manager->flush();

        $azimutHomePage = new PageContent();
        $azimutHomePage
            ->setLayout($azimutPageLayoutHome)
            ->setMenuTitle('Home', 'en')
            ->setMenuTitle('Accueil', 'fr')
            ->setAutoSlug(false)
            ->setSlug('', 'en')
            ->setSlug('', 'fr')
            ->setMenu($azimutSiteMainMenu)
        ;
        $manager->persist($azimutHomePage);
        $this->setReference('azimutHomePage', $azimutHomePage);
        $manager->flush();

        $azimutKiosksPage = new PageContent();
        $azimutKiosksPage
            ->setLayout($azimutPageLayoutProducts)
            ->setMenuTitle('Kiosks', 'en')
            ->setMenuTitle('Bornes', 'fr')
            ->setMenu($azimutSiteMainMenu)
        ;
        $manager->persist($azimutKiosksPage);
        $this->setReference('azimutKiosksPage', $azimutKiosksPage);
        $manager->flush();

        $azimutDemoCustomFormPage = new PageContent();
        $azimutDemoCustomFormPage
            ->setLayout($azimutPageLayoutDemoForm)
            ->setMenuTitle('Demo form', 'en')
            ->setMenuTitle('Formulaire démo', 'fr')
            ->setMenu($azimutSiteMainMenu)
        ;
        $manager->persist($azimutDemoCustomFormPage);
        $this->setReference('azimutDemoCustomFormPage', $azimutDemoCustomFormPage);
        $manager->flush();

        $azimutSubmitArticlePage = new PageContent();
        $azimutSubmitArticlePage
            ->setLayout($azimutPageLayoutSubmitArticle)
            ->setMenuTitle('Submit article', 'en')
            ->setMenuTitle('Soumettre un article', 'fr')
            ->setMenu($azimutSiteMainMenu)
        ;
        $manager->persist($azimutSubmitArticlePage);
        $this->setReference('azimutSubmitArticlePage', $azimutSubmitArticlePage);
        $manager->flush();

        $azimutDisplaysPage = new PageContent();
        $azimutDisplaysPage
            ->setLayout($layoutPageThreeCol)
            ->setMenuTitle('Signage', 'en')
            ->setMenuTitle('Affichage', 'fr')
            ->setMenu($azimutSiteMainMenu)
        ;
        $manager->persist($azimutDisplaysPage);
        $this->setReference('azimutDisplaysPage', $azimutDisplaysPage);
        $manager->flush();

        $azimutInternetPage = new PageContent();
        $azimutInternetPage
            ->setLayout($layoutPageThreeCol)
            ->setMenuTitle('Internet', 'en')
            ->setMenuTitle('Internet', 'fr')
            ->setMenu($azimutSiteMainMenu)
        ;
        $manager->persist($azimutInternetPage);
        $this->setReference('azimutInternetPage', $azimutInternetPage);
        $manager->flush();

        $azimutSoftwaresPage = new PageContent();
        $azimutSoftwaresPage
            ->setLayout($layoutPageThreeCol)
            ->setMenuTitle('Software', 'en')
            ->setMenuTitle('Logiciels', 'fr')
            ->setMenu($azimutSiteMainMenu)
        ;
        $manager->persist($azimutSoftwaresPage);
        $this->setReference('azimutSoftwaresPage', $azimutSoftwaresPage);
        $manager->flush();

        $azimutTrainingsPage = new PageContent();
        $azimutTrainingsPage
            ->setLayout($layoutPageThreeCol)
            ->setMenuTitle('Training', 'en')
            ->setMenuTitle('Formations', 'fr')
            ->setMenu($azimutSiteMainMenu)
        ;
        $manager->persist($azimutTrainingsPage);
        $this->setReference('azimutTrainingsPage', $azimutTrainingsPage);
        $manager->flush();

        $azimutPresentationPage = new PageContent();
        $azimutPresentationPage
            ->setLayout($azimutPageLayoutSimple)
            ->setMenuTitle('Presentation', 'en')
            ->setMenuTitle('Présentation', 'fr')
            ->setMenu($azimutSiteMainMenu)
        ;
        $manager->persist($azimutPresentationPage);
        $this->setReference('azimutPresentationPage', $azimutPresentationPage);
        $manager->flush();

        $azimutNewsPage = new PageContent();
        $azimutNewsPage
            ->setLayout($azimutPageLayoutNews)
            ->setMenuTitle('News', 'en')
            ->setMenuTitle('Actualités', 'fr')
            ->setMenu($azimutSiteFooterMenu)
        ;
        $manager->persist($azimutNewsPage);
        $this->setReference('azimutNewsPage', $azimutNewsPage);
        $manager->flush();

        $azimutNewsPageAutoFilled = new PageContent();
        $azimutNewsPageAutoFilled
            ->setLayout($azimutPageLayoutNewsAutoFilled)
            ->setMenuTitle('News auto', 'en')
            ->setMenuTitle('Actualités auto', 'fr')
            ->setMenu($azimutSiteFooterMenu)
        ;
        $manager->persist($azimutNewsPageAutoFilled);
        $this->setReference('azimutNewsPageAutoFilled', $azimutNewsPageAutoFilled);
        $manager->flush();

        $azimutPressReviewPage = new PageContent();
        $azimutPressReviewPage
            ->setLayout($layoutPageThreeCol)
            ->setMenuTitle('Press reviews', 'en')
            ->setMenuTitle('Revues de presse', 'fr')
            ->setMenu($azimutSiteFooterMenu)
        ;
        $manager->persist($azimutPressReviewPage);
        $this->setReference('azimutPressReviewPage', $azimutPressReviewPage);
        $manager->flush();

        $azimutReferencesPage = new PageContent();
        $azimutReferencesPage
            ->setLayout($layoutPageThreeCol)
            ->setMenuTitle('References', 'en')
            ->setMenuTitle('Références', 'fr')
            ->setMenu($azimutSiteFooterMenu)
        ;
        $manager->persist($azimutReferencesPage);
        $this->setReference('azimutReferencesPage', $azimutReferencesPage);
        $manager->flush();

        $azimutContactPage = new PageContent();
        $azimutContactPage
            ->setLayout($layoutPageThreeCol)
            ->setMenuTitle('Contact', 'en')
            ->setMenuTitle('Contact', 'fr')
            ->setMenu($azimutSiteFooterMenu)
        ;
        $manager->persist($azimutContactPage);
        $this->setReference('azimutContactPage', $azimutContactPage);
        $manager->flush();

        $azimutRecruitmentPage = new PageContent();
        $azimutRecruitmentPage
            ->setLayout($layoutPageThreeCol)
            ->setMenuTitle('Recruitment', 'en')
            ->setMenuTitle('Recrutement', 'fr')
            ->setMenu($azimutSiteFooterMenu)
        ;
        $manager->persist($azimutRecruitmentPage);
        $this->setReference('azimutRecruitmentPage', $azimutRecruitmentPage);
        $manager->flush();

        $azimutLegalNoticePage = new PageContent();
        $azimutLegalNoticePage
            ->setLayout($layoutPageThreeCol)
            ->setMenuTitle('Legal notice', 'en')
            ->setMenuTitle('Mentions légales', 'fr')
            ->setMenu($azimutSiteFooterMenu)
        ;
        $manager->persist($azimutLegalNoticePage);
        $this->setReference('azimutLegalNoticePage', $azimutLegalNoticePage);
        $manager->flush();

        // set target zone for submitted articles
        $azimutPageLayoutSubmitArticle->getZoneDefinition('form')
            ->setTargetZone($azimutNewsPage->getZone('news'))
        ;

        $manager->flush();


        // Azimut System waiting site

        $site = new Site();
        $site
            ->setName('Azimut System')
            ->setPublisherName('Azimut')
            ->setTitle('Azimut System', 'en')
            ->setTitle('Azimut System', 'fr')
            ->setLayout($siteLayoutAzimutSystem)
            ->setMainDomainName(new DomainName('www.azimut-system.net'))
            ->addSecondaryDomainName(new DomainName('azimut-system.net'))
            ->setMetaNoIndex(true)
        ;
        $manager->persist($site);
        $this->setReference('siteAzimutSystem', $site);

        $manager->flush();

        $AzimutSystemMainMenu = $site->getMenu('main');

        $page = new PageContent();
        $page
                ->setLayout($layoutPageHomeAzimutSystem)
                ->setMenuTitle('Home', 'en')
                ->setMenuTitle('Accueil', 'fr')
                ->setAutoSlug(false)
                ->setShowInMenu(false)
                ->setSlug('', 'en')
                ->setSlug('', 'fr')
                ->setMenu($AzimutSystemMainMenu)
            ;


        $manager->flush();
    }

    /**
    * {@inheritDoc}
    */
    public function getOrder()
    {
        return 6;
    }
}
