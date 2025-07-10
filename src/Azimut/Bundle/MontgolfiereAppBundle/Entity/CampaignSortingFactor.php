<?php


namespace Azimut\Bundle\MontgolfiereAppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(name="montgolfiere_campaign_sorting_factor")
 * @ORM\Entity()
 */
class CampaignSortingFactor
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
     * @var Campaign
     *
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\MontgolfiereAppBundle\Entity\Campaign", inversedBy="sortingFactors")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $campaign;

    /**
     * @var string[]
     *
     * @ORM\Column(name="names", type="json_array", nullable=true)
     * @Serializer\Groups({"backoffice_sorting_factors_list"})
     */
    private $names;

    /**
     * @var CampaignSortingFactorValue[]|ArrayCollection<CampaignSortingFactorValue>
     *
     * @ORM\OneToMany(targetEntity="Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignSortingFactorValue", mappedBy="sortingFactor", cascade={"persist"})
     * @Serializer\Groups({"backoffice_sorting_factors_list"})
     * @ORM\OrderBy({"position":"ASC"})
     */
    private $values;

    public function __construct()
    {
        $this->names = [];
        $this->values = new ArrayCollection();
    }

    public function __clone()
    {
        $this->id = $this->campaign = null;
        $values = [];
        foreach ($this->values as $value) {
            $values[] = (clone $value)->setSortingFactor($this);
        }
        $this->values = new ArrayCollection($values);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCampaign(): Campaign
    {
        return $this->campaign;
    }

    public function setCampaign(Campaign $campaign): self
    {
        $this->campaign = $campaign;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getNames(): array
    {
        return $this->names;
    }

    public function setNames(array $names): self
    {
        $this->names = $names;

        return $this;
    }

    /**
     * @return CampaignSortingFactorValue[]|ArrayCollection
     */
    public function getValues()
    {
        return $this->values;
    }

}
