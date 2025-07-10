<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:    2016-10-03 15:46:12
 */

namespace Azimut\Bundle\CmsBundle\EventListener\Doctrine;

use Azimut\Bundle\CmsBundle\Entity\CmsFile;
use Azimut\Bundle\CmsBundle\Entity\CmsFileTranslation;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use Azimut\Bundle\CmsBundle\Services\MediaDeclinationTagParser;
use Doctrine\Common\Collections\ArrayCollection;
use Azimut\Component\PHPExtra\StringHelper;

class CmsFileSubscriber implements EventSubscriber
{
    /**
     * @var MediaDeclinationTagParser
     */
    private $mediaDeclinationTagParser;

    public function __construct(MediaDeclinationTagParser $mediaDeclinationTagParser)
    {
        $this->mediaDeclinationTagParser = $mediaDeclinationTagParser;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::onFlush,
            Events::prePersist,
            Events::preUpdate,
        ];
    }

    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof CmsFile) {
                $this->updateEmbeddedMediaDeclinations($entity, $em, $uow);
            }
            if ($entity instanceof CmsFileTranslation) {
                $this->updateEmbeddedMediaDeclinations($entity->getTranslatable(), $em, $uow);
            }
        }

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof CmsFile) {
                $this->updateEmbeddedMediaDeclinations($entity, $em, $uow);
            }
            if ($entity instanceof CmsFileTranslation) {
                $this->updateEmbeddedMediaDeclinations($entity->getTranslatable(), $em, $uow);
            }
        }
    }

    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $entity = $eventArgs->getEntity();

        if ($entity instanceof CmsFile) {
            $this->autoUpdateCmsFileSlug($entity, $em);
        }
        if ($entity instanceof CmsFileTranslation) {
            $this->autoUpdateCmsFileTranslationSlug($entity, $em);
        }
    }

    public function preUpdate(LifecycleEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $entity = $eventArgs->getEntity();

        if ($entity instanceof CmsFile) {
            $this->autoUpdateCmsFileSlug($entity, $em);
        }
        if ($entity instanceof CmsFileTranslation) {
            $this->autoUpdateCmsFileTranslationSlug($entity, $em);
        }
    }

    private function autoUpdateCmsFileSlug(CmsFile $cmsFile, EntityManagerInterface $em)
    {
        if ($cmsFile->isSlugTranslated()) {
            $cmsFile->setSlug(null);
            return;
        }

        $cmsFile->setSlug(StringHelper::slugify($cmsFile->getName()));

        $this->uniquifySlugs($cmsFile, $cmsFile, $em);
    }

    private function autoUpdateCmsFileTranslationSlug(CmsFileTranslation $cmsFileTranslation, EntityManagerInterface $em)
    {
        if (!$cmsFileTranslation->getCmsFile()->isSlugTranslated()) {
            $cmsFileTranslation->setSlug(null);
            return;
        }
        $cmsFileTranslation->setSlug(StringHelper::slugify($cmsFileTranslation->getTranslatable()->getName($cmsFileTranslation->getLocale())));

        $this->uniquifySlugs($cmsFileTranslation, $cmsFileTranslation->getCmsFile(), $em);
    }

    private function uniquifySlugs($entity, CmsFile $cmsFile, EntityManagerInterface $em)
    {
        $repository = $em->getRepository(CmsFile::class);
        $slug = $entity->getSlug();

        $i = 0;
        $newSlug = $slug;

        if ($cmsFile->getId()) {
            while (count($repository->findBySlugExcludingCmsFile($newSlug, $cmsFile)) > 0) {
                $i++;
                $newSlug = $slug.'-'.$i;
            }
        } else {
            while (count($repository->findBySlug($newSlug)) > 0) {
                $i++;
                $newSlug = $slug.'-'.$i;
            }
        }

        if ($newSlug !== $slug) {
            $entity->setSlug($newSlug);
        }
    }

    /**
     * Find cmsFile text fields that may contain media declination tags
     * Then find media declinations from tags
     * And store media declinations references into cms file embeddedMediaDeclination collection
    */
    private function updateEmbeddedMediaDeclinations(CmsFile $cmsFile, EntityManagerInterface $em, UnitOfWork $uow)
    {
        $cmsFileClassMetaData = $em->getClassMetadata(get_class($cmsFile));
        $cmsFileTranslationClassMetaData = $em->getClassMetadata($cmsFile->getTranslationClass());

        // Empty embedded media declination on cms file
        $cmsFile->getEmbeddedMediaDeclinations()->clear();

        // Find all text fields in cms file
        foreach ($cmsFileClassMetaData->fieldMappings as $fieldMapping) {
            if ('text' == $fieldMapping['type']) {
                // Find media declination in text
                $mediaDeclinations = $this->mediaDeclinationTagParser->extractMediaDeclinations(call_user_func_array([$cmsFile, 'get'.ucfirst($fieldMapping['fieldName'])], []));

                // Add found media declinations in cmsFile embedded media declinations
                foreach ($mediaDeclinations as $mediaDeclination) {
                    $cmsFile->addEmbeddedMediaDeclination($mediaDeclination);
                }
            }
        }

        // Find all text fields in cms file translations
        foreach ($cmsFileTranslationClassMetaData->fieldMappings as $fieldMapping) {
            if ('text' == $fieldMapping['type']) {
                foreach ($cmsFile->getTranslations() as $cmsFileTranslation) {
                    // Find media declination in text
                    $mediaDeclinations = $this->mediaDeclinationTagParser->extractMediaDeclinations(call_user_func_array([$cmsFileTranslation, 'get'.ucfirst($fieldMapping['fieldName'])], []));

                    // Add found media declinations in cmsFile embedded media declinations
                    foreach ($mediaDeclinations as $mediaDeclination) {
                        $cmsFile->addEmbeddedMediaDeclination($mediaDeclination);
                    }
                }
            }
        }

        // Tell unit of work to recompile changesets
        $classMetadata = $em->getMetadataFactory()->getMetadataFor('AzimutCmsBundle:CmsFile');
        $uow->computeChangeSet($classMetadata, $cmsFile);
    }
}
