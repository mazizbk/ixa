<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-03-06 11:45:31
 */

namespace Azimut\Bundle\FrontofficeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="frontoffice_menu_definition")
 */
class MenuDefinition
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"detail_site_layout"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50)
     * @Groups({"detail_site_layout"})
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50)
     * @Groups({"detail_site_layout"})
     * @Assert\NotBlank()
     */
    private $placeholder;

    /**
     * @var SiteLayout
     *
     * @ORM\ManyToOne(targetEntity="SiteLayout", inversedBy="menuDefinitions")
     * @ORM\JoinColumn(name="layout_id")
     */
    private $layout;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_first_page_level_locked", type="boolean")
     * @Groups({"detail_site_layout"})
     */
    protected $isFirstPageLevelLocked = false;

    /**
     * @var int
     *
     * @ORM\Column(name="max_pages_count", type="smallint", nullable=true)
     * @Groups({"detail_site_layout"})
     */
    protected $maxPagesCount;

    public function __construct($placeholder = null, $name = null)
    {
        $this->setPlaceholder($placeholder);
        $this->setName($name);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getLayout()
    {
        return $this->layout;
    }

    public function setLayout(SiteLayout $layout)
    {
        $this->layout = $layout;

        return $this;
    }

    /**
     * Get placeholder
     *
     * @return string
     */
    public function getPlaceholder()
    {
        return $this->placeholder;
    }

    /**
     * Set placeholder
     *
     * @param  string $placeholder
     * @return MenuDefinition
     */
    public function setPlaceholder($placeholder)
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    /**
     * Is first page level locked
     *
     * @param boolean $isFirstPageLevelLocked
     *
     * @return boolean|self
     */
    public function isFirstPageLevelLocked($isFirstPageLevelLocked = null)
    {
        if (null !== $isFirstPageLevelLocked) {
            $this->isFirstPageLevelLocked = $isFirstPageLevelLocked;
            return $this;
        }

        return $this->isFirstPageLevelLocked;
    }

    /**
     * Get maxPagesCount
     *
     * @return int|null
     */
    public function getMaxPagesCount()
    {
        return $this->maxPagesCount;
    }

    /**
     * Set maxPagesCount
     *
     * @param int|null $maxPagesCount
     *
     * @return self
     */
    public function setMaxPagesCount($maxPagesCount)
    {
        $this->maxPagesCount = $maxPagesCount;
        return $this;
    }
}
