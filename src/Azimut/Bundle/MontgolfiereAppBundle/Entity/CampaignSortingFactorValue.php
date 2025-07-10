<?php


namespace Azimut\Bundle\MontgolfiereAppBundle\Entity;


use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(name="montgolfiere_campaign_sorting_factor_value")
 * @ORM\Entity()
 */
class CampaignSortingFactorValue
{

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"backoffice_sorting_factors_list"})
     */
    private $id;

    /**
     * @var CampaignSortingFactor
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignSortingFactor", inversedBy="values")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Gedmo\SortableGroup()
     */
    private $sortingFactor;

    /**
     * @var string[]
     * @ORM\Column(name="labels", type="json_array", nullable=true)
     * @Serializer\Groups({"backoffice_sorting_factors_list"})
     */
    private $labels;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false)
     * @Serializer\Groups({"backoffice_sorting_factors_list"})
     */
    private $workforce;

    /**
     * @var integer
     * @ORM\Column(type="integer", nullable=false)
     * @Gedmo\SortablePosition()
     * @Serializer\Groups({"backoffice_sorting_factors_list"})
     */
    private $position;

    public function __construct()
    {
        $this->labels = [];
        $this->workforce = 0;
    }

    public function __clone()
    {
        $this->id = $this->sortingFactor = null;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getSortingFactor(): CampaignSortingFactor
    {
        return $this->sortingFactor;
    }

    public function setSortingFactor(CampaignSortingFactor $sortingFactor): self
    {
        $this->sortingFactor = $sortingFactor;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getLabels(): array
    {
        return $this->labels;
    }

    /**
     * @param string[] $labels
     * @return self
     */
    public function setLabels(array $labels): self
    {
        $this->labels = $labels;

        return $this;
    }

    public function getWorkforce(): int
    {
        return $this->workforce;
    }

    public function setWorkforce(int $workforce): self
    {
        $this->workforce = $workforce;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

}
