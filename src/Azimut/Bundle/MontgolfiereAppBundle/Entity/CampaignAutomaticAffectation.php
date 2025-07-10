<?php

namespace Azimut\Bundle\MontgolfiereAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CampaignAutomaticAffectation
 *
 * @ORM\Table(name="montgolfiere_campaign_automatic_affectation")
 * @ORM\Entity(repositoryClass="Azimut\Bundle\MontgolfiereAppBundle\Repository\CampaignAutomaticAffectationRepository")
 */
class CampaignAutomaticAffectation
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
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\MontgolfiereAppBundle\Entity\Campaign", inversedBy="automaticAffectations")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $campaign;

    /**
     * @var CampaignSortingFactorValue[]&iterable<CampaignSortingFactorValue>
     *
     * @ORM\ManyToMany(targetEntity="Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignSortingFactorValue", fetch="EAGER")
     * @ORM\JoinTable(name="montgolfiere_campaign_automatic_affectation_values")
     */
    private $sortingFactorValues;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=false)
     */
    private $locale;


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
     * @return CampaignSortingFactorValue[]&iterable<CampaignSortingFactorValue>
     */
    public function getSortingFactorValues()
    {
        return $this->sortingFactorValues;
    }

    public function setSortingFactorValues(array $sortingFactorValues): self
    {
        $this->sortingFactorValues = $sortingFactorValues;

        return $this;
    }

    public function addSortingFactorValue(CampaignSortingFactorValue $value): self
    {
        $this->sortingFactorValues[] = $value;

        return $this;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

}

