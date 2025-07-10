<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2015-09-22 08:57:39
 */

namespace Azimut\Bundle\SecurityBundle\Controller;

use Azimut\Bundle\SecurityBundle\Security\ObjectAccessRightAware;
use Azimut\Bundle\SecurityBundle\Security\SecurityAwareRepository;
use Doctrine\Common\Persistence\ObjectRepository;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;

/**
 *  @PreAuthorize("isAuthenticated() && isAuthorized('APP_SECURITY')")
 */
class ApiObjectController extends FOSRestController
{

    /**
     * Get action
     * @var string $className of the objects needed
     * @return array
     *
     * @Rest\View(serializerGroups={"security_access_right_obj"})
     * @Rest\Route(requirements={"className"=".+"})
     *
     * @ApiDoc(
     *  section="Security",
     *  resource=true,
     *  description="Security : Get objects by class"
     * )
     */
    public function getObjectsofclassAction($className)
    {
        if (strpos($className, '.json') !== false) {
            $className=substr($className, 0, -5);
        }

        $class = str_replace('_', '\\', $className);
        $class = str_replace('/', '\\', $class);

        /** @var ObjectAccessRightAware[] $objectList */
        $repository = $this->getSecurityAwareRepository($class);
        $objectList = $repository instanceof SecurityAwareRepository ? $repository->findSecurityObjects($className) : $repository->findAll();

        // keep only first-level objects (objects that don't have a parent object of the same class)
        $objectList = array_filter($objectList, function ($object) use ($className) {
            /** @var ObjectAccessRightAware $object */
            if (!is_array($object->getParentsSecurityContextObject())) {
                return true;
            }
            foreach ($object->getParentsSecurityContextObject() as $parent) {
                if ($parent instanceof $className) {
                    return false;
                }
            }
            return true;
        });

        // array_filter forced real indexes whereas we need a simple array
        $objectList = array_values($objectList);

        return array(
            'objects' => $objectList,
        );
    }

    /**
     * Returns a SecurityAwareRepository for a given class, traversing all parent classes until one is found (returns the base repository if none is found)
     * @param string $class
     * @param null   $baseRepository
     * @return ObjectRepository
     */
    private function getSecurityAwareRepository($class, $baseRepository = null)
    {
        $em = $this->getDoctrine()->getManager();

        $repository = $em->getRepository($class);
        if ($repository instanceof SecurityAwareRepository) {
            return $repository;
        }

        $reflClass = $em->getClassMetadata($class)->getReflectionClass();
        if ($reflClass->getParentClass()) {
            return $this->getSecurityAwareRepository($reflClass->getParentClass()->getName(), $repository);
        }

        return $baseRepository?$baseRepository:$repository;
    }

    /**
     * Get action
     * @var integer $id Id of the object which children needed
     * @var string $classname Class of the object which children needed
     * @return array
     *
     * @Rest\View(serializerGroups={"security_access_right_obj"})
     *
     * @ApiDoc(
     *  section="Security",
     *  description="Security : Get children objects of an object"
     * )
     * @QueryParam(
     *  name="id", description="Object id"
     * )
     * @QueryParam(
     *  name="className", description="Object class name"
     * )
     */
    public function getChildrenofobjectAction($id, $className)
    {
        $em = $this->getDoctrine()->getManager();

        $class = str_replace('_', '\\', $className); //if request is send by api_doc
        $class = str_replace('/', '\\', $class); //if request is send by angularjs

        $object = $em->getRepository($class)->find($id);
        if (null === $object) {
            throw new \InvalidArgumentException("Object of class ".$className." and id ".$id." not found");
        }

        return array(
            'objects' => $object->children,
        );
    }
}
