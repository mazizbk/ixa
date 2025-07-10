<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-06-26 17:23:09
 */

namespace Azimut\Bundle\MediacenterBundle\EventListener\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use Azimut\Bundle\MediacenterBundle\Entity\MediaDeclination;
use Azimut\Bundle\MediacenterBundle\Entity\Folder;

class MediaDeclinationSubscriber implements EventSubscriber
{
    private $uploads_dir;

    public function __construct($uploads_dir)
    {
        $this->uploads_dir = $uploads_dir.'/mediacenter';
    }

    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::preUpdate,
            Events::postPersist,
            Events::postUpdate,
            Events::postRemove,
        ];
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof MediaDeclination) {
            $this->preUpload($entity, $args->getEntityManager());
        }
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof MediaDeclination) {
            $this->preUpload($entity, $args->getEntityManager());
        }
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof MediaDeclination) {
            $this->upload($entity);
        }
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof MediaDeclination) {
            $this->upload($entity);
        }
    }

    public function postRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof MediaDeclination) {
            $this->removeUpload($entity);
        }
    }

    private function preUpload(MediaDeclination $mediaDeclination, EntityManagerInterface $em)
    {
        $uow = $em->getUnitOfWork();
        $file = $mediaDeclination->getFile();

        if (null !== $file) {
            // remove old file if it's an update
            if ($mediaDeclination->getPath()) {
                $this->removeUpload($mediaDeclination);
            }

            if ($file instanceof UploadedFile) {
                $name = $file->getClientOriginalName();
            }
            else {
                $name = $file->getFilename();
            }

            // MacOSX uses normalization form D (NFD) to encode UTF-8, while most other systems use NFC
            if (!\Normalizer::isNormalized($name)) {
                $name = \Normalizer::normalize($name);
            }

            //clean file name
            $name = htmlentities($name, ENT_NOQUOTES, 'utf-8');
            $name = preg_replace('#\&([A-za-z])(?:uml|circ|tilde|acute|grave|cedil|ring)\;#', '\1', $name);
            $name = preg_replace('#\&([A-za-z]{2})(?:lig)\;#', '\1', $name);
            $name = preg_replace('#\&[^;]+\;#', '', $name);
            $name = preg_replace('#\+#', '', $name);

            $name = strtolower($name);
            $extPos = strripos($name, ".");
            if ($extPos) {
                $name = substr($name, 0, $extPos);
            }
            $name = preg_replace('/[\s\.]/', '-', $name);

            $extension = $file->guessExtension();

            if ('mpga' == $extension) {
                $extension = 'mp3';
            }

            // accept mp3 files with wrong mime type
            if ('bin' == $extension && 'mp3' == $file->getClientOriginalExtension()) {
                $extension = 'mp3';
            }

            //check if a file has the same name
            $actualName = $name;
            $i = 1;
            while (file_exists($this->uploads_dir."/$actualName.$extension")) {
                $actualName = (string) $name.$i;
                $i++;
            }

            $mediaDeclination->setPath($actualName.".".$extension);

            $mediaDeclination->setFileExtension($extension);

            $mediaDeclination->generateThumb();
        }
    }

    private function upload(MediaDeclination $mediaDeclination)
    {
        if (null === $mediaDeclination->getFile()) {
            return;
        }

        // if there is an error when moving the file, an exception will
        // be automatically thrown by move(). This will properly prevent
        // the entity from being persisted to the database on error
        $mediaDeclination->getFile()->move($this->uploads_dir, $mediaDeclination->getPath());
    }

    private function removeUpload(MediaDeclination $mediaDeclination)
    {
        $file = $this->uploads_dir.'/'.$mediaDeclination->getPath();


        if (null != $file && file_exists($file) && is_file($file)) {
            unlink($file);
        }
    }
}
