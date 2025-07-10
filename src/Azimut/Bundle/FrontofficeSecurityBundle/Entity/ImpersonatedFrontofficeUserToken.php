<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-06-13 15:18:23
 */

namespace Azimut\Bundle\FrontofficeSecurityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\SecurityBundle\Entity\User;

/**
 * @ORM\Entity
 * @ORM\Table(name="frontoffice_security_impersonated_user_token")
 */
class ImpersonatedFrontofficeUserToken
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $token;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $creationDateTime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $usageDateTime;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=46)
     */
    private $ip;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\SecurityBundle\Entity\User")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $loggedUser;

    /**
     * @var FrontofficeUser
     *
     * @ORM\ManyToOne(targetEntity="FrontofficeUser", fetch="EAGER")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $impersonatedUser;

    public function getId()
    {
        return $this->id;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

   public function getCreationDateTime()
   {
        return $this->creationDateTime;
   }

   public function setCreationDateTime($creationDateTime)
   {
        if (null !== $creationDateTime && !$creationDateTime instanceof \DateTime) {
            $creationDateTime = new \DateTime($creationDateTime);
        }
        $this->creationDateTime = $creationDateTime;
        return $this;
   }

   public function getUsageDateTime()
   {
        return $this->usageDateTime;
   }

   public function setUsageDateTime($usageDateTime)
   {
        if (null !== $usageDateTime && !$usageDateTime instanceof \DateTime) {
            $usageDateTime = new \DateTime($usageDateTime);
        }
        $this->usageDateTime = $usageDateTime;
        return $this;
   }

   public function getIp()
   {
        return $this->ip;
   }

   public function setIp($ip)
   {
        $this->ip = $ip;
        return $this;
   }

   public function getLoggedUser()
   {
        return $this->loggedUser;
   }

   public function setLoggedUser(User $loggedUser)
   {
        $this->loggedUser = $loggedUser;
        return $this;
   }

   public function getImpersonatedUser()
   {
        return $this->impersonatedUser;
   }

   public function setImpersonatedUser(FrontofficeUser $impersonatedUser)
   {
        $this->impersonatedUser = $impersonatedUser;
        return $this;
   }
}
