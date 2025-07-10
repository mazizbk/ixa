<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-16 12:25:29
 */

namespace Azimut\Bundle\MediacenterBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Azimut\Bundle\MediacenterBundle\Event\AzimutMediacenterEvents;
use Azimut\Bundle\MediacenterBundle\Event\FileUploadEvent;
use Azimut\Bundle\MediacenterBundle\Service\DiskQuotaManager;
use Symfony\Component\Translation\TranslatorInterface;

class FileUploadSubscriber implements EventSubscriberInterface
{
    /**
     * @var DiskQuotaManager
     */
    private $diskQuotaManager;

    /**
     * @var TranslatorInterface
     */
    protected $translator;


    public function __construct(DiskQuotaManager $diskQuotaManager, TranslatorInterface $translator)
    {
        $this->diskQuotaManager = $diskQuotaManager;
        $this->translator = $translator;
    }

    public static function getSubscribedEvents()
    {
        return array(
            AzimutMediacenterEvents::FILE_UPLOAD => 'fileUpload'
        );
    }

    public function fileUpload(FileUploadEvent $event)
    {
        $mediaDeclination = $event->getMediaDeclination();

        if (null === $event->getMediaDeclination()->getFile()) {
            return;
        }

        $mediaDeclinationSize = $mediaDeclination->getSize();

        $folder = $mediaDeclination->getMedia()->getFolder();

        $folderQuota = null;

        if ($folder) {
            $folderQuota = $folder->getQuota();
        }

        //there's a quota on folder
        if (null != $folderQuota) {
            $folderSize = $folder->getSize();

            if ($folderSize + $mediaDeclinationSize > $folderQuota) {
                $event->block($this->translator->trans('cannot.save.file.%file_name%.folder.quota.%folder_quota%.overflowed', array('%file_name%' => $folder->getName(), '%folder_quota%' => $folderQuota)));
            }
        }
        //no quota on folder, use global one
        else {
            $globalQuota = $this->diskQuotaManager->getDiskQuota();
            $diskUsage = $this->diskQuotaManager->getDiskUsage();

            $diskUnit = "Mo";

            if ($diskUsage + $mediaDeclinationSize > $globalQuota) {
                $event->block($this->translator->trans('cannot.save.file.global.quota.%quota%.overflowed', array('%quota%' => $this->diskQuotaManager->getDiskQuota($diskUnit).$diskUnit)));
            }
        }
    }
}
