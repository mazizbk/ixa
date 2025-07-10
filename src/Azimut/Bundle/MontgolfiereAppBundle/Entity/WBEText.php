<?php


namespace Azimut\Bundle\MontgolfiereAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="montgolfiere_well_being_engagement_text")
 * @ORM\Entity()
 */
class WBEText
{
    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     */
    protected $engagementProfile;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     */
    protected $wellBeingProfile;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $locale;

    /**
     * @var ?string
     *
     * @ORM\Column(name="title", type="string", length=150, nullable=true)
     * @Assert\Length(max="150")
     */
    protected $title;

    /**
     * @var ?string
     *
     * @ORM\Column(type="text")
     */
    protected $text;

    /**
     * @var ?string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $advice;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    public function getEngagementProfile(): int
    {
        return $this->engagementProfile;
    }

    public function setEngagementProfile(int $engagementProfile): self
    {
        $this->engagementProfile = $engagementProfile;

        return $this;
    }

    public function getWellBeingProfile(): int
    {
        return $this->wellBeingProfile;
    }

    public function setWellBeingProfile(int $wellBeingProfile): self
    {
        $this->wellBeingProfile = $wellBeingProfile;

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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }


    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getAdvice(): ?string
    {
        return $this->advice;
    }

    public function setAdvice(?string $advice): self
    {
        $this->advice = $advice;

        return $this;
    }

}
