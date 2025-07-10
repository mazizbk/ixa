<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2013-09-13
*/

namespace Azimut\Bundle\FrontofficeBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Groups;
use Azimut\Bundle\DoctrineExtraBundle\Translation\TranslationProxy;
use Azimut\Bundle\DoctrineExtraBundle\Entity\TranslatableEntityInterface;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\VirtualProperty;
use Azimut\Bundle\SecurityBundle\Security\ObjectAccessRightAware; //trait for access rights
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\Blameable\Traits\BlameableEntity;

/**
 * @ORM\Table(name="frontoffice_site")
 * @ORM\Entity(repositoryClass="Azimut\Bundle\FrontofficeBundle\Entity\Repository\SiteRepository")
 */
class Site implements TranslatableEntityInterface
{
    use ObjectAccessRightAware;
    use TimestampableEntity, BlameableEntity;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Groups({"list_sites","detail_site", "security_access_right_obj"})
     */
    protected $id;

    /**
     * @var SiteTranslation[]|ArrayCollection<SiteTranslation>
     *
     * @ORM\OneToMany(targetEntity="SiteTranslation", mappedBy="site", cascade={"persist", "remove"}, orphanRemoval=true, indexBy="locale")
     * @Assert\Valid()
     */
    protected $translations;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=false)
     * @Groups({"list_sites","detail_site", "detail_obj"})
     * @Assert\NotBlank()
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=false)
     * @Groups({"list_sites","detail_site", "detail_obj"})
     * @Assert\NotBlank()
     */
    protected $publisherName;

    /**
     * @var DomainName
     *
     * @ORM\OneToOne(targetEntity="DomainName", inversedBy="siteMain", cascade={"persist", "remove"}, orphanRemoval=true)
     * @Groups({"list_sites","detail_site"})
     * @Assert\Valid()
     */
    protected $mainDomainName;

    /**
     * @var DomainName[]|ArrayCollection<DomainName>
     *
     * @ORM\OneToMany(targetEntity="DomainName", mappedBy="siteSecondary", cascade={"persist", "remove"}, orphanRemoval=true)
     * @Groups({"list_sites","detail_site"})
     * @Assert\Valid()
     */
    protected $secondaryDomainNames;

    /**
     * @var Menu[]|ArrayCollection<Menu>
     *
     * @ORM\OneToMany(targetEntity="Menu", mappedBy="site", cascade={"persist", "remove"}, orphanRemoval=true)
     * @Groups({"list_sites","detail_site"})
     * @Assert\Valid()
     */
    protected $menus;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     * @Groups({"list_sites","detail_site"})
     */
    protected $active;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     * @Groups({"list_sites","detail_site"})
     */
    protected $metaNoIndex;

    /**
     * @var SiteLayout
     *
     * @ORM\ManyToOne(targetEntity="SiteLayout")
     * @ORM\JoinColumn(name="layout_id", referencedColumnName="id", nullable=false)
     * @Groups({"detail_site"})
     * @Assert\Valid()
     */
    private $layout;

    /**
     * @var AccessRightSite[]|ArrayCollection<AccessRightSite>
     *
     * @ORM\OneToMany(targetEntity="Azimut\Bundle\FrontofficeBundle\Entity\AccessRightSite", mappedBy="site")
     * @Assert\Valid()
     */
    protected $accessRights;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     * @Groups({"detail_site"})
     */
    private $commentsActive = false;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     * @Groups({"detail_site"})
     */
    private $commentModerationActive = true;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     * @Groups({"detail_site"})
     */
    private $commentRatingActive = false;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=5)
     * @Groups({"detail_site"})
     */
    protected $scheme = 'http';

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     * @Groups({"detail_site"})
     */
    private $searchEngineActive = true;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->menus = new ArrayCollection();
        $this->accessRights = new ArrayCollection();
        $this->secondaryDomainNames = new ArrayCollection();
        $this->active = true;
        $this->metaNoIndex = false;
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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    static function getTranslationClass()
    {
        return SiteTranslation::class;
    }

    public function getTranslations()
    {
        return $this->translations;
    }

     /**
     * Get parent used by FrontofficeVoter
     */
    public function getParentsSecurityContextObject()
    {
        return null;
    }

    /**
     * @VirtualProperty
     * @Groups({"security_access_right_obj"})
     */
    public static function getAccessRightType()
    {
        return 'site';
    }

    public static function getAccessRightClassName()
    {
        return 'Azimut\Bundle\FrontofficeBundle\Entity\AccessRightSite';
    }

     /**
     * @VirtualProperty
     * @Groups({"security_access_right_obj"})
     */
    public function getChildrenSecurityContextObject()
    {
        $children = [];
        foreach ($this->getMenus() as $menu) {
            $pages = $menu->getPages();
            if ($pages instanceof Collection) {
                $pages = $pages->toArray();
            }
            if (is_array($pages)) {
                $children = array_merge($children, $pages);
            }
        }

        return $children;
    }

    /*
     * Used for SecurityVoter to determine the access rights class.
     */
    public static function getParentsClassesSecurityContextObject()
    {
        return null;
    }

    public static function getChildrenClassesSecurityContextObject()
    {
        return [Page::class];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get publisherName
     *
     * @return string
     */
    public function getPublisherName()
    {
        return $this->publisherName;
    }

    /**
     * Set publisherName
     *
     * @param string $publisherName
     *
     * @return self
     */
    public function setPublisherName($publisherName)
    {
        $this->publisherName = $publisherName;
        return $this;
    }

    /**
    * @VirtualProperty()
    * @Groups({"detail_site"})
    */
    public function getTitle($locale = null)
    {
        /** @var SiteTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);

        return $proxy->getTitle();
    }

    public function setTitle($title, $locale = null)
    {
        /** @var SiteTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);
        $proxy->setTitle($title);

        return $this;
    }

    /**
     * Set active
     *
     * @param  boolean $active
     * @return Site
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

    public function getSecondaryDomainNames()
    {
        return $this->secondaryDomainNames;
    }

    public function addSecondaryDomainName(DomainName $domainName)
    {
        if (!$this->secondaryDomainNames->contains($domainName)) {
            $this->secondaryDomainNames->add($domainName);
            $domainName->setSiteSecondary($this);
        }

        return $this;
    }

    public function removeSecondaryDomainName($domainName)
    {
        if ($this->secondaryDomainNames->contains($domainName)) {
            $this->secondaryDomainNames->removeElement($domainName);
        }

        return $this;
    }

    /**
     * @return DomainName
     */
    public function getMainDomainName()
    {
        return $this->mainDomainName;
    }

    public function setMainDomainName(DomainName $domainName)
    {
        $this->mainDomainName = $domainName;
        $domainName->setSiteMain($this);
        return $this;
    }

    /**
     * Get menus
     *
     * @return ArrayCollection $menu
     */
    public function getMenus()
    {
        return $this->menus;
    }

    public function getMenu($placeholder)
    {
        if (null === $this->layout) {
            throw new \RuntimeException('Cannot get menu: no layout set in current site.');
        }

        foreach ($this->menus as $menu) {
            if ($menu->getPlaceholder() == $placeholder) {
                return $menu;
            }
        }

        $menuDefinition = $this->layout->getMenuDefinition($placeholder);

        $menu = new Menu();
        $menu->setMenuDefinition($menuDefinition);
        $this->addMenu($menu);

        return $menu;
    }

    public function addMenu(Menu $menu)
    {
        if (!$this->menus->contains($menu)) {
            $this->menus->add($menu);
            $menu->setSite($this);
        }

        return $this;
    }

    /**
     * @return SiteLayout
     */
    public function getLayout()
    {
        return $this->layout;
    }

    public function setLayout(SiteLayout $layout)
    {
        if (null != $this->layout && $this->layout !== $layout) {
            //delete menus of old layout
            $this->menus->clear();
        }

        $this->layout = $layout;

        $menuDefinitions = $this->layout->getMenuDefinitions();

        foreach ($menuDefinitions as $menuDefinition) {
            $menu = new Menu();
            $menu->setMenuDefinition($menuDefinition);
            $this->addMenu($menu);
        }

        return $this;
    }

    public function getTemplate()
    {
        if (null === $this->layout) {
            throw new \RuntimeException('Layout template not set in site.');
        }

        return $this->layout->getTemplate();
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
    * @return Site
    */
    public function setMetaNoIndex($metaNoIndex)
    {
        $this->metaNoIndex = $metaNoIndex;
        return $this;
    }

    /**
     * Has active user login
     * @return boolean
     */
    public function hasUserLogin()
    {
        return $this->layout->hasUserLogin();
    }

    /**
     * Has active shop functionnality
     * @return boolean
     */
    public function hasShop()
    {
        return $this->layout->hasShop();
    }

    /**
     * Set isCommentsActive
     *
     * @param  boolean $commentsActive
     * @return Site
     */
    public function setCommentsActive($commentsActive)
    {
        $this->commentsActive = $commentsActive;

        return $this;
    }

    /**
     * Get isCommentsActive
     *
     * @return boolean
     */
    public function isCommentsActive()
    {
        return $this->commentsActive;
    }

    /**
     * Set commentModerationActive
     *
     * @param  boolean $commentModerationActive
     * @return Site
     */
    public function setCommentModerationActive($commentModerationActive)
    {
        $this->commentModerationActive = $commentModerationActive;

        return $this;
    }

    /**
     * Get commentModerationActive
     *
     * @return boolean
     */
    public function isCommentModerationActive()
    {
        return $this->commentModerationActive;
    }

    /**
     * Set commentRatingActive
     *
     * @param  boolean $commentRatingActive
     * @return Site
     */
    public function setCommentRatingActive($commentRatingActive)
    {
        $this->commentRatingActive = $commentRatingActive;

        return $this;
    }

    /**
     * Get commentRatingActive
     *
     * @return boolean
     */
    public function isCommentRatingActive()
    {
        return $this->commentRatingActive;
    }

    /**
     * Get scheme
     *
     * @return string
     */
    public function getScheme()
    {
        return $this->scheme ? $this->scheme : 'http';
    }

    /**
     * Set scheme
     *
     * @param string $scheme
     *
     * @return self
     */
    public function setScheme($scheme)
    {
        $this->scheme = $scheme;
        return $this;
    }

    /**
     * Get URI
     *
     * @VirtualProperty
     * @Groups({"detail_site"})
     *
     * @return string
     */
    public function getUri()
    {
        return $this->getScheme().'://'.$this->mainDomainName->getName();
    }

    /**
     * Is search engine active
     *
     * @return bool
     */
    public function isSearchEngineActive()
    {
        return $this->searchEngineActive;
    }

    /**
     * Set searchEngineActive
     *
     * @param bool $searchEngineActive
     *
     * @return self
     */
    public function setSearchEngineActive($searchEngineActive)
    {
        $this->searchEngineActive = $searchEngineActive;
        return $this;
    }
}
