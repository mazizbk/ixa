<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-07-11 10:12:37
 */

namespace Azimut\Bundle\ModerationBundle\Service;

use Doctrine\Common\Annotations\Reader;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\File\File;

use Azimut\Bundle\ModerationBundle\Entity\CmsFileBuffer;
use Azimut\Bundle\CmsBundle\Entity\CmsFile;
use Azimut\Bundle\CmsBundle\Entity\CmsFileMediaDeclinationAttachment;
use Azimut\Bundle\ModerationBundle\Annotation\CmsFileConverterProperty;
use Azimut\Bundle\DoctrineExtraBundle\Translation\TranslationProxy;
use Azimut\Bundle\MediacenterBundle\Form\Transformer\FileToMediaTransformer;
use Azimut\Component\PHPExtra\TraitHelper;
use Azimut\Bundle\CmsBundle\Entity\Traits\CmsFileMainAttachmentTrait;
use Azimut\Bundle\MediacenterBundle\Entity\Folder;

class CmsFileBufferConverter
{
    private $annotationReader;

    private $registry;

    private $uploadDir;

    public function __construct(Reader $annotationReader, RegistryInterface $registry, $uploadDir)
    {
        $this->annotationReader = $annotationReader;
        $this->registry = $registry;
        $this->uploadDir = $uploadDir;
    }

    /**
    * Convert a CmsFileBuffer to its equivalent CmsFile entity
    * @return CmsFile
    */
    public function convert(CmsFileBuffer $cmsFileBuffer)
    {
        $em = $this->registry->getManager();
        /** @noinspection PhpUnhandledExceptionInspection */
        $reflection = new \ReflectionClass($cmsFileBuffer);
        $className = $cmsFileBuffer::getTargetCmsFileClass();
        /** @var CmsFile $cmsFile */
        $cmsFile = new $className;

        $cmsFile->setPublishStartDateTime(new \DateTime());

        // configure locale
        $translationProxyLocale = TranslationProxy::getDefaultLocale();
        TranslationProxy::setDefaultLocale($cmsFileBuffer->getLocale());

        // get all properties excluding static ones
        /** @var \ReflectionProperty[] $properties */
        $properties = array_diff(
            $reflection->getProperties(),
            $reflection->getProperties(\ReflectionProperty::IS_STATIC)
        );

        foreach ($properties as $property) {
            // only properties having CmsFileConverterProperty annotation will be copied
            if ($annotation = $this->annotationReader->getPropertyAnnotation($property, CmsFileConverterProperty::class)) {

                $targetPropertyName = $annotation->targetName?:$property->getName();

                $setter = 'set'.ucfirst($targetPropertyName);
                $iser = 'is'.ucfirst($targetPropertyName);
                $haser = 'has'.ucfirst($targetPropertyName);
                if (0 === strpos($targetPropertyName, 'is')) {
                    $iser = $targetPropertyName;
                }
                if (0 === strpos($targetPropertyName, 'has')) {
                    $haser = $targetPropertyName;
                }

                if (method_exists($cmsFile, $setter)) {
                    $cmsFile->$setter($cmsFileBuffer->{$property->getName()});
                } elseif (method_exists($cmsFile, $iser)) {
                    $cmsFile->$iser($cmsFileBuffer->{$property->getName()});
                } elseif (method_exists($cmsFile, $haser)) {
                    $cmsFile->$haser($cmsFileBuffer->{$property->getName()});
                } else {
                    throw new \Exception(sprintf('Class %s has no method %s, %s or %s', $className, $setter, $iser, $haser));
                }
            }
        }

        // add media file
        $transformer = new FileToMediaTransformer($em);

        if (TraitHelper::isClassUsing($cmsFileBuffer::getTargetCmsFileClass(), CmsFileMainAttachmentTrait::class) && null != $cmsFileBuffer->getFilePath()) {
            /** @var CmsFileMainAttachmentTrait $cmsFile */
            $media = $transformer->reverseTransform(new File($this->uploadDir.'/moderation/'.$cmsFileBuffer->getFilePath()));

            $media->setName($cmsFileBuffer->getName());

            $folder = $this->registry->getRepository(Folder::class)->findOneBy(['name' => 'Submitted.library']);

            if (!$folder) {
                throw new \Exception('Unable to find Mediacenter folder to store submitted cmsfile buffer\'s file (it should be a root folder named "Submitted.library")');
            }

            $media->setFolder($folder);

            $em->persist($media);

            // attach media to cmsfile
            $cmsFile->setMainAttachment(new CmsFileMediaDeclinationAttachment($media->getMainDeclination()));
        }


        // restore locale setting
        TranslationProxy::setDefaultLocale($translationProxyLocale);

        $em->persist($cmsFile);

        $cmsFileBuffer->isArchived(true);

        return $cmsFile;
    }
}
