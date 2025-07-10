<?php


namespace Azimut\Bundle\MontgolfiereAppBundle\Model;


class VirtualItemAnalysis extends ItemAnalysis
{
    /**
     * @var float
     */
    private $workcareAverage = 50;

    public function getWorkcareAverage(): float
    {
        return $this->workcareAverage;
    }

    public function setWorkcareAverage(float $workcareAverage): self
    {
        $this->workcareAverage = $workcareAverage;

        return $this;
    }


}
