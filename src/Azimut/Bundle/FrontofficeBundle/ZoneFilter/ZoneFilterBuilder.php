<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-06-13 11:52:40
 */

namespace Azimut\Bundle\FrontofficeBundle\ZoneFilter;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;
use Symfony\Component\HttpFoundation\ParameterBag;

use Azimut\Bundle\FrontofficeBundle\Entity\AbstractZoneFilter;
use Azimut\Component\PHPExtra\InterfaceHelper;
use Azimut\Bundle\DoctrineExtraBundle\Entity\TranslatableEntityInterface;
use Azimut\Bundle\FrontofficeBundle\Entity\ZonePermanentFilter;

class ZoneFilterBuilder {

    private $qb;

    private $filter;

    private $entityManager;

    /**
     * @param QueryBuilder $qb
     * @param AbstractZoneFilter $filter
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(QueryBuilder $qb, AbstractZoneFilter $filter, EntityManagerInterface $entityManager)
    {
        $this->qb = $qb;
        $this->filter = $filter;
        $this->entityManager = $entityManager;
    }

    /**
     * Append a zone filter to query builder
     *
     * @param array|ArrayCollection $cmsFileSubClasses
     * @param ParameterBag|null $requestQuery
     *
     * @return QueryBuilder
     */
    public function getFilteredQueryBuilder($cmsFileSubClasses, $requestQuery)
    {
        $orXFilter = $this->qb->expr()->orX();

        foreach ($cmsFileSubClasses as $subClass) {
            $this->appendZoneCmsFileSubclassFilter($subClass, $requestQuery, $orXFilter);
        }

        $this->qb->andWhere($orXFilter);

        return $this->qb;
    }

    /**
     * @param string $subClass
     * @param ParameterBag|null $requestQuery
     * @param Expr\Orx $orXFilter
     */
    private function appendZoneCmsFileSubclassFilter($subClass, $requestQuery, Expr\Orx $orXFilter)
    {
        $filterValue = $this->filter->getQueryParameter($requestQuery);

        // If zone filter is not permanent and value is empty, ignore it
        if (!($this->filter instanceof ZonePermanentFilter)) {
            if (empty($filterValue)) {
                return;
            }
            if (is_array($filterValue) && 1 == count($filterValue) && empty($filterValue[0]) ) {
                return;
            }
        }

        $subClassMetaData = $this->entityManager->getClassMetadata($subClass);
        $cmsFileType = $subClassMetaData->discriminatorValue;

        $subClassMetaData = $this->entityManager->getClassMetadata($subClass);
        $subClassTranslationMetaData = $this->entityManager->getClassMetadata($subClass::getTranslationClass());

        $subClassFields = array_keys($subClassMetaData->fieldMappings);
        $subClassAssociations = array_keys($subClassMetaData->associationMappings);
        $subClassTranslationFields = array_keys($subClassTranslationMetaData->fieldMappings);

        $entityAliasName = null;
        $property = $this->filter->getProperty();

        // Split property name on points to handle subentities
        if (false !== strpos($property, '.')) {
            $propertyHierarchy = explode('.', $property);

            // if entity owns the association
            if (in_array($propertyHierarchy[0], $subClassAssociations)) {
                $associatedSubClass = $subClassMetaData->associationMappings[$propertyHierarchy[0]]['targetEntity'];
                $baseEntityAliasName = $this->getEntityAliasName($propertyHierarchy[0], $subClass, $cmsFileType);

                $entityPath = $baseEntityAliasName.'.'.$propertyHierarchy[0];
                $entityAliasName = $baseEntityAliasName.'_'.implode('_', $propertyHierarchy);

                $this->qb->leftJoin($baseEntityAliasName.'.'.$propertyHierarchy[0], $entityAliasName);

                // If translation owns the property
                // We first check that the property is not owned by entity before checking on the translation (because both can have the same, for instance "id")
                if (!$this->entityClassHasProperty($associatedSubClass, $propertyHierarchy[1])
                        && $this->entityClassTranslationHasProperty($associatedSubClass, $propertyHierarchy[1])) {
                    // Join on translations
                    $this->qb->leftJoin($entityAliasName.'.translations', 't_'.$entityAliasName);

                    // Join on real translation class (because it may be diffent in case of inheritance)
                    $this->qb->leftJoin($associatedSubClass::getTranslationClass(), 'tr_'.$entityAliasName, 'WITH', 't_'.$entityAliasName.'.id = tr_'.$entityAliasName .'.id');

                    $entityAliasName = 'tr_'.$entityAliasName;
                }
            }
        }
        else {
            $entityAliasName = $this->getEntityAliasName($this->filter->getProperty(), $subClass, $cmsFileType);
        }

        if (null != $entityAliasName) {
            $orXFilter->add($this->filter->getQuery($entityAliasName));
            $this->qb->setParameter($this->filter->getQueryParameterName(), $this->filter->getQueryParameter($requestQuery));
        }
    }

    /**
     * Return the alias name of the entity in the DQL query depending wether the entity or its translation
     * owns the property or association
     *
     * @param string $property
     * @param string $subClass
     * @param string $cmsFileType
     *
     * @return string|null
     */
    private function getEntityAliasName($property, $subClass, $cmsFileType)
    {
        // if entity owns the property
        if ($this->entityClassHasProperty($subClass, $property)) {
            return 'c'.$cmsFileType;
        }
        // if entity translation owns the property
        if ($this->entityClassTranslationHasProperty($subClass, $property)) {
            return 'ct'.$cmsFileType;
        }
        return null;
    }


    /**
     * Returns true if entity owns the property (or association)
     *
     * @param string $entityClass
     * @param string $entityClass
     *
     * @return boolean
     */
    private function entityClassHasProperty($entityClass, $property)
    {
        $entityClassMetaData = $this->entityManager->getClassMetadata($entityClass);

        $entityClassFields = array_keys($entityClassMetaData->fieldMappings);
        $entityClassAssociations = array_keys($entityClassMetaData->associationMappings);

        if (in_array($property, $entityClassFields)) {
            return true;
        }

        if (in_array($property, $entityClassAssociations)) {
            return true;
        }

        return false;
    }

    /**
     * Returns true if entity translation owns the property (or association)
     *
     * @param string $entityClass
     * @param string $entityClass
     *
     * @return boolean
     */
    private function entityClassTranslationHasProperty($entityClass, $property)
    {
        if (InterfaceHelper::isClassImplementing($entityClass, TranslatableEntityInterface::class)) {
            $entityClassTranslationMetaData = $this->entityManager->getClassMetadata($entityClass::getTranslationClass());
            $entityClassTranslationFields = array_keys($entityClassTranslationMetaData->fieldMappings);

            return in_array($property, $entityClassTranslationFields);
        }
    }
}
