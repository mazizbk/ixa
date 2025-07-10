<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2013-11-27 09:13:56
 */

namespace Azimut\Bundle\SecurityBundle\AccessRights;

use Azimut\Bundle\SecurityBundle\AccessRoles\RoleProviders;
use Azimut\Bundle\SecurityBundle\Entity\AccessRight;
use Azimut\Bundle\SecurityBundle\Security\ObjectAccessRightAware;
use Azimut\Component\PHPExtra\TraitHelper;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Azimut\Bundle\SecurityBundle\Entity\AccessRole;
use Azimut\Bundle\SecurityBundle\Entity\AccessRightRoles;
use Azimut\Bundle\SecurityBundle\Entity\AccessRightClass;
use Azimut\Bundle\SecurityBundle\Entity\AccessRightAppRoles;
use Azimut\Bundle\SecurityBundle\Entity\User;
use Azimut\Bundle\SecurityBundle\Entity\Group;

class AccessRightService
{
    private $manager;
    /**
     * @var RoleProviders
     */
    private $roleProvider;
    private $accessRightRepository;
    private $accessRolesRepository;

    public function __construct(Registry $doctrine, RoleProviders $roleProvider)
    {
        $this->manager = $doctrine->getManager();
        $this->roleProvider = $roleProvider;
        $this->accessRightRepository = $doctrine->getRepository(AccessRightClass::class);
        $this->accessRolesRepository = $doctrine->getRepository(AccessRole::class);
    }

    /**
     * Searching for an access right class with this Class name
     * @param $class string
     */
    public function getAccessRightClassList($class)
    {
        $accessRightList = $this->accessRightRepository->getListClass($class);
        $classParents = $this->getParents($class);
        foreach ($classParents as $key => $class) {
            $accessRightList = array_merge($accessRightList, $this->accessRightRepository->getListClass($class));
        }

        return $accessRightList;
    }

    /**
     * Getting all parent tree class with this Class name
     * @param $class string
     */
    public function getParents($class=null, $plist=array())
    {
        $class = $class ? $class : $this;
        $parent = get_parent_class($class);
        if ($parent) {
            $plist[] = $parent;
            /*Do not use $this. Use 'self' here instead, or you
                * will get an infinite loop. */
            $plist = self::getParents($parent, $plist);
        }

        return $plist;
    }

    /**
     *
     * @param $userOrGroup User or Group
     * @param $role string
     * @param $object null, string, object
     */
    public function addAccessRight($userOrGroup, $role, $object = null)
    {
        if (!((($userOrGroup instanceof User) || ($userOrGroup instanceof Group)) && $this->manager->contains($userOrGroup))) {
            throw new \InvalidArgumentException('Access_Right_Service: User/Group not found OR not an instance of User/Group )');
        }

        $accessRole = $this->findOrCreateAccessRole($role);

        $this->check($role, $object);

        $ar = $this->findAccessRight($userOrGroup->getAccessRights(), $object);

        if ($object == null) {
            return $this->addAccessRightRoles($userOrGroup, $accessRole, $ar);
        }

        if (is_string($object)) {
            if (!class_exists($object)) {
                throw new \InvalidArgumentException('Access_Right_Service: Class doesn\'t exist');
            }
            return $this->addAccessRightClass($userOrGroup, $accessRole, $object, $ar);
        }

        if (!TraitHelper::isClassUsing($object , ObjectAccessRightAware::class)) {
            throw new \InvalidArgumentException('Access_Right_Service: Object doesn\'t support AccessRight');
        }

        return $this->addAccessRightObject($userOrGroup, $accessRole, $object, $ar);
    }


