<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2014-01-09 11:43:56
 */

namespace Azimut\Bundle\SecurityBundle\AccessRoles;

use Azimut\Bundle\SecurityBundle\Security\BaseAccessRoleService;

class RoleProviders
{
    /**
     * @var BaseAccessRoleService[]
     */
    private $providers;

    public function __construct()
    {
        $this->providers = array();
    }

    public function addProvider($provider, $alias)
    {
        if (false === $this->hasProvider($alias)) {
            $this->providers[$alias] = $provider;
        }
    }

    public function hasProvider($alias)
    {
        return isset($this->providers[$alias]);
    }

    /**
     * @param $alias
     * @return BaseAccessRoleService|null
     */
    public function getProvider($alias)
    {
        if (array_key_exists($alias, $this->providers)) {
            return $this->providers[$alias];
        }

        return null;
    }

    /**
     * @return BaseAccessRoleService[]
     */
    public function getProviders()
    {
        return $this->providers;
    }

    public function getRoles()
    {
        $roles = [];
        //get all providers
        foreach ($this->providers as $provider) {
            //get global roles from each provider without duplications
            foreach ($provider->getRoles() as $role) {
                if (!in_array($role, $roles)) {
                    $roles[] = $role;
                }
            }
        }
        return $roles;
    }

    public function getEntityRoles()
    {
        $roles = [];
        //get all providers
        foreach ($this->providers as $provider) {
            //if there are roles on entities get them as well without duplications
            if (count($provider->getEntities()) != 0) {
                foreach ($provider->getRolesOnEntities() as $rolesOnEntity) {
                    foreach ($rolesOnEntity as $roleOnEntity) {
                        if (!in_array($roleOnEntity, $roles)) {
                            $roles[] = $roleOnEntity;
                        }
                    }
                }
            }
        }
        return $roles;
    }

    public function getAllRoles()
    {
        return array_merge($this->getRoles(), $this->getEntityRoles());
    }

    public function getEntitiesFromProviders()
    {
        $result = array();
        foreach ($this->getProviders() as $provider) {
            if ($provider->getEntities() != null) {
                foreach ($provider->getEntities() as $class) {
                    if (!in_array($class, $result)) {
                        $result[$class] = $class;
                    }
                }
            }
        }

        return $result;
    }

    public function getProviderForClass($class)
    {
        foreach ($this->getProviders() as $provider) {
            if ($provider->supportsClass($class)) {
                return $provider;
            }
        }

        throw new \InvalidArgumentException("No provider found for class ".$class);
    }
}
