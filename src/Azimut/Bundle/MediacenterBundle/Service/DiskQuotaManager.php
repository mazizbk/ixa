<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-16 15:16:14
 */

namespace Azimut\Bundle\MediacenterBundle\Service;

use Azimut\Bundle\MediacenterBundle\Entity\MediaDeclination;
use Symfony\Bridge\Doctrine\RegistryInterface;

class DiskQuotaManager
{
    private $registry;
    private $diskQuota;

    public function __construct($diskQuota, RegistryInterface $registry)
    {
        $this->registry = $registry;

        if (is_int($diskQuota)) {
            $this->diskQuota = $diskQuota;
        } else {
            $diskQuota = strtolower($diskQuota);

            $multiplier = 1;
            if (strpos($diskQuota, 'ko')) {
                $multiplier = 1000;
            } elseif (strpos($diskQuota, 'mo')) {
                $multiplier = 1000 * 1000;
            } elseif (strpos($diskQuota, 'go')) {
                $multiplier = 1000 * 1000 * 1000;
            } elseif (strpos($diskQuota, 'to')) {
                $multiplier = 1000 * 1000 * 1000 * 1000;
            }

            $diskQuota = substr($diskQuota, 0, strlen($diskQuota)-2);

            $this->diskQuota = $diskQuota * $multiplier;
        }
    }

    public function getDiskQuota($unit = null)
    {
        switch (strtolower($unit)) {
            case 'ko': return round($this->diskQuota / 1000, 2);
            case 'mo': return round($this->diskQuota / 1000 / 1000, 2);
            case 'go': return round($this->diskQuota / 1000 / 1000 / 1000, 2);
            case 'to': return round($this->diskQuota / 1000 / 1000 / 1000 / 1000, 2);
        }

        return $this->diskQuota;
    }

    public function getDiskUsage($unit = null)
    {
        $diskUsage = $this->registry
            ->getRepository(MediaDeclination::class)
            ->getDiskUsage();

        switch (strtolower($unit)) {
            case 'ko': return round($diskUsage / 1000, 2);
            case 'mo': return round($diskUsage / 1000 / 1000, 2);
            case 'go': return round($diskUsage / 1000 / 1000 / 1000, 2);
            case 'to': return round($diskUsage / 1000 / 1000 / 1000 / 1000, 2);
        }

        return $diskUsage;
    }
}
