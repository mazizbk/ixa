<?php

namespace Azimut\Bundle\MontgolfiereAppBundle\Repository;

use Azimut\Bundle\MontgolfiereAppBundle\Entity\AnalysisVersion;

class AnalysisVersionRepository extends \Doctrine\ORM\EntityRepository
{
    public function getLastVersion(): AnalysisVersion
    {
        return $this->findBy([], ['id' => 'DESC'], 1)[0];
    }
}
