<?php

namespace Azimut\Bundle\MontgolfiereAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * RestitutionItem
 *
 * @ORM\Table(name="montgolfiere_restitution_item", uniqueConstraints={
 *        @UniqueConstraint(name="item_unique",
 *            columns={"combination", "categorie", "theme_id"})
 *    })
 * @ORM\Entity(repositoryClass="Azimut\Bundle\MontgolfiereAppBundle\Repository\RestitutionItemRepository")
 *
 * @Assert\Expression(
 *     "!((this.getTrendText() != null and this.getActionPlanText() == null) or (this.getActionPlanText() != null and this.getTrendText() == null))" ,
 *     message="Merci de remplir les deux textes"
 * )
 */
class RestitutionItem
{
    const COLOR_GREEN = 'G',
        COLOR_BLUE = 'B',
        COLOR_YELLOW = 'Y';

    const CATEGORY_BAD = 0,
        CATEGORY_DISPARATE = 1,
        CATEGORY_UNBALANCED = 2,
        CATEGORY_COMPENSATED = 3,
        CATEGORY_CONSISTENT = 4
    ;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $combination;

    /**
     * @var Theme
     *
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\MontgolfiereAppBundle\Entity\Theme", inversedBy="restitutionItems")
     * @Serializer\Exclude()
     */
    private $theme;

    /**
     * @var string
     *
     * @ORM\Column(name="trend_text", type="text")
     */
    private $trendText;

    /**
     * @var string
     *
     * @ORM\Column(name="action_plan_text", type="text")
     */
    private $actionPlanText;

    /**
     * @var int
     * @ORM\Column(name="categorie", type="integer")
     * @Assert\Choice(min="1", max="1", choices={
     *     RestitutionItem::CATEGORY_BAD,
     *     RestitutionItem::CATEGORY_DISPARATE,
     *     RestitutionItem::CATEGORY_UNBALANCED,
     *     RestitutionItem::CATEGORY_COMPENSATED,
     *     RestitutionItem::CATEGORY_CONSISTENT
     * })
     */
    private $category;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function getCombination(): string
    {
        return $this->combination;
    }

    public function setCombination(string $combination): self
    {
        $this->combination = $combination;

        return $this;
    }

    public function setTheme(Theme $theme): self
    {
        $this->theme = $theme;

        return $this;
    }

    public function getTheme(): Theme
    {
        return $this->theme;
    }

    /**
     * Set textTend
     *
     * @param string $trendText
     *
     * @return RestitutionItem
     */
    public function setTrendText($trendText)
    {
        $this->trendText = $trendText;

        return $this;
    }

    /**
     * Get textTend
     *
     * @return string
     */
    public function getTrendText()
    {
        return $this->trendText;
    }

    /**
     * Set textPa
     *
     * @param string $actionPlanText
     *
     * @return RestitutionItem
     */
    public function setActionPlanText($actionPlanText)
    {
        $this->actionPlanText = $actionPlanText;

        return $this;
    }

    /**
     * Get textPa
     *
     * @return string
     */
    public function getActionPlanText()
    {
        return $this->actionPlanText;
    }

    /**
     * Set category
     *
     * @param int $category
     *
     * @return RestitutionItem
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return int
     */
    public function getCategory()
    {
        return $this->category;
    }

    public function __clone()
    {
        $this->id = null;
    }
}

