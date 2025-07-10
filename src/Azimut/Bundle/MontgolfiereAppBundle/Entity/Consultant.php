<?php

namespace Azimut\Bundle\MontgolfiereAppBundle\Entity;

use Azimut\Bundle\FrontofficeSecurityBundle\Entity\FrontofficeUser;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;

/**
 * Consultant
 *
 * @ORM\Table(name="montgolfiere_consultant")
 * @ORM\Entity(repositoryClass="Azimut\Bundle\MontgolfiereAppBundle\Repository\ConsultantRepository")
 * @DynamicInheritanceSubClass(discriminatorValue="consultant")
 */
class Consultant extends FrontofficeUser
{
    const ROLE_DEFAULT = 'ROLE_FRONT_CONSULTANT';

    public function __construct()
    {
        $this->roles = [self::ROLE_DEFAULT];
        $this->isEmailConfirmed = true;
        $this->isActive = true;
    }

    /**
     * @var Campaign[] | ArrayCollection<Campaign>
     * @ORM\ManyToMany(targetEntity="Azimut\Bundle\MontgolfiereAppBundle\Entity\Campaign", mappedBy="consultants")
     */
    private $campaigns;

    /**
     * @return Campaign[]|ArrayCollection
     */
    public function getCampaigns()
    {
        return $this->campaigns;
    }


}