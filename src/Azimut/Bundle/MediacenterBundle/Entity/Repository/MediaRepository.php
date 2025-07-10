<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2013-07-10
 */

namespace Azimut\Bundle\MediacenterBundle\Entity\Repository;

use Azimut\Bundle\MediacenterBundle\Entity\Media;
use Doctrine\ORM\EntityRepository;
use Azimut\Bundle\MediacenterBundle\Entity\MediaOther;
use Azimut\Bundle\MediacenterBundle\Entity\MediaGenericEmbedHtml;

class MediaRepository extends EntityRepository
{
    public function createInstanceFromString($name)
    {
        $metadata = $this->getClassMetadata();
        $map = $metadata->discriminatorMap;

        if (!isset($map[$name])) {
            throw new \InvalidArgumentException(sprintf('No media of type "%s". Available: %s', $name, implode(', ', array_keys($map))));
        }

        $class = $map[$name];

        return new $class();
    }

    public function createInstanceFromMimeType($mimeType)
    {
        //check if the mime type is well formed
        if (!preg_match('#^[-\w]+/[-\w\.]+$#', $mimeType)) {
            throw new \InvalidArgumentException("Mime type '$mimeType' not valid.");
        }

        $metadata = $this->getClassMetadata();
        /** @var Media[] $discriminatorMap */
        $discriminatorMap = $metadata->discriminatorMap;

        $mediaClass = null;

        foreach ($discriminatorMap as $class) {
            foreach ($class::getMimetypes() as $mimeRegex) {
                if (preg_match($mimeRegex, $mimeType)) {
                    $mediaClass = $class;
                    break;
                }
            }
            if ($mediaClass) {
                break;
            }
        }

        if (null === $mediaClass) {
            return new MediaOther();
        }

        return new $mediaClass();

        /*foreach ($this->em->getMetadataFactory()->getMetadataFor('...\Media')-
>discriminationMap as $key => $class) { $class::getMimetypes(); }
• Si pas de mimetypes --> lève une exception
• Media::getMimeTypes renvoie une liste d'expressions régulières*/
    }

    public function createInstanceFromEmbedHtml($embedHtml)
    {
        $metadata = $this->getClassMetadata();
        /** @var Media[] $discriminatorMap */
        $discriminatorMap = $metadata->discriminatorMap;

        $mediaClass = null;

        foreach ($discriminatorMap as $class) {
            foreach ($class::getEmbedUrls() as $embedHtmlRegex) {
                if (preg_match($embedHtmlRegex, $embedHtml)) {
                    $mediaClass = $class;
                    break;
                }
            }
            if ($mediaClass) {
                break;
            }
        }

        if (null === $mediaClass) {
            return new MediaGenericEmbedHtml();
        }

        return new $mediaClass();
    }

    /**
     * @param Media $class
     * @return string[]
     */
    public function getMimeTypesFromClass($class)
    {
        return $class::getMimeTypes();
    }

    public function getAvailableTypes()
    {
        $types = array();

        /**
         * @var string $type
         * @var Media $class
         */
        foreach ($this->getClassMetadata()->discriminatorMap as $type => $class) {
            array_push($types, array(
                'id' => $type,
                'cssIcon' => $class::getCssIcon(),
            ));
        }

        return $types;
    }

    public function findMediasByFolderId($folderId)
    {
        return $this->getEntityManager()
        ->createQuery(
            'SELECT m FROM AzimutMediacenterBundle:Media m WHERE m.folder = :folderId'
        )
        ->setParameter('folderId', $folderId)
        ->getResult();
    }

    /*
    // if name has to be translated
    public function findOneByName($name,$locale = null)
    {
        $result = $this->getEntityManager()
        ->createQuery(
            'SELECT m FROM AzimutMediacenterBundle:Media m LEFT JOIN m.translations mt WHERE mt.name = :name AND mt.locale = :locale'
        )
        ->setParameter('name', $name)
        ->setParameter('locale', $locale)
        ->setMaxResults(1)
        ->getResult();

        if(count($result) == 0) return null;

        return $result[0];
    }*/

    private function findOneByNameInFolderQueryBuilder($name, $folderId)
    {
        $queryBuilder = $this
            ->createQueryBuilder('m')
            ->where('m.name = :name')
            ->setParameter('name', $name)
        ;

        if (null == $folderId) {
            $queryBuilder->andWhere('m.folder is null');
        } else {
            $queryBuilder
                ->andWhere('m.folder = :folderId')
                ->setParameter('folderId', $folderId)
            ;
        }


        return $queryBuilder;
    }

    public function findOneByNameInFolder($name, $folderId)
    {
        return $this->findOneByNameInFolderExcludingMedia($name, $folderId, null);
    }

    public function findOneByNameInFolderExcludingMedia($name, $folderId, $excludeMediaId)
    {
        $queryBuilder = $this->findOneByNameInFolderQueryBuilder($name, $folderId);

        if (null != $excludeMediaId) {
            $queryBuilder
                ->andWhere('m.id != :excludeMediaId')
                ->setParameter('excludeMediaId', $excludeMediaId)
            ;
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        return $queryBuilder
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult()
        ;
    }

    public function findNotTrashed()
    {
        return $this->getEntityManager()
            ->createQuery('SELECT m FROM AzimutMediacenterBundle:Media m WHERE m.trashed = false')
            ->getResult();
    }

    public function findRootTrashed()
    {
        return $this->getEntityManager()
            ->createQuery('SELECT m FROM AzimutMediacenterBundle:Media m WHERE m.trashed = true AND m.trashedFolderPath IS NOT NULL')
            ->getResult();
    }

    public function findTrashed()
    {
        return $this->getEntityManager()
            ->createQuery('SELECT m FROM AzimutMediacenterBundle:Media m WHERE m.trashed = true')
            ->getResult();
    }

    public function getMediaCount()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return $this->getEntityManager()
            ->createQuery('SELECT COUNT(m.id) from AzimutMediacenterBundle:Media m')
            ->getSingleScalarResult()
        ;
    }
}
