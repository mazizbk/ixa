<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-07-01 11:25:54
 */

namespace Azimut\Bundle\FrontofficeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="Azimut\Bundle\FrontofficeBundle\Entity\Repository\RedirectionRepository")
 * @ORM\Table(name="frontoffice_redirection")
 * @UniqueEntity(
 *     fields={"address", "page"},
 *     repositoryMethod="findInSiteByAddressAndPageExcludingPage",
 *     errorPath="address",
 *     message="this.redirection.already.exists.in.site"
 * )
 */
class Redirection
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"list_redirections","detail_redirection"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Groups({"list_redirections","detail_redirection","detail_page"})
     */
    private $address;

    /**
     * @var Page
     *
     * @ORM\ManyToOne(targetEntity="Page", inversedBy="redirections")
     * @Groups({"list_redirections","detail_redirection"})
     * @ORM\JoinColumn(onDelete="cascade")
     */
    private $page;

    public function __construct($address = null)
    {
        $this->address = $address;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function setPage(Page $page)
    {
        $this->page = $page;
        $page->addRedirection($this);
        return $this;
    }

    public function getPage()
    {
        return $this->page;
    }

    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    public function getAddress()
    {
        return $this->address;
    }
}
