<?php


namespace Azimut\Bundle\MontgolfiereAppBundle\Model;


use Azimut\Bundle\MontgolfiereAppBundle\Entity\AnalysisVersion;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignParticipationAnswer;
use Azimut\Bundle\MontgolfiereAppBundle\Util\CampaignAnalyser;
use JMS\Serializer\Annotation as Serializer;

abstract class Analysis
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $participations = 0;

    /**
     * @var int
     */
    private $absSum = 0;

    /**
     * @var int
     */
    private $allSum = 0;

    /**
     * @var int
     */
    private $workcareSum = 0;

    /**
     * @var HouseSettings|null
     */
    private $houseSettings;

    /**
     * @var int
     */
    private $negativeValueParticipations = 0;

    /**
     * @var int
     */
    private $positiveValueParticipations = 0;

    /**
     * @var int[]
     */
    private $cutsDistribution;

    /**
     * @var bool
     * @Serializer\Exclude()
     */
    private $locked = false;

    /**
     * @var AnalysisVersion
     * @Serializer\Exclude()
     */
    private $analysisVersion;

    public function __construct()
    {
        $this->cutsDistribution = array_fill(0, count(CampaignAnalyser::getTrendsCuts()), 0);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->preventLockedEdit();
        $this->name = $name;

        return $this;
    }

    public function getParticipations(): int
    {
        return $this->participations;
    }

    public function getAbsSum(): int
    {
        return $this->absSum;
    }

    public function getAllSum(): int
    {
        return $this->allSum;
    }


    public function getWorkcareSum(): int
    {
        return $this->workcareSum;
    }

    public function addAnswer(CampaignParticipationAnswer $answer): self
    {
        $this->preventLockedEdit();
        if($answer->getValue() === null) {
            return $this;
        }
        $this->absSum+= 10 - abs($answer->getValue());
        $this->allSum+= $answer->getValue();
        $this->workcareSum+= abs($answer->getValue());
        $this->participations++;
        if ($answer->getValue() < 0){
            $this->negativeValueParticipations++;
        }else{
            $this->positiveValueParticipations++;
        }
        $this->cutsDistribution[CampaignAnalyser::getTrend($answer->getValue()+10, 20)]++;

        return $this;
    }

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("absAverage")
     * @Serializer\Type("float")
     */
    public function getAbsAverage(): float
    {
        if($this->participations === 0) {
            return 0;
        }

        return $this->absSum / $this->participations;
    }

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("allAverage")
     * @Serializer\Type("float")
     */
    public function getAllAverage(): float
    {
        if($this->participations === 0) {
            return 0;
        }

        return $this->allSum / $this->participations;
    }

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("workcareAverage")
     * @Serializer\Type("float")
     */
    public function getWorkcareAverage(): float
    {
        if($this->participations === 0) {
            return 50;
        }

        $positiveOrNegative = $this->getAllAverage()<0 ? -1 : 1;

        return (($this->workcareSum / $this->participations * $positiveOrNegative) + 10) * 5;
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

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("consensus")
     * @Serializer\Type("array<bool>")
     */
    public function getConsensus(): array
    {
        $result = [];
        foreach ($this->analysisVersion->getConsensusDefinitions() as $definition) {
            $hasConsensus = false;
            switch($definition['formula']) {
                case 'same-side':
                    if ($this->getAbsAverage() >= 8) {
                        $hasConsensus = true;
                    }
                    elseif ($this->getAllAverage() < 0 && $this->negativeValueParticipations >= $this->participations * $definition['payload'] / 100){
                        $hasConsensus = true;
                    }
                    elseif ($this->getAllAverage() >= 0 && $this->positiveValueParticipations >= $this->participations * $definition['payload'] / 100){
                        $hasConsensus = true;
                    }
                    break;
            }
            $result[] = $hasConsensus;
        }

        return $result;
    }

    final protected function preventLockedEdit(): void
    {
        if($this->locked) {
            throw new \RuntimeException('Analysis is locked.');
        }
    }

    final public function lock(): self
    {
        $this->locked = true;

        return $this;
    }

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("trend")
     * @Serializer\Type("int")
     * @return int
     */
    public function getTrend(): int
    {
        return CampaignAnalyser::getTrend($this->getWorkcareAverage());
    }

    public function getCutsDistribution(): array
    {
        return $this->cutsDistribution;
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
