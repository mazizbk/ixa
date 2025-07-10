<?php

namespace Azimut\Bundle\MontgolfiereAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CampaignAnalysisGroup
 *
 * @ORM\Table(name="montgolfiere_campaign_analysis_group")
 * @ORM\Entity
 */
class CampaignAnalysisGroup
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Campaign
     *
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\MontgolfiereAppBundle\Entity\Campaign", inversedBy="analysisGroups")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $campaign;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var array
     *
     * @ORM\Column(name="criteria", type="array")
     */
    private $criteria = [];

    public function __construct(?int $id = null)
    {
        $this->id = $id;
    }


    public function getId(): int
    {
        return $this->id;
    }

    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
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

    public function setCriteria(array $criteria): self
    {
        $this->criteria = $criteria;

        return $this;
    }

    public function getCriteria(): array
    {
        return $this->criteria;
    }
}

