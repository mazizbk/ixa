<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-02-05 14:06:52
 */

namespace Azimut\Bundle\FrontofficeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;
use Azimut\Bundle\DoctrineExtraBundle\Translation\TranslationProxy;
use Azimut\Bundle\DoctrineExtraBundle\Entity\TranslatableEntityInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Azimut\Bundle\DoctrineExtraBundle\Validator\Constraints as AzimutAssert;
use Azimut\Bundle\SecurityBundle\Security\ObjectAccessRightAware; //trait for access rights
use Azimut\Component\PHPExtra\StringHelper;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\Blameable\Traits\BlameableEntity;

/**
 * @ORM\Entity(repositoryClass="Azimut\Bundle\FrontofficeBundle\Entity\Repository\PageRepository")
 * @ORM\Table(name="frontoffice_page")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({"content" = "PageContent", "alias" = "PageAlias", "placeholder" = "PagePlaceholder", "link" = "PageLink"})
 * @AzimutAssert\LangFilled(requiredFields={"menuTitle"})
 */
abstract class Page implements TranslatableEntityInterface
{
    use ObjectAccessRightAware;
    use TimestampableEntity, BlameableEntity;

    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"list_pages", "detail_page", "list_sites", "detail_site", "detail_menu", "security_access_right_obj"})
     */
    private $id;

    /**
     * @var PageTranslation[]|ArrayCollection<PageTranslation>
     *
     * @ORM\OneToMany(targetEntity="PageTranslation", mappedBy="page", cascade={"persist", "remove"}, orphanRemoval=true, indexBy="locale")
     * @Assert\Valid()
     */
    protected $translations;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     * @Groups({"list_pages", "detail_page"})
     */
    protected $autoSlug;

    /**
     * @var  bool
     *
     * @ORM\Column(type="boolean")
     * @Groups({"list_pages", "detail_page"})
     */
    protected $autoMetas;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     * @Groups({"detail_page"})
     */
    protected $differentPageTitle;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     * @Groups({"list_sites", "detail_site", "detail_menu", "list_pages", "detail_page"})
     */
    protected $active;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     * @Groups({"detail_page"})
     */
    protected $metaNoIndex;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     * @Groups({"list_sites", "detail_site", "detail_menu", "list_pages", "detail_page"})
     */
    protected $showInMenu;

    /**
     * @var Menu
     *
     * @ORM\ManyToOne(targetEntity="Menu", inversedBy="pages", cascade={"persist"}))
     * @ORM\JoinColumn(name="menu_id", referencedColumnName="id", onDelete="cascade")
     */
    private $menu;

    /**
     * @var Page|null
     *
     * @ORM\ManyToOne(targetEntity="Page", inversedBy="childrenPages")
     * @ORM\JoinColumn(name="parent_page_id", referencedColumnName="id", onDelete="cascade")
     **/
    private $parentPage;

    /**
     * @var Page[]|ArrayCollection<Page>
     *
     * @ORM\OneToMany(targetEntity="Page", mappedBy="parentPage", cascade={"persist"}))
     * @Groups({"list_pages", "list_sites", "detail_site"})
     * @ORM\OrderBy({"displayOrder" = "ASC"})
     **/
    private $childrenPages;

    /**
     * @var AccessRightPage[]|ArrayCollection<AccessRightPage>
     *
     * @ORM\OneToMany(targetEntity="AccessRightPage", mappedBy="page")
     */
    protected $accessRights;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     * @Groups({"list_pages", "detail_page"})
     *
     */
    protected $uniquePasswordAccess;

    /**
     * @var Redirection[]|ArrayCollection<Redirection>
     *
     * @ORM\OneToMany(targetEntity="Redirection", mappedBy="page", cascade={"persist", "remove"}, orphanRemoval=true)
     * @Groups({"detail_page"})
     * @Assert\Valid()
     */
    protected $redirections;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=false)
     * @Groups({"list_pages", "detail_page", "list_sites", "detail_site", "detail_menu", "security_access_right_obj"})
     */
    protected $displayOrder;

    /**
     * @var Site
     *
     * @ORM\ManyToOne(targetEntity="Site")
     * @ORM\JoinColumn(name="site_id", referencedColumnName="id", nullable=false)
     */
    private $site;

    /**
     * @var string[]
     *
     * @ORM\Column(name="userRoles", type="array")
     * @Groups({"detail_page"})
     */
    private $userRoles = [];

    /**
     * @var bool
     *
     * @ORM\Column(name="page_parameters_locked", type="boolean")
     * @Groups({"detail_page"})
     */
    protected $isPageParametersLocked = false;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->childrenPages = new ArrayCollection();
        $this->accessRights = new ArrayCollection();
        $this->redirections = new ArrayCollection();
        $this->active = true;
        $this->metaNoIndex = false;
        $this->autoSlug = true;
        $this->autoMetas = true;
        $this->showInMenu = true;
        $this->differentPageTitle = false;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getFormType()
    {
        $type = get_class($this);
        $type = str_replace('\\Entity\\', '\\Form\\Type\\', $type);
        $type.= 'Type';

        return $type;
    }

    static function getTranslationClass()
    {
        return static::class.'Translation';
    }

    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @VirtualProperty()
     *
     * @Groups({"detail_page", "detail_menu"})
     */
    public function getMenuTitle($locale = null)
    {
        /** @var PageTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);

        return $proxy->getMenuTitle();
    }

    public function setMenuTitle($menuTitle, $locale = null)
    {
        /** @var PageTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);
        $proxy->setMenuTitle($menuTitle);

        if (true === $this->autoSlug) {
            $this->updateAutoSlug($locale);
        }
        if (true === $this->autoMetas) {
            $this->updateAutoMetas($locale);
        }

        return $this;
    }

    /**
     * @VirtualProperty()
     *
     * @Groups({"detail_page"})
     */
    public function getPageTitle($locale = null)
    {
        /** @var PageTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);

        if (!$this->differentPageTitle) {
            return $proxy->getMenuTitle();
        }

        return $proxy->getPageTitle();
    }

    public function setPageTitle($pageTitle, $locale = null)
    {
        /** @var PageTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);
        $proxy->setPageTitle($pageTitle);

        return $this;
    }

    /**
     * @VirtualProperty()
     *
     * @Groups({"detail_page", "detail_menu"})
     */
    public function getPageSubtitle($locale = null)
    {
        /** @var PageTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);

        return $proxy->getPageSubtitle();
    }

    public function setPageSubtitle($pageSubtitle, $locale = null)
    {
        /** @var PageTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);
        $proxy->setPageSubtitle($pageSubtitle);

        return $this;
    }

    /**
     * @VirtualProperty()
     *
     * @Groups({"list_pages", "list_sites", "detail_site", "detail_page", "detail_menu"})
     */
    public function getName()
    {
        $locale = TranslationProxy::getDefaultLocale();
        if ('all' == $locale) {
            TranslationProxy::setDefaultLocale(null);
        }

        $name = $this->getMenuTitle();

        // if title is empty, find the title of another locale
        if (empty($name)) {
            foreach ($this->translations as $translation) {
                $name = $translation->getMenuTitle();
                if (!empty($name)) {
                    break;
                }
            }
        }

        TranslationProxy::setDefaultLocale($locale);

        return $name;
    }

    /**
     * @VirtualProperty()
     *
     * @Groups({"list_pages", "detail_page"})
     */
    public function getSlug($locale = null)
    {
        /** @var PageTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);

        return $proxy->getSlug();
    }

    public function setSlug($slug, $locale = null)
    {
        /** @var PageTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);

        if (null !== $slug && mb_substr($slug, 0, 1) !== '/') {
            $slug = '/'.$slug;
        }
        $proxy->setSlug($slug);

        return $this;
    }

    /**
     * @VirtualProperty()
     *
     * @Groups({"detail_page"})
     */
    public function getMetaTitle($locale = null)
    {
        /** @var PageTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);

        return $proxy->getMetaTitle();
    }

    public function setMetaTitle($metaTitle, $locale = null)
    {
        /** @var PageTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);
        $proxy->setMetaTitle($metaTitle);

        return $this;
    }

    /**
     * @VirtualProperty()
     *
     * @Groups({"detail_page"})
     */
    public function getMetaDescription($locale = null)
    {
        /** @var PageTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);

        return $proxy->getMetaDescription();
    }

    public function setMetaDescription($metaDescription, $locale = null)
    {
        /** @var PageTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);
        $proxy->setMetaDescription($metaDescription);

        return $this;
    }

    /**
     * Get site's URI
     *
     * @VirtualProperty()
     * @Groups({"detail_page"})
     *
     * @return string
     */
    public function getSiteUri()
    {
        return $this->getSite()->getUri();
    }

    /**
     * Full slug (used for dynamic routing)
     * @VirtualProperty
     * @Groups({"detail_page"})
     */
    public function getFullSlug($locale = null)
    {
        $parentPage = $this->getParentPage();
        if (!$parentPage) {
            return $this->getSlug($locale);
        }

        $parentSlug = $parentPage->getFullSlug($locale);

        if (is_array($parentSlug)) {
            $fullSlug = [];
            foreach ($parentSlug as $slugLocale => $slug) {
                $fullSlug[$slugLocale] = $slug .'/'. $this->getSlug($slugLocale);
            }
        }
        else {
            $fullSlug = $parentPage->getFullSlug($locale).'/'.$this->getSlug($locale);
        }

        return $fullSlug;
    }

    public function hasAutoSlug()
    {
        return $this->autoSlug;
    }

    public function setAutoSlug($autoSlug)
    {
        $this->autoSlug = $autoSlug;

        if (true === $autoSlug) {
            $this->updateAutoSlugs();
        }

        return $this;
    }

    private function updateAutoSlug($locale)
    {
        $title = $this->getMenuTitle($locale);
        if (null != $title) {
            $this->setSlug(StringHelper::slugify($title), $locale);
        }
    }

    private function updateAutoSlugs()
    {
        foreach ($this->translations as $locale => $translation) {
            $this->updateAutoSlug($locale);
        }
    }

    public function hasAutoMetas()
    {
        return $this->autoMetas;
    }

    public function setAutoMetas($autoMetas)
    {
        $this->autoMetas = $autoMetas;

        if (true === $autoMetas) {
            $this->updateAllAutoMetas();
        }

        return $this;
    }

    private function updateAutoMetas($locale)
    {
        $title = $this->getMenuTitle($locale);
        if (null != $title) {
            $this->setMetaTitle($title, $locale);
            $this->setMetaDescription(null, $locale);
        }
    }

    private function updateAllAutoMetas()
    {
        foreach ($this->translations as $locale => $translation) {
            $this->updateAutoMetas($locale);
        }
    }

    /**
     * Set active
     *
     * @param  boolean $active
     * @return Page
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }

    public function setShowInMenu($showInMenu)
    {
        $this->showInMenu = $showInMenu;

        return $this;
    }

    public function isShowInMenu()
    {
        return $this->showInMenu;
    }

    public function isShownInMenu()
    {
        return $this->showInMenu;
    }

    /**
     * @return Menu
     */
    public function getMenu()
    {
        return $this->menu;
    }

    public function setMenu(Menu $menu = null)
    {
        if (null != $menu) {
            $this->setParentPage(null);
            $menu->addPage($this);
            if (null == $this->displayOrder) {
                $this->displayOrder = $menu->getNextChildPageOrder();
            }
        } else {
            if (null != $this->getMenu()) {
                $this->getMenu()->removePage($this);
            }
        }
        $this->menu = $menu;

        if (null != $menu) {
            $this->site = $menu->getSite();

            // force site link update on children
            foreach ($this->childrenPages as $page) {
                $page->setParentPage($this);
            }
        }

        return $this;
    }

    /**
     * @VirtualProperty
     * @Groups({"security_access_right_obj"})
     */
    public static function getAccessRightType()
    {
        return 'page';
    }

    public static function getAccessRightClassName()
    {
        return AccessRightPage::class;
    }

    /*
     * Used for FrontofficeVoter to determine the access rights.
     */
    public function getParentsSecurityContextObject()
    {
        if ($parentPage = $this->getParentPage()) {
            return [$parentPage];
        } else {
            return [$this->getMenu()->getSite()];
        }
    }

    /**
     * @VirtualProperty
     * @Groups({"security_access_right_obj"})
     */
    public function getChildrenSecurityContextObject()
    {
        return $this->getChildrenPages(); //used for access right object
    }

    /*
     * Used for SecurityVoter to determine the access rights class.
     */
    public static function getParentsClassesSecurityContextObject()
    {
        return Site::class;
    }

    public static function getChildrenClassesSecurityContextObject()
    {
        return [
            self::class,
            CmsFileImage::class,
            CmsFileVideo::class,
            CmsFileImageGallery::class,
            CmsFileRichText::class,
            CmsFileTitle::class,
            CmsFileDocument::class,
            CmsFileDocumentList::class,
        ];
    }

    /**
     * @VirtualProperty
     * @Groups({"security_access_right_obj"})
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * Add childPage
     *
     * @param Page $childPage
     */
    public function addChildPage(Page $childPage)
    {
        if (!$this->getChildrenPages()->contains($childPage)) {
            $this->getChildrenPages()->add($childPage);
        }
    }

    /**
     * Get childrenPages
     *
     * @return Collection $childrenPages
     */
    public function getChildrenPages()
    {
        return $this->childrenPages;
    }

    public function hasChildrenPages()
    {
        return ($this->getChildrenPages()->count() > 0);
    }

    /**
     *
     */
    public function removeChildPage($childPage)
    {
        if (!$this->getChildrenPages()->contains($childPage)) {
            $this->getChildrenPages()->removeElement($childPage);
        }

        return $this;
    }

    /**
     * @param $locale
     * @return Page[]
     */
    public function getActiveChildrenPagesShownInMenu($locale)
    {
        return $this->childrenPages->filter(function(Page $page) use($locale) {
            return $page->isActive() && $page->isShownInMenu() && $page->getTranslations()->containsKey($locale);
        })->toArray();
    }

    /**
     * @param $locale
     * @return Page[]
     */
    public function getActiveChildrenPages($locale)
    {
        return $this->childrenPages->filter(function(Page $page) use($locale) {
            return $page->isActive() && $page->getTranslations()->containsKey($locale);
        })->toArray();
    }

    /**
     * Set parentPage
     * Used for dynamic routing
     * @param Page $parentPage
     * @return $this
     */
    public function setParentPage($parentPage)
    {
        if (null != $parentPage) {
            $this->setMenu(null);
            $parentPage->addChildPage($this);
            if (null == $this->displayOrder) {
                $this->displayOrder = $parentPage->getNextChildPageOrder();
            }
        } else {
            if (null != $this->getParentPage()) {
                $this->getParentPage()->removeChildPage($this);
            }
        }
        $this->parentPage = $parentPage;

        if (null != $parentPage) {
            $this->site = $parentPage->getSite();

            // force site link update on children
            foreach ($this->childrenPages as $page) {
                $page->setParentPage($this);
            }
        }

        return $this;
    }

    /**
     * Get parentPage
     * Used for dynamic routing
     * @return Page $parentPage
     */
    public function getParentPage()
    {
        return $this->parentPage;
    }

    /**
     * @VirtualProperty
     * @Groups({"list_pages", "detail_page", "list_sites", "detail_site", "detail_menu"})
     * @deprecated Use instanceof instead
     */
    public function getPageType()
    {
        return '';
    }

    /**
     * @VirtualProperty
     * @Groups({"detail_page"})
     */
    public function getParentPageId()
    {
        return $this->getParentPage() ? $this->getParentPage()->getId() : null;
    }

    /**
     * @VirtualProperty
     * @Groups({"detail_page"})
     */
    public function getMenuId()
    {
        return $this->getMenu() ? $this->getMenu()->getId() : null;
    }

    public function setRedirections(Collection $redirections)
    {
        foreach ($redirections as $redirection) {
            $this->addRedirection($redirection);
        }
        return $this;
    }

    public function addRedirection(Redirection $redirection)
    {
        if (!$this->getRedirections()->contains($redirection)) {
            $this->getRedirections()->add($redirection);
            $redirection->setPage($this);
        }

        return $this;
    }

    public function getRedirections()
    {
        return $this->redirections;
    }

    public function hasRedirections()
    {
        return ($this->getRedirections()->count() > 0);
    }

    public function getDisplayOrder()
    {
        return $this->displayOrder;
    }

    public function setDisplayOrder($displayOrder)
    {
        if (null == $displayOrder) {
            if (null != $this->menu) {
                $displayOrder = $this->menu->getNextChildPageOrder();
            } elseif (null != $this->parentPage) {
                $displayOrder = $this->parentPage->getNextChildPageOrder();
            }
        }
        $this->displayOrder = $displayOrder;
        return $this;
    }

    public function getNextChildPageOrder()
    {
        return $this->childrenPages->count();
    }

    public function getParent()
    {
        return (null !== $this->menu)? $this->menu:$this->parentPage;
    }

    public function hasDifferentPageTitle()
    {
        return $this->differentPageTitle;
    }

    public function setDifferentPageTitle($differentPageTitle)
    {
        $this->differentPageTitle = $differentPageTitle;
        if (!$differentPageTitle) {
            foreach ($this->translations as $translation) {
                $translation->setPageTitle(null);
            }
        }
        return $this;
    }

    /**
    * Is metaNoIndex
    * @return boolean
    */
    public function isMetaNoIndex()
    {
        return $this->metaNoIndex;
    }

    /**
    * Set metaNoIndex
    * @param  boolean $metaNoIndex
    * @return Page
    */
    public function setMetaNoIndex($metaNoIndex)
    {
        $this->metaNoIndex = $metaNoIndex;
        return $this;
    }

    /**
     * @Assert\IsTrue(message = "A page cannot have a parent menu and a parent page at the same time")
     */
    public function isValidParent()
    {
        if (null != $this->parentPage && null != $this->menu) {
            return false;
        }
        return true;
    }

    /**
     * @VirtualProperty
     * @Groups({"list_access_rights"})
     */
    public function isChildPageAllowed()
    {
        // forbib children page is page is defined as home for at least one locale
        foreach ($this->translations as $translation) {
            if ('' === $translation->getSlug()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @Assert\IsTrue(message = "children.pages.are.not.allowed.on.this.page")
     */
    public function isValidChildrenPages()
    {
        if ($this->hasChildrenPages() && !$this->isChildPageAllowed()) {
            return false;
        }
        return true;
    }

    /**
     * @Assert\IsTrue(message = "children.pages.are.not.allowed.on.this.page")
     */
    public function isValidParentPage()
    {
        if ($this->parentPage && !$this->parentPage->isChildPageAllowed()) {
            return false;
        }
        return true;
    }

    public function getSite()
    {
        if (null != $this->menu) {
            return $this->menu->getSite();
        } else {
            $parentPage = $this->parentPage;
            while (null != $parentPage->getParentPage()) {
                $parentPage = $parentPage->getParentPage();
            }
            return $parentPage->getMenu()->getSite();
        }
    }

    public function getUserRoles()
    {
        return $this->userRoles;
    }

    public function setUserRoles($userRoles)
    {
        $this->userRoles = $userRoles;
        return $this;
    }

    public function isPrivate()
    {
        return count($this->userRoles) > 0;
    }

    /**
     * @return string
     */
    public function getUniquePasswordAccess()
    {
        return $this->uniquePasswordAccess;
    }

    /**
     * @param string $uniquePasswordAccess
     * @return self
     */
    public function setUniquePasswordAccess($uniquePasswordAccess)
    {
        $this->uniquePasswordAccess = $uniquePasswordAccess;

        return $this;
    }

    /**
     * Get breadcrumb
     *
     * @return array
     */
    public function getBreadcrumb()
    {
        $breadcrumb = [$this];
        $page = $this;
        while ($page = $page->getParentPage()) {
            array_unshift($breadcrumb, $page);
        }
        return $breadcrumb;
    }

    /**
     * Is page parameters locked
     *
     * @param boolean $isPageParametersLocked
     *
     * @return boolean|self
     */
    public function isPageParametersLocked($isPageParametersLocked = null)
    {
        if (null !== $isPageParametersLocked) {
            $this->isPageParametersLocked = $isPageParametersLocked;
            return $this;
        }

        return $this->isPageParametersLocked;
    }
}
