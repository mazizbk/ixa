<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-11-06 10:53:22
 */

namespace Azimut\Bundle\FrontofficeBundle\Event\Entity;

use Symfony\Component\EventDispatcher\Event;
use Azimut\Bundle\FrontofficeBundle\Entity\ZoneCmsFileAttachment;

class ZoneCmsFileAttachmentRemoved extends Event
{
    const NAME = 'zone.cms.file.attachment.removed';

    /**
     * @var ZoneCmsFileAttachment
     */
    protected $zoneCmsFileAttachment;

    public function __construct(ZoneCmsFileAttachment $zoneCmsFileAttachment)
    {
        $this->zoneCmsFileAttachment = $zoneCmsFileAttachment;
    }

    /**
     * @return ZoneCmsFileAttachment
     */
    public function getZoneCmsFileAttachment()
    {
        return $this->zoneCmsFileAttachment;
    }
}
