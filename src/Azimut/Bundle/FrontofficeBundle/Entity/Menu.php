<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:    2013-09-13
 */

namespace Azimut\Bundle\FrontofficeBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="frontoffice_menu")
 * @ORM\Entity(repositoryClass="Azimut\Bundle\FrontofficeBundle\Entity\Repository\MenuRepository")
 */
class Menu
{
    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"list_menus","detail_menu","list_sites","detail_site", "security_access_right_obj"})
     */
    private $id;

    /**
     * @var MenuDefinition
     *
     * @ORM\ManyToOne(targetEntity="MenuDefinition")
     * @ORM\JoinColumn(name="menu_definition_id", onDelete="cascade")
     * @Assert\Valid()
     */
    private $menuDefinition;

    /**
     * @var Site
     *
     * @ORM\ManyToOne(targetEntity="Site", inversedBy="menus", cascade={"persist"}))
     * @ORM\JoinColumn(name="site_id", referencedColumnName="id", nullable=false, onDelete="cascade")
     * @Assert\Valid()
     */
    private $site;

    /**
     * @var Page[]|ArrayCollection<Page>
     *
     * @ORM\OneToMany(targetEntity="Page", mappedBy="menu", cascade={"persist"}))
     * @Groups({"list_sites", "detail_site", "detail_menu"})
     * @ORM\OrderBy({"displayOrder" = "ASC"})
     * @Assert\Valid()
     */
    private $pages;

    public function __construct()
    {
        $this->accessRights = new ArrayCollection();
        $this->pages        = new ArrayCollection();
        $this->pageAliases  = new ArrayCollection();
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

    /**
     * @VirtualProperty
     * @Groups({"list_menus","detail_menu","list_sites","detail_site"})
     */
    public function getName()
    {
        if (null === $this->menuDefinition) {
            throw new \RuntimeException('Cannot return Menu name: no definition set on entity.');
        }

        return $this->menuDefinition->getName();
    }

    /**
     * @VirtualProperty
     * @Groups({"detail_menu"})
     */
    public function getPlaceholder()
    {
        if (null === $this->menuDefinition) {
            throw new \RuntimeException('Cannot return Menu name: no definition set on entity.');
        }

        return $this->menuDefinition->getPlaceholder();
    }


    /**
     * Get site
     *
     * @return Site
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * Set site
     *
     * @param Site
     * @return Menu
     */
    public function setSite($site)
    {
        $this->site = $site;

        return $this;
    }

    public function getMenuDefinition()
    {
        return $this->menuDefinition;
    }

    public function setMenuDefinition(MenuDefinition $menuDefinition)
    {
        $this->menuDefinition = $menuDefinition;

        return $this;
    }

    public function addPage($page)
    {
        if (!$this->getPages()->contains($page)) {
            $this->getPages()->add($page);
        }

        return $this;
    }

    public function removePage($page)
    {
        if ($this->getPages()->contains($page)) {
            $this->getPages()->removeElement($page);
        }

        return $this;
    }

    public function getPages()
    {
        return $this->pages;
    }

    /*public function getActivePages()
    {
        $pages = [];
        foreach ($this->pages->toArray() as $page) {
            if($page->isActive()) $pages[] = $page;
        }
        return $pages;
    }*/

    public function getActivePagesShownInMenu($locale)
    {
        $pages = [];
        foreach ($this->pages as $page) {
            if ($page->isActive() && $page->isShownInMenu() && $page->getTranslations()->containsKey($locale)) {
                $pages[] = $page;
            }
        }
        return $pages;
    }

    /**
     * @VirtualProperty
     * @Groups({"detail_menu"})
     */
    public function getSiteId()
    {
        return $this->getSite()->getId();
    }

    public function getNextChildPageOrder()
    {
        return $this->pages->count();
    }

    /**
     * Is first page level locked
     *
     * @return boolean
     */
    public function isFirstPageLevelLocked()
    {
        return $this->menuDefinition->isFirstPageLevelLocked();
    }

    /**
     * Get maxPagesCount
     *
     * @return int|null
     */
    public function getMaxPagesCount()
    {
        return $this->menuDefinition->getMaxPagesCount();
    }

    /**
     * Is max pages count reached
     *
     * @return boolean
     */
    public function isMaxPagesCountReached()
    {
        return null != $this->getMaxPagesCount() && $this->pages->count() > $this->getMaxPagesCount();
    }
}
