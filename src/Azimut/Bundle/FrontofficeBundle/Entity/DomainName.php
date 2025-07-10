<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-25 10:03:45
 */

namespace Azimut\Bundle\FrontofficeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="frontoffice_domain_name")
 * @UniqueEntity(fields="name", message="this.domain.name.already.exists")
 */
class DomainName
{
    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"list_sites","detail_site"})
     * @Assert\Regex(
     *     pattern="/^(?!\-)(?:[a-zA-Z\d\-]{0,62}[a-zA-Z\d]\.){1,126}(?!\d+)[a-zA-Z\d]{1,63}$/",
     *     message="this.is.not.a.valid.domain.name"
     * )
     */
    protected $name;

    /**
     * @var Site
     *
     * @ORM\ManyToOne(targetEntity="Site", inversedBy="secondaryDomainNames"))
     * @ORM\JoinColumn(onDelete="cascade")
     */
    private $siteSecondary;

    /**
     * @var Site
     *
     * @ORM\OneToOne(targetEntity="Site", mappedBy="mainDomainName"))
     */
    private $siteMain;

    public function __construct($name = null)
    {
        $this->setName($name);
    }

    public function getId()
    {
        return $this->id;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Site
     */
    public function getSite()
    {
        return $this->siteMain?:$this->siteSecondary;
    }

    /**
     * @param Site
     * @return $this
     */
    public function setSiteMain(Site $site)
    {
        $this->siteMain = $site;

        return $this;
    }

    /**
     * @param Site
     * @return $this
     */
    public function setSiteSecondary(Site $site)
    {
        $this->siteSecondary = $site;

        return $this;
    }

    public function __toString()
    {
        return $this->getName();
    }
}
