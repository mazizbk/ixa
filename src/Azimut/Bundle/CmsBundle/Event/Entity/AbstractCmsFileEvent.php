<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-11-05 18:11:09
 */

namespace Azimut\Bundle\CmsBundle\Event\Entity;

use Symfony\Component\EventDispatcher\Event;
use Azimut\Bundle\CmsBundle\Entity\CmsFile;

class AbstractCmsFileEvent extends Event
{
    /**
     * @var CmsFile
     */
    protected $cmsFile;

    public function __construct(CmsFile $cmsFile)
    {
        $this->cmsFile = $cmsFile;
    }

    /**
     * Get CmsFile
     * @return CmsFile
     */
    public function getCmsFile()
    {
        return $this->cmsFile;
    }
}
