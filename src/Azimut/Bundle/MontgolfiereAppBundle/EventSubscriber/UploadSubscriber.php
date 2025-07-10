<?php
/**
 * Created by mikaelp on 25-Jul-18 10:39 AM
 */

namespace Azimut\Bundle\MontgolfiereAppBundle\EventSubscriber;


use Azimut\Bundle\MontgolfiereAppBundle\Traits\UploadableEntity;
use Azimut\Component\PHPExtra\TraitHelper;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadSubscriber implements EventSubscriber
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $targetDir;

    /**
     * @var string
     */
    private $uploadsDir;

    public function __construct(Filesystem $filesystem, $uploadsDir, $targetDir)
    {
        $this->filesystem = $filesystem;
        $this->uploadsDir = $uploadsDir;
        $this->targetDir = $targetDir;
    }

    /**
     * @return string
     */
    public function getTargetDir()
    {
        return $this->targetDir;
    }

    /**
     * @return string
     */
    public function getUploadsDir()
    {
        return $this->uploadsDir;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::preUpdate,
            Events::postRemove,
        ];
    }

    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getObject();
        if(!TraitHelper::isClassUsing(get_class($entity), UploadableEntity::class)) {
            return false;
        }
        /** @var UploadableEntity $entity */
        if(!$entity->getUploadedFile() instanceof UploadedFile) {
            return false;
        }
        $newName = $this->upload($entity->getUploadedFile());
        $entity->setFilename($newName);
        $entity->setOriginalName($entity->getUploadedFile()->getClientOriginalName());

        return true;
    }

    public function preUpdate(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getObject();
        if(!TraitHelper::isClassUsing(get_class($entity), UploadableEntity::class)) {
            return false;
        }
        /** @var UploadableEntity $entity */
        if(!$entity->getUploadedFile() instanceof UploadedFile) {
            return false;
        }
        if($entity->getFilename()) {
            $this->deleteFile($entity->getFilename());
        }

        $newName = $this->upload($entity->getUploadedFile());
        $entity->setFilename($newName);
        $entity->setOriginalName($entity->getUploadedFile()->getClientOriginalName());

        return true;
    }

    public function postRemove(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getObject();
        if(!TraitHelper::isClassUsing(get_class($entity), UploadableEntity::class)) {
            return false;
        }

        /** @var UploadableEntity $entity */
        if(!$entity->getFilename()) {
            return false;
        }

        $this->deleteFile($entity->getFilename());

        return true;
    }

    private function deleteFile($filename)
    {
        if(!$filename || strlen(trim($filename)) == 0) {
            return;
        }

        $this->filesystem->remove($this->uploadsDir.DIRECTORY_SEPARATOR.$this->targetDir.DIRECTORY_SEPARATOR.$filename);
    }

    protected function upload(UploadedFile $file)
    {
        $newName = $this->generateUniqueName($file->guessExtension());
        $file->move($this->uploadsDir.DIRECTORY_SEPARATOR.$this->targetDir, $newName);

        return $newName;
    }

    private function generateUniqueName($extension)
    {
        do {
            $fileName = md5(uniqid());
            if($extension) {
                $fileName.= '.'.$extension;
            }
            $completePath = $this->uploadsDir.DIRECTORY_SEPARATOR.$this->targetDir.DIRECTORY_SEPARATOR.$fileName;
        }
        while($this->filesystem->exists($completePath));

        return $fileName;
    }

}
