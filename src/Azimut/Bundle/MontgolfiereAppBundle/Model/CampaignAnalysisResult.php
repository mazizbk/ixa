<?php


namespace Azimut\Bundle\MontgolfiereAppBundle\Model;


use Azimut\Bundle\MontgolfiereAppBundle\Entity\AnalysisVersion;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\Theme;
use JMS\Serializer\Annotation as Serializer;

class CampaignAnalysisResult
{
    /**
     * @var string
     * @Serializer\Type("string")
     */
    private $title;
    /**
     * @var \DateTimeImmutable|null
     * @Serializer\Type("DateTimeImmutable")
     */
    private $date;

    /**
     * @var int
     */
    private $participants = 0;

    /**
     * @var int|null
     */
    private $expectedParticipants;

    /**
     * @var ThemeAnalysis[]
     */
    private $themesAnalysis = [];

    /**
     * @var ItemAnalysis[]
     */
    private $itemsAnalysis = [];

    /**
     * @var string
     * @Serializer\Type("string")
     */
    private $fileName;

    /**
     * @var AnalysisVersion
     */
    private $analysisVersion;

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(?\DateTimeImmutable $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getParticipants(): int
    {
        return $this->participants;
    }

    public function setParticipants(int $participants): self
    {
        $this->participants = $participants;

        return $this;
    }

    public function getExpectedParticipants(): ?int
    {
        return $this->expectedParticipants;
    }

    public function setExpectedParticipants(?int $expectedParticipants): self
    {
        $this->expectedParticipants = $expectedParticipants;

        return $this;
    }

    /**
     * @return ThemeAnalysis[]
     */
    public function getThemesAnalysis(): array
    {
        return $this->themesAnalysis;
    }

    public function getThemeAnalysis(Theme $theme): ThemeAnalysis
    {
        foreach ($this->themesAnalysis as $themeAnalysis) {
            if($themeAnalysis->getTheme() === $theme) {
                return $themeAnalysis;
            }
        }
        throw new \LogicException('Theme '.$theme->getId().' has not been analyzed');
    }

    /**
     * @param ThemeAnalysis[] $themesAnalysis
     * @return CampaignAnalysisResult
     */
    public function setThemesAnalysis(array $themesAnalysis): self
    {
        $this->themesAnalysis = $themesAnalysis;

        return $this;
    }

    /**
     * @return ItemAnalysis[]
     */
    public function getItemsAnalysis(): array
    {
        return $this->itemsAnalysis;
    }

    /**
     * @param ItemAnalysis[] $itemsAnalysis
     * @return CampaignAnalysisResult
     */
    public function setItemsAnalysis(array $itemsAnalysis): self
    {
        $this->itemsAnalysis = $itemsAnalysis;

        return $this;
    }

    /**
     * @return ThemeAnalysis[][]
     */
    public function getWordTableThemes(): array
    {
        $result = [];
        foreach ($this->themesAnalysis as $themeAnalysis) {
            if(!$themeAnalysis->getWordSettings() || $themeAnalysis->getWordSettings()->getRow() === null || $themeAnalysis->getWordSettings()->getColumn() === null) {
                continue;
            }
            if(!array_key_exists($themeAnalysis->getWordSettings()->getRow(), $result)) {
                $result[$themeAnalysis->getWordSettings()->getRow()] = [];
            }
            if(array_key_exists($themeAnalysis->getWordSettings()->getColumn(), $result[$themeAnalysis->getWordSettings()->getRow()]) && $result[$themeAnalysis->getWordSettings()->getRow()][$themeAnalysis->getWordSettings()->getColumn()] !== null) {
                throw new \LogicException(sprintf('A theme already exists in position %s:%s', $themeAnalysis->getWordSettings()->getRow(), $themeAnalysis->getWordSettings()->getColumn()));
            }

            $result[$themeAnalysis->getWordSettings()->getRow()][$themeAnalysis->getWordSettings()->getColumn()] = $themeAnalysis;

            // Ensure that we don't have gaps in our array
            self::array_pad($result[$themeAnalysis->getWordSettings()->getRow()], max(array_keys($result[$themeAnalysis->getWordSettings()->getRow()])), null);
        }

        // Ensure that we don't have gaps in our array
        self::array_pad($result, max(array_keys($result))+1, null);

        return $result;
    }

    // PHP's array_pad overwrites the existing keys, this one does not
    private static function array_pad(array &$array, int $count, $value): void
    {
        $newArray = array_fill(0, $count, $value);
        $array = $array + $newArray;
        ksort($array);
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
