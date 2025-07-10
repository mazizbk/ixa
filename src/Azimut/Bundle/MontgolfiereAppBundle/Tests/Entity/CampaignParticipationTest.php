<?php

namespace Azimut\Bundle\MontgolfiereAppBundle\Tests\Entity;

use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignParticipation;
use PHPUnit\Framework\TestCase;

class CampaignParticipationTest extends TestCase
{
    public function testRpsAlertProperty()
    {
        $participation = new CampaignParticipation();
        $this->assertFalse($participation->isRpsAlert());
        $participation->setRpsAlert(true);
        $this->assertTrue($participation->isRpsAlert());
    }
}
