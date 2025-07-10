<?php

namespace Azimut\Bundle\MontgolfiereAppBundle\Entity;

use Azimut\Bundle\MontgolfiereAppBundle\Model\HouseSettings;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Item
 *
 * @ORM\Table(name="montgolfiere_item")
 * @ORM\Entity(repositoryClass="Azimut\Bundle\MontgolfiereAppBundle\Repository\ItemRepository")
 */
class Item
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"backoffice_segments_list"})
     */
    private $id;

    /**
     * @var string[]
     *
     * @ORM\Column(name="name", type="json", nullable=false)
     * @Assert\All(@Assert\NotBlank())
     * @Serializer\Groups({"backoffice_segments_list"})
     */
    private $name;

    /**
     * @var Theme
     *
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\MontgolfiereAppBundle\Entity\Theme", inversedBy="items")
     * @Gedmo\SortableGroup()
     */
    private $theme;

    /**
     * @var integer
     * @ORM\Column(type="integer", nullable=false)
     * @Gedmo\SortablePosition()
     */
    private $position;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=false)
     */
    private $wellBeingWeight = 0;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=false)
     */
    private $engagementWeight = 0;

    /**
     * @var HouseSettings|null
     * @ORM\Column(type="jms_json", nullable=true)
     */
    private $houseSettings;

    /**
     * @var bool|null
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $definesEngagementProfile;

    /**
     * @var RestitutionItemTableText[]&ArrayCollection
     * @ORM\OneToMany(targetEntity="Azimut\Bundle\MontgolfiereAppBundle\Entity\RestitutionItemTableText", mappedBy="item")
     */
    private $restitution;

    /**
     * @var AnalysisVersion
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\MontgolfiereAppBundle\Entity\AnalysisVersion")
     * @ORM\JoinColumn(nullable=false)
     */
    private $analysisVersion;

    /**
     * @var Tooltip[]
     * @ORM\OneToMany(targetEntity="Azimut\Bundle\MontgolfiereAppBundle\Entity\Tooltip", mappedBy="item")
     */
    private $tooltips;

    /**
     * @var Question[]
     * @ORM\OneToMany(targetEntity="Azimut\Bundle\MontgolfiereAppBundle\Entity\Question", mappedBy="item")
     */
    private $questions;

    public function __construct()
    {
        $this->houseSettings = new HouseSettings();
        $this->houseSettings->setType(HouseSettings::TYPE_ITEM);
        $this->restitution = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string[]
     */
    public function getName(): array
    {
        return $this->name;
    }

    /**
     * @param string[] $name
     * @return self
     */
    public function setName(array $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getTheme(): Theme
    {
        return $this->theme;
    }

    public function setTheme(Theme $theme): self
    {
        $this->theme = $theme;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getWellBeingWeight(): int
    {
        return $this->wellBeingWeight;
    }

    public function setWellBeingWeight(int $wellBeingWeight): self
    {
        $this->wellBeingWeight = $wellBeingWeight;

        return $this;
    }

    public function getEngagementWeight(): int
    {
        return $this->engagementWeight;
    }

    public function setEngagementWeight(int $engagementWeight): self
    {
        $this->engagementWeight = $engagementWeight;

        return $this;
    }

    public function getHouseSettings(): ?HouseSettings
    {
        return $this->houseSettings;
    }

    public function setHouseSettings(?HouseSettings $houseSettings): self
    {
        $this->houseSettings = $houseSettings;

        return $this;
    }

    public function getDefinesEngagementProfile(): ?bool
    {
        return $this->definesEngagementProfile;
    }

    public function setDefinesEngagementProfile(bool $definesEngagementProfile): self
    {
        $this->definesEngagementProfile = $definesEngagementProfile;

        return $this;
    }

    /**
     * @return ArrayCollection&RestitutionItemTableText[]
     */
    public function getRestitution()
    {
        return $this->restitution;
    }

    /**
     * @return Tooltip[]
     */
    public function getTooltips()
    {
        return $this->tooltips;
    }

    public function getQuestions()
    {
        return $this->questions;
    }

    public function __clone()
    {
        $this->id = null;
    }

    public function getAnalysisVersion(): AnalysisVersion
    {
        return $this->analysisVersion;
    }

    public function setAnalysisVersion(AnalysisVersion $analysisVersion): self
    {
        $this->analysisVersion = $analysisVersion;

        return $this;
    }

}
