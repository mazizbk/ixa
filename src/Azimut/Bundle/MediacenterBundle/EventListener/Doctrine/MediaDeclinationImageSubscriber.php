<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-08-19 16:49:39
 */

namespace Azimut\Bundle\MediacenterBundle\EventListener\Doctrine;

use Azimut\Bundle\MediacenterBundle\Entity\MediaDeclinationImage;
use Azimut\Bundle\MediacenterBundle\Entity\MediaImage;
use Azimut\Bundle\MediacenterBundle\Entity\MediaVideo;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Azimut\Component\ImageManager\ImageManager;
use Azimut\Bundle\FormExtraBundle\Model\Geolocation;

class MediaDeclinationImageSubscriber implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return array(
            Events::prePersist,
            Events::preUpdate,
            Events::postPersist,
            Events::postUpdate
        );
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof MediaDeclinationImage) {
            $this->preUploadAddMetaData($entity);
        }
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof MediaDeclinationImage) {
            $this->preUploadAddMetaData($entity);
        }
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof MediaDeclinationImage) {
            $this->postUploadRotateUploadedImage($entity);
        }
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof MediaDeclinationImage) {
            $this->postUploadRotateUploadedImage($entity);
        }
    }

    private function preUploadAddMetaData(MediaDeclinationImage $entity)
    {
        if (null !== $entity->getFile()) {
            if ($imageManager = new ImageManager($entity->getFile())) {

                //original shooting date
                $originalCreateDate = $imageManager->getMetaDataField('DateTimeOriginal');

                // image description
                $description = $imageManager->getMetaDataField('ImageDescription');

                // author
                $author = $imageManager->getMetaDataField('Artist');

                // copyright
                $copyright = $imageManager->getMetaDataField('Copyright');

                // software
                $software = $imageManager->getMetaDataField('Software');

                // Size
                $pixelWidth = $imageManager->getMetaDataField('ImageWidth');
                $pixelHeight = $imageManager->getMetaDataField('ImageLength');

                // resolution
                $resolution = $imageManager->getMetaDataField('XResolution');

                // camera maker
                $deviceMaker = $imageManager->getMetaDataField('Make');

                // camera model
                $deviceModel = $imageManager->getMetaDataField('Model');

                // orientation (from 1 to 8)
                $orientation = $imageManager->getMetaDataField('Orientation');

                // GPS coordinates
                $latitude = $imageManager->getMetaDataField('GPSLatitude');
                $longitude = $imageManager->getMetaDataField('GPSLongitude');
                $geolocation = new Geolocation($latitude, $longitude);

                // set values

                if ($originalCreateDate) {
                    $entity->setDateTimeOriginal($originalCreateDate);
                }

                $media = $entity->getMedia();
                $mediaDescription = $media->getDescription();
                $mediaName = $media->getName();
                if ($description) {
                    $media->setDescription($description);
                }

                if ($author && !$entity->getAuthor()) {
                    $entity->setAuthor($author);
                }

                if ($copyright && ($media instanceof MediaImage || $media instanceof MediaVideo) && !$media->getCopyright()) {
                    $media->setCopyright($copyright);
                }

                if ($software) {
                    $entity->setSoftware($software);
                }

                if ($pixelWidth) {
                    $entity->setPixelWidth($pixelWidth);
                }

                if ($pixelHeight) {
                    $entity->setPixelHeight($pixelHeight);
                }

                if ($resolution) {
                    $entity->setResolution($resolution);
                }

                if ($deviceMaker) {
                    $entity->setDeviceMaker($deviceMaker);
                }

                if ($deviceModel) {
                    $entity->setDeviceModel($deviceModel);
                }

                $entity->setOrientation($orientation);

                if($media instanceof MediaImage) {
                    $media->setGeolocation($geolocation);
                }
            }
        }
    }

    private function postUploadRotateUploadedImage(MediaDeclinationImage $entity)
    {

        /*

        $entity = $args->getEntity();

        if (null === $entity->getAbsolutePath()) {
            return;
        }

        //WARNING : this will crash with image too large for server, WITHOUT ANY ERROR DISPLAYED
        $image = ImageCreateFromJPEG($entity->getAbsolutePath());

        $orientation = $entity->getOrientation();

        //TODO : imageflip is only available after PHP 5.5
        //add function : http://stackoverflow.com/questions/15811421/imageflip-in-php-is-undefined

        //turn image the right way
        if (!empty($orientation)) {

            $rotatedImage = null;

            switch ($orientation) {
                case 2: //horizontal flip
                    $rotatedImage = imageflip($image, IMG_FLIP_HORIZONTAL);
                break;
                case 3: //180° rotate
                    $rotatedImage = imagerotate($image,180,0);
                    break;
                case 4: //vertical flip
                    $rotatedImage = imageflip($image, IMG_FLIP_VERTICAL);
                    break;
                case 5: //vertical flip + 90° rotate right
                    $rotatedImage = imageflip($image, IMG_FLIP_VERTICAL);
                    $rotatedImage = imagerotate($rotatedImage,-90,0);
                    break;
                case 6: //90° rotate right
                    $rotatedImage = imagerotate($image,-90,0);
                    break;
                case 7: //horizontal flip + 90° rotate right
                    $rotatedImage = imageflip($image, IMG_FLIP_HORIZONTAL);
                    $rotatedImage = imagerotate($image,-90,0);
                    break;
                case 8: //90° rotate left
                    $rotatedImage = imagerotate($image,90,0);
                    break;
            }

            if ($rotatedImage) {
                imagejpeg($rotatedImage, $entity->getAbsolutePath());
                imagedestroy($rotatedImage);
            }

            imagedestroy($image);
        }

        //small thumb*/
    }
}
