<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-16 12:01:38
 */

namespace Azimut\Bundle\MediacenterBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Azimut\Bundle\MediacenterBundle\Entity\MediaDeclination;

class FileUploadEvent extends Event
{
    protected $mediaDeclination;
    private $isBlocked;
    private $blockedMessage;

    public function __construct(MediaDeclination $mediaDeclination)
    {
        $this->mediaDeclination = $mediaDeclination;
    }

    public function getMediaDeclination()
    {
        return $this->mediaDeclination;
    }

    public function block($message)
    {
        $this->isBlocked = true;
        $this->blockedMessage = $message;
    }

    public function isBlocked()
    {
        return $this->isBlocked;
    }

    public function getBlockedMessage()
    {
        return $this->blockedMessage;
    }
}
