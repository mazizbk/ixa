<?php

namespace Azimut\Bundle\MontgolfiereAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="montgolfiere_analysis_version")
 * @ORM\Entity(repositoryClass="Azimut\Bundle\MontgolfiereAppBundle\Repository\AnalysisVersionRepository")
 */
class AnalysisVersion
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
     * @var string[]
     * @ORM\Column(type="simple_array")
     */
    private $colors;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $blocksShadow;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $displayLegendTitles;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $legendY;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $legendBorderWidth;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $legendZonesIcons;

    /**
     * @var array{hasLegends:bool,items:array<positive-int, array{text:string,legend?:string,size:integer}>}
     * @ORM\Column(type="json")
     */
    private $structure;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $displayThemesConsensus;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $colorThemeImage;

    /**
     * @var array<array-key, array{formula:string,payload:mixed,shape:string,name:string}>
     * @ORM\Column(type="json")
     */
    private $consensusDefinitions;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $consensusPosition;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $arrowEndType;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $arrowMiddleCircle;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $arrowColor;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $itemsGrouping;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $itemsText;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $themeImageRadius;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $themeBlockFont;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $itemBlockFont;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $itemBlockRadius;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $itemBlockBorder;

    public function getId(): int
    {
        return $this->id;
    }

    public function getColors(): array
    {
        return $this->colors;
    }

    /**
     * @return array<array-key, array{formula:string,payload:mixed,shape:string,name:string}>
     */
    public function getConsensusDefinitions(): array
    {
        return $this->consensusDefinitions;
    }

}
