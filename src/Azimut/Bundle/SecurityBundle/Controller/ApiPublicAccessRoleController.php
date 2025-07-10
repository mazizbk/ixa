<?php
/**
 * Created by mikaelp on 2/26/2016 10:43 AM
 */

namespace Azimut\Bundle\SecurityBundle\Controller;

use Azimut\Bundle\SecurityBundle\Security\ObjectAccessRightAware;
use Azimut\Component\PHPExtra\TraitHelper;
use Doctrine\Common\Persistence\Mapping\AbstractClassMetadataFactory;
use Doctrine\ORM\Mapping\ClassMetadata;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\FOSRestController;

/**
 * @method setLocale
 * @method setTranslatable
 */
class ApiPublicAccessRoleController extends FOSRestController
{
    public function getRolesHierarchyAction()
    {
        $hierarchy = [];

        $roleProviderChain = $this->get('azimut_security.role_provider_chain');
        $providers = $roleProviderChain->getProviders();
        foreach ($providers as $provider) {
            $entities = $provider->getEntities();
            foreach ($entities as $entity) {
                if (TraitHelper::isClassUsing($entity, ObjectAccessRightAware::class)) {
                    /** @var ObjectAccessRightAware|string $entity */
                    /** @noinspection PhpDynamicAsStaticMethodCallInspection */
                    $parents = $entity::getParentsClassesSecurityContextObject();
                    if (// Is top-level class
                        is_null($parents) || // No parents
                        (is_array($parents)  // Array of parents
                            && (count($parents) == 0 ||  // But empty array (no parents)
                                (count($parents) == 1 && $parents[0] == $entity) // Only one parent: itself
                            )
                        ) ||
                        $parents == $entity // $parents is a string, and itself
                    ) {
                        $hierarchy[$entity] = self::getClassSecurityChildren($entity);
                    }
                }
            }
        }

        return $hierarchy;
    }

    private static function getClassSecurityChildren($className)
    {
        $finalChildren = [];
        if (TraitHelper::isClassUsing($className, ObjectAccessRightAware::class)) {
            /** @var ObjectAccessRightAware|string $className */
            /** @noinspection PhpDynamicAsStaticMethodCallInspection */
            $children = $className::getChildrenClassesSecurityContextObject();
            if (is_null($children)) {
                return $finalChildren;
            }
            if (!is_array($children)) {
                $children = [$children];
            }
            foreach ($children as $child) {
                if ($child == $className) {
                    $finalChildren[$child] = [];
                    continue;
                }
                $finalChildren[$child] = self::getClassSecurityChildren($child);
            }
        }

        return $finalChildren;
    }

    /**
     * @Get("/classesparent")
     * @return array
     * @throws \Doctrine\Common\Persistence\Mapping\MappingException
     * @throws \Exception
     */
    public function getClassesParentAction()
    {
        $result = [];
        $roleProviderChain = $this->get('azimut_security.role_provider_chain');
        /** @var AbstractClassMetadataFactory $metadataFactory */
        $metadataFactory = $this->getDoctrine()->getManager()->getMetadataFactory();
        $providers = $roleProviderChain->getProviders();
        foreach ($providers as $provider) {
            $entities = $provider->getEntities();
            foreach ($entities as $entity) {
                /** @var ClassMetadata $metadata */
                $metadata = $metadataFactory->getMetadataFor($entity);
                foreach ($metadata->discriminatorMap as $class) {
                    $result[$class] = $entity;
                }
            }
        }

        return $result;
    }

    /**
     * @Get("/classessecuritytype")
     * @return array
     * @throws \Doctrine\Common\Persistence\Mapping\MappingException
     * @throws \Exception
     */
    public function getClassesSecurityTypeAction()
    {
        $result = [];
        $roleProviderChain = $this->get('azimut_security.role_provider_chain');
        $providers = $roleProviderChain->getProviders();
        foreach ($providers as $provider) {
            $entities = $provider->getEntities();
            foreach ($entities as $entity) {
                $reflClass = new \ReflectionClass($entity);
                if ($reflClass->hasMethod('getAccessRightType') && $reflClass->hasMethod('getAccessRightType')) {
                    $securityType = $reflClass->getMethod('getAccessRightType')->invoke(null);
                    $result[$entity] = $securityType;
                }
            }
        }

        return $result;
    }
}
