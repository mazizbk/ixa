<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-02-02 16:16:50
 */

namespace Azimut\Bundle\FrontofficeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\DoctrineExtraBundle\Entity\EntityTranslationInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="frontoffice_page_translation", indexes={@ORM\Index(name="slug_idx", columns={"slug"})})
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({"content" = "PageContentTranslation", "alias" = "PageAliasTranslation", "placeholder" = "PagePlaceholderTranslation", "link" = "PageLinkTranslation"})
 */
abstract class PageTranslation implements EntityTranslationInterface
{

    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Page
     *
     * @ORM\ManyToOne(targetEntity="Page", inversedBy="translations")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $page;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $locale;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $menuTitle;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $pageTitle;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $pageSubtitle;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Assert\Regex(
     *     pattern="/^[a-z0-9-]+$/",
     *     message="this.value.must.contain.only.lowercase.alphanumeric.characters.and.dashes"
     * )
     */
    private $slug;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=60, nullable=true)
     */
    private $metaTitle;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=160, nullable=true)
     */
    private $metaDescription;


    public function getId()
    {
        return $this->id;
    }

    public function getTranslatable()
    {
        return $this->page;
    }

    public function setTranslatable($translatable)
    {
        if (!$translatable instanceof Page) {
            throw new \RuntimeException('Expected $translatable to be an instance of Page');
        }

        $this->page = $translatable;

        return $this;
    }

    public function getLocale()
    {
        return $this->locale;
    }

    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    public function getMenuTitle()
    {
        return $this->menuTitle;
    }

    public function setMenuTitle($menuTitle)
    {
        $this->menuTitle = $menuTitle;

        return $this;
    }

    public function getPage()
    {
        return $this->page;
    }

    public function getPageTitle()
    {
        return $this->pageTitle;
    }

    public function setPageTitle($pageTitle)
    {
        $this->pageTitle = $pageTitle;

        return $this;
    }

    public function getPageSubtitle()
    {
        return $this->pageSubtitle;
    }

    public function setPageSubtitle($pageSubtitle)
    {
        $this->pageSubtitle = $pageSubtitle;
        return $this;
    }

    public function setSlug($slug)
    {
        if (null == $slug) {
            $slug = '';
        }

        $slug = ltrim($slug, '/');

        $this->slug = $slug;

        return $this;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function setMetaTitle($metaTitle)
    {
        $this->metaTitle = $metaTitle;

        return $this;
    }

    public function getMetaTitle()
    {
        if (null == $this->metaTitle) {
            return mb_substr($this->menuTitle, 0, 60);
        }

        return $this->metaTitle;
    }

    public function setMetaDescription($metaDescription)
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }

    public function getMetaDescription()
    {
        return $this->metaDescription;
    }
}
