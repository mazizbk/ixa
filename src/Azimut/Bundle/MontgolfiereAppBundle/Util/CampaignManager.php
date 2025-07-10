<?php


namespace Azimut\Bundle\MontgolfiereAppBundle\Util;


class CampaignManager
{
    public static function getProgressColor(float $progress): string
    {
        if($progress == 0) { // 0 or null
            return 'black';
        }

        if($progress >= .9) {
            return 'success';
        }
        if($progress >= .7) {
            return 'warning';
        }
        if($progress >= .5) {
            return 'danger';
        }

        return 'black';
    }
}
