<?php


namespace Azimut\Bundle\MontgolfiereAppBundle\Entity;

use Azimut\Bundle\MontgolfiereAppBundle\Model\HouseSettings;
use Azimut\Bundle\MontgolfiereAppBundle\Model\VirtualThemeSettings;
use Azimut\Bundle\MontgolfiereAppBundle\Model\WordSettings;
use Azimut\Bundle\MontgolfiereAppBundle\Traits\UploadableEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="montgolfiere_theme")
 */
class Theme
{
    public const TYPE_FIXED = 'fixed';
    public const TYPE_FREE = 'free';

    use UploadableEntity;

    /**
     * @var integer
     * @ORM\Id()
     * @ORM\Column(type="smallint")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"backoffice_segments_list"})
     */
    protected $id;

    /**
     * @var string[]
     *
     * @ORM\Column(name="name", type="json", nullable=false)
     * @Assert\All(@Assert\NotBlank())
     * @Serializer\Groups({"backoffice_segments_list"})
     */
    protected $name;

    /**
     * @var string[]
     *
     * @ORM\Column(name="description", type="json", nullable=false)
     */
    protected $description;

    /**
     * @var string
     * @ORM\Column(name="type", type="string", nullable=false)
     */
    protected $type;

    /**
     * @var ArrayCollection<array-key, Item>
     *
     * @ORM\OneToMany(targetEntity="Azimut\Bundle\MontgolfiereAppBundle\Entity\Item", mappedBy="theme")
     * @ORM\OrderBy({"position":"ASC"})
     */
    protected $items;

    /**
     * @var integer
     * @ORM\Column(type="integer", nullable=false)
     * @Gedmo\SortablePosition()
     */
    private $position;

    /**
     * @var HouseSettings|null
     * @ORM\Column(type="jms_json", nullable=true)
     */
    private $houseSettings;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $skipInAnalysis = false;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $virtual;

    /**
     * @var VirtualThemeSettings|null
     * @ORM\Column(type="jms_json", nullable=true)
     */
    private $virtualSettings;

    /**
     * @var WordSettings|null
     * @ORM\Column(type="jms_json", nullable=true)
     */
    private $wordSettings;

    /**
     * @var AnalysisVersion
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\MontgolfiereAppBundle\Entity\AnalysisVersion")
     * @ORM\JoinColumn(nullable=false)
     * @Gedmo\SortableGroup()
     */
    private $analysisVersion;

    /**
     * @var RestitutionItem[]
     * @ORM\OneToMany(targetEntity="Azimut\Bundle\MontgolfiereAppBundle\Entity\RestitutionItem", mappedBy="theme")
     */
    private $restitutionItems;

    public function __construct()
    {
        $this->houseSettings = new HouseSettings();
        $this->houseSettings->setType(HouseSettings::TYPE_THEME);
    }

    /**
     * @return int
     */
    public function getId(): int
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

    /**
     * @return string[]
     */
    public function getDescription(): array
    {
        return $this->description;
    }

    /**
     * @param string[] $description
     * @return self
     */
    public function setDescription(array $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

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

    /**
     * @return ArrayCollection<array-key, Item>
     */
    public function getItems()
    {
        return $this->items;
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

    public function isSkipInAnalysis(): bool
    {
        return $this->skipInAnalysis;
    }

    public function setSkipInAnalysis(bool $skipInAnalysis): self
    {
        $this->skipInAnalysis = $skipInAnalysis;

        return $this;
    }

    public function isVirtual(): bool
    {
        return $this->virtual;
    }

    public function setVirtual(bool $virtual): self
    {
        $this->virtual = $virtual;

        return $this;
    }

    public function getVirtualSettings(): ?VirtualThemeSettings
    {
        return $this->virtualSettings;
    }

    public function setVirtualSettings(?VirtualThemeSettings $virtualSettings): self
    {
        $this->virtualSettings = $virtualSettings;

        return $this;
    }

    public function getWordSettings(): ?WordSettings
    {
        return $this->wordSettings;
    }

    public function setWordSettings(?WordSettings $wordSettings): self
    {
        $this->wordSettings = $wordSettings;

        return $this;
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

    /**
     * @return RestitutionItem[]
     */
    public function getRestitutionItems()
    {
        return $this->restitutionItems;
    }

    public function __clone()
    {
        $this->id = null;
    }

}
