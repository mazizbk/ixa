<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2013-07-04
 */

namespace Azimut\Bundle\MediacenterBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class FolderRepository extends EntityRepository
{
    public function findRootFoldersOrderedByName()
    {

        return $this->createQueryBuilder('f')
            ->where('f.parentFolder IS NULL')
            ->andWhere('f.trashed = false')
            ->orderBy('f.name', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findRootFolders()
    {
        return $this->createQueryBuilder('f')
            ->where('f.parentFolder IS NULL')
            ->andWhere('f.trashed = false')
            ->getQuery()
            ->getResult()
        ;
    }

    private function findOneByNameInFolderQueryBuilder($name, $parentFolderId)
    {
        $queryBuilder = $this
            ->createQueryBuilder('f')
            ->where('f.name = :name')
            ->setParameter('name', $name)
        ;

        if (null == $parentFolderId) {
            $queryBuilder->andWhere('f.parentFolder is null');
        } else {
            $queryBuilder
                ->andWhere('f.parentFolder = :parentFolderId')
                ->setParameter('parentFolderId', $parentFolderId)
            ;
        }


        return $queryBuilder;
    }

    public function findOneByNameInFolder($name, $parentFolderId)
    {
        return $this->findOneByNameInFolderExcludingFolder($name, $parentFolderId, null);
    }

    public function findOneByNameInFolderExcludingFolder($name, $parentFolderId, $excludeFolderId)
    {
        $queryBuilder = $this->findOneByNameInFolderQueryBuilder($name, $parentFolderId);

        if (null != $excludeFolderId) {
            $queryBuilder
                ->andWhere('f.id != :excludeFolderId')
                ->setParameter('excludeFolderId', $excludeFolderId)
            ;
        }

        return $queryBuilder
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
        ;
    }

    public function findOneNotTrashedByNameInFolder($name, $parentFolderId)
    {
        $queryBuilder = $this->findOneByNameInFolderQueryBuilder($name, $parentFolderId);

        if (null != $parentFolderId) {
            $queryBuilder
                ->andWhere('f.trashed = false')
            ;
        }

        return $queryBuilder
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
        ;
    }

    public function findNotTrashed()
    {
        return $this->getEntityManager()
            ->createQuery('SELECT f FROM AzimutMediacenterBundle:Folder f WHERE f.trashed = false')
            ->getResult();
    }

    public function findRootTrashed()
    {
        return $this->getEntityManager()
            ->createQuery('SELECT f FROM AzimutMediacenterBundle:Folder f WHERE f.trashed = true AND f.trashedFolderPath IS NOT NULL')
            ->getResult();
    }

    public function findTrashed()
    {
        return $this->getEntityManager()
            ->createQuery('SELECT f FROM AzimutMediacenterBundle:Folder f WHERE f.trashed = true')
            ->getResult();
    }
}