    public function check($role, $entity=null)
    {
        if ($entity == null) {
            $service = $this->roleProvider->getProvider('security');
            if (!in_array($role, $service->getRoles())) {
                throw new \InvalidArgumentException('Access_Right_Service: Global Role Not Found');
            }
        }

        if (is_string($entity) && class_exists($entity)) {
            $this->checkClass($entity);
            if (!$this->checkRole($role, $entity)) {
                throw new \InvalidArgumentException('Access_Right_Service: Role On this Class Not Supported');
            }
        }

        if (!TraitHelper::isClassUsing($entity , ObjectAccessRightAware::class)) {
            $this->checkClassEntity($entity);
            if (!$this->checkRoleEntity($role, $entity)) {
                throw new \InvalidArgumentException('Access_Right_Service: Role On this Object Not Supported');
            }
        }
    }

    public function checkClass($class)
    {
        $services = $this->roleProvider->getProviders();
        foreach ($services as $service) {
            if ($service->getEntities() != null) {
                $entities = $service->getEntities();
                if (in_array($class, $entities)) {
                    return true;
                }
            }
        }
        throw new \InvalidArgumentException('Access_Right_Service: Class doesn\'t support AccessRight');
    }

    public function checkRole($role, $class)
    {
        if (!is_string($class)) {
            $class = get_class($class);
        }
        $services = $this->roleProvider->getProviders();

        foreach ($services as $service) {
            if ($service->getEntities() != null) {
                $rolesOnEntities = $service->getRolesOnEntities();

                if (isset($rolesOnEntities[$class])) {
                    if (in_array($role, $rolesOnEntities[$class])) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function checkClassEntity($entity)
    {
        $services = $this->roleProvider->getProviders();
        foreach ($services as $service) {
            if ($service->getEntities() != null) {
                $entities = $service->getEntities();
                foreach ($entities as $availableEntity) {
                    if ($entity instanceof $availableEntity) {
                        return true;
                    }
                }
            }
        }
        throw new \InvalidArgumentException('Access_Right_Service: Class doesn\'t support AccessRight');
    }

    public function checkRoleEntity($role, $entity)
    {
        $services = $this->roleProvider->getProviders();

        foreach ($services as $service) {
            if ($service->getEntities() != null) {
                $rolesOnEntities = $service->getRolesOnEntities();

                foreach ($rolesOnEntities as $key => $roleOnEntity) {
                    if ($entity instanceof $key) {
                        if (in_array($role, $rolesOnEntities[$key])) {
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }

    /**
     * @param AccessRight[] $accessRights
     * @param mixed         $object
     * @return AccessRight|null
     */
    public function findAccessRight($accessRights, $object = null)
    {
        if ($object == null) {
            foreach ($accessRights as $ar) {
                if ($ar instanceof AccessRightRoles) {
                    return $ar;
                }
            }
        }
        if (is_string($object) && class_exists($object)) {
            foreach ($accessRights as $ar) {
                if ($ar instanceof AccessRightClass && $ar->getClass() == $object) {
                    return $ar;
                }
            }
        }

        if (is_object($object) && TraitHelper::isClassUsing($object, ObjectAccessRightAware::class)) {
            /** @var ObjectAccessRightAware $object */
            $class = $object->getAccessRightType();
            foreach ($accessRights as $ar) {
                if ($ar instanceof $class && $ar->getObject() == $object) {
                    return $ar;
                }
            }
        }

        return null;
    }

    public function addAccessRightRoles(User $user, AccessRole $accessRole, AccessRightRoles $accessRightRoles)
    {
        //  $arRoles = $this->findAccessRight($user->getAccessRights());

        if ($accessRightRoles!= null  && $accessRightRoles->hasRole($accessRole->getRole())) {
            return false;
        }

        if ($accessRightRoles == null) {
            $accessRightRoles = new AccessRightRoles();
            $user->addAccessRight($accessRightRoles);
            $this->manager->persist($accessRightRoles);
        }

        $accessRightRoles->addRole($accessRole);
        $this->manager->flush();

        return true;
    }

    public function addAccessRightClass(User $user, AccessRole $accessRole, $object, AccessRightClass $arClass = null)
    {
        // $arClass = $this->findAccessRight($user->getAccessRights(), $object);

        if ($arClass!= null  && $arClass->hasRole($accessRole->getRole())) {
            return false;
        }

        if ($arClass == null) {
            $arClass = new AccessRightClass();
            $arClass->setClass($object);
            $user->addAccessRight($arClass);
            $this->manager->persist($arClass);
        }

        $arClass->addRole($accessRole);
        $this->manager->flush();

        return true;
    }

    /**
     * @param User                   $user
     * @param AccessRole             $accessRole
     * @param ObjectAccessRightAware $object
     * @param AccessRight            $arObj
     * @return bool
     */
    public function addAccessRightObject(User $user, AccessRole $accessRole, $object, AccessRight $arObj)
    {
        //  $arObj = $this->findAccessRight($user->getAccessRights(), $object);

        if ($arObj != null && $arObj->hasRole($accessRole->getRole())) {
            return false;
        }

        if ($arObj == null) {
            /** @var AccessRight $arObj */
            $arObj = $object->createAccessRight();
            $arObj->setObject($object);
            $user->addAccessRight($arObj);
            $this->manager->persist($arObj);
        }

        $arObj->addRole($accessRole);
        $this->manager->flush();

        return true;
    }

    public function removeAccessRight($userOrGroup, $role, $object)
    {
        if ($userOrGroup instanceof User || $userOrGroup instanceof Group) {
            $accessRole = $this->accessRolesRepository->findOneBy(['role' => $role]);
            if ($accessRole == null) {
                return false;
            }

            $ar = $this->findAccessRight($userOrGroup->getAccessRights(), $object);
            if ($ar != null) {
                $ar->removeRole($accessRole);
                if (count($ar->getRoles()) == 0) {
                    $userOrGroup->removeAccessRight($ar);
                    $this->manager->remove($ar);
                    $this->manager->flush();
                }
            }
        }

        return false;
    }

    /**
     * Search for an AccessRole with role $role if not found create one
     * @param $role string
     * @return AccessRole
     */
    public function findOrCreateAccessRole($role)
    {
        $roleManager = $this->manager->getRepository(AccessRole::class);
        $accessRole = $roleManager->findOneBy(array('role' => $role));
        if (null === $accessRole) {
            $accessRole = new AccessRole();
            $accessRole->setRole($role);

            $this->manager->persist($accessRole);
            $this->manager->flush();
        }

        return $accessRole;
    }

    /**
     * Search for an AccessRole with role $role
     * @param $role string
     * @return AccessRole
     */
    public function getAccessRole($role)
    {
        $roleManager = $this->manager->getRepository(AccessRole::class);
        $accessRole = $roleManager->findOneBy(array('role' => $role));

        return  $accessRole;
    }

    /**
     * @param bool $withHiddenClasses
     * @return array
     * @todo put complete namespace of a class in the return json roles
     */
    public function getAllAccessRolesByApplication($withHiddenClasses = true)
    {
        $returnRoles = [];
        // fetch all roles from provider
        $roles = $this->roleProvider->getAllRoles();
        //check if roles exists in database if not create them
        foreach ($roles as $role) {
            $returnRoles[] = $this->findOrCreateAccessRole($role)->toArray();
        }
        // iterate on roles
        foreach ($returnRoles as $key => $role) {
            // check if it is a APP_ROLE
            if (0 === strpos($role['role'], 'APP_')) {
                //give name to application
                $appName = strtolower((substr($role['role'], 4)));
                // fetch the corresponding roles on app'as classes
                $roleService = $this->roleProvider->getProvider('azimut_'.$appName.'_roles');
                $returnRoles[$key]['name'] = $appName;
                if (null !== $roleService) {
                    $rolesOnEntity = [];
                    //get the roles on entities from service
                    if (sizeof($roleService->getRolesOnEntities()) != 0) {
                        $tempRolesOnEntities = $roleService->getRolesOnEntities();
                        //and show them under the application role they belong
                        /**
                         * @var ObjectAccessRightAware $className
                         * @var  $roles
                         */
                        foreach ($tempRolesOnEntities as $className => $roles) {
                            if ($withHiddenClasses || !$roleService->isClassHidden($className)) {
                                /** @var ObjectAccessRightAware $parentClass */
                                /** @noinspection PhpDynamicAsStaticMethodCallInspection Is meant to be called statically */
                                $parentClass = $className::getParentsClassesSecurityContextObject();
                                /** @noinspection PhpDynamicAsStaticMethodCallInspection Is meant to be called statically */
                                $parentClassAccessRightType = $parentClass?$parentClass::getAccessRightType():'';

                                /** @noinspection PhpDynamicAsStaticMethodCallInspection Is meant to be called statically */
                                $accessRightType = $className::getAccessRightType();

                                $rolesOnEntity[$accessRightType] = [];
                                $rolesOnEntity[$accessRightType]['namespace'] = $className;
                                $rolesOnEntity[$accessRightType]['parentClasses'] = $parentClassAccessRightType;
                                $roleObjs = [];

                                foreach ($roles as $role) {
                                    if (!in_array($role, $roleObjs)) {
                                        $roleObjs[] = $this->getAccessRole($role);
                                    }
                                }
                                $rolesOnEntity[$accessRightType]['roles'] = $roleObjs;
                            }
                        }
                    }
                    $returnRoles[$key]['classes'] = $rolesOnEntity;
                }
            }
        }
        return $returnRoles;
    }

    /**
     * @return AccessRole[]
     */
    public function getApplicationAccessRoles()
    {
        return $this->getPrefixedAccessRoles('APP');
    }

    /**
     * @return AccessRole[]
     */
    public function getGlobalAccessRoles()
    {
        return $this->getPrefixedAccessRoles('GLOBAL');
    }

    /**
     * @return AccessRole[]
     */
    private function getPrefixedAccessRoles($prefix)
    {
        $roles = [];
        $rolesOnApp = [];
        $returnRoles = [];

        foreach ($this->roleProvider->getProviders() as $service) {
            $roles[] = $service->getRoles();
        }
        foreach ($roles as $role) {
            $roleSize = sizeof($role);
            for ($i = 0; $i < $roleSize; $i++) {
                $arr = explode("_", $role[$i]);
                if ($arr[0] == $prefix) {
                    $rolesOnApp[] = $role[$i];
                }
            }
        }

        foreach ($rolesOnApp as $role) {
            $roleApp = $this->findOrCreateAccessRole($role);
            $returnRoles[] = $roleApp;
        }

        return $returnRoles;
    }

    public function checkAccessRightExists(AccessRight $accessRight)
    {
        if ($accessRight->getUser() != null && $this->manager->contains($accessRight->getUser())) {
            $userOrGroup = $accessRight->getUser();
        } elseif ($accessRight->getGroup() != null && $this->manager->contains($accessRight->getGroup())) {
            $userOrGroup = $accessRight->getGroup();
        } else {
            throw new \InvalidArgumentException('Access_Right_Service: User/Group not found OR not an instance of User/Group )');
        }

        if ($userOrGroup != null) {
            if ($userOrGroup->getAccessRights()->isEmpty()) {
                return $accessRight;
            } //if no access right for this user just create one

            if ($accessRight instanceof AccessRightAppRoles) {
                foreach ($userOrGroup->getAccessRights() as $ar) {
                    if ($ar instanceof AccessRightAppRoles) {
                        foreach ($accessRight->getRoles() as $role) {
                            $ar->addRole($role);
                        }

                        return $ar;
                    }
                }

                return $accessRight;
            }

            if ($accessRight instanceof AccessRightRoles) {
            }

            if ($accessRight instanceof AccessRightClass) {
            }

            if ($accessRight instanceof AccessRightAcl) {
            } else {
            }
        }
    }
}
