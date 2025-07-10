<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-07-28
 */

namespace Azimut\Bundle\CmsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;
use JMS\Serializer\Annotation\Groups;
use Azimut\Bundle\CmsBundle\Entity\Traits\CmsFileMainAttachmentTrait;
use Azimut\Bundle\CmsBundle\Entity\Traits\CmsFileSecondaryAttachmentsTrait;
use Azimut\Bundle\CmsBundle\Entity\Traits\CmsFileComplementaryAttachment1Trait;
use Azimut\Bundle\CmsBundle\Entity\Traits\CmsFileComplementaryAttachment2Trait;
use Azimut\Bundle\CmsBundle\Entity\Traits\CmsFileComplementaryAttachment3Trait;
use Azimut\Bundle\CmsBundle\Entity\Traits\CmsFileComplementaryAttachment4Trait;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="Azimut\Bundle\CmsBundle\Entity\Repository\CmsFileRepository")
 * @ORM\Table(name="cms_cmsfile_demo")
 *
 * @DynamicInheritanceSubClass(discriminatorValue="demo")
 */
class CmsFileDemo extends CmsFile
{
    use CmsFileMainAttachmentTrait, CmsFileComplementaryAttachment1Trait, CmsFileComplementaryAttachment2Trait, CmsFileComplementaryAttachment3Trait, CmsFileComplementaryAttachment4Trait;
    use CmsFileSecondaryAttachmentsTrait {
        CmsFileSecondaryAttachmentsTrait::__construct as private __constructCmsFileSecondaryAttachmentsTrait;
    }

    /**
     * @ORM\OneToMany(targetEntity="AccessRightCmsFileDemo", mappedBy="cmsfiledemo")
     */
    protected $accessRights;

    /**
     * @ORM\Column(type="string", length=150)
     * @Groups({"detail_cms_file"})
     */
    protected $myField;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     * @Groups({"detail_cms_file"})
     */
    protected $myRadioField;

    /**
     * @ORM\Column(type="array")
     * @Groups({"detail_cms_file"})
     */
    protected $myMultipleCheckboxesField = [];

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     * @Groups({"detail_cms_file"})
     */
    protected $mySelectField;

    /**
     * @ORM\Column(type="array")
     * @Groups({"detail_cms_file"})
     */
    protected $myMultipleSelectField = [];

    /**
     * @ORM\ManyToOne(targetEntity="CmsFileArticle")
     * @Groups({"detail_cms_file"})
     */
    protected $myEntityRadioField;

    /**
    * @ORM\ManyToMany(targetEntity="CmsFileArticle")
    * @Groups({"detail_cms_file"})
    */
    protected $myEntityMultipleCheckboxesField;

    /**
    * @ORM\ManyToOne(targetEntity="CmsFileArticle")
    * @Groups({"detail_cms_file"})
    */
    protected $myEntitySelectField;

    /**
     * @ORM\ManyToMany(targetEntity="CmsFileArticle")
     * @ORM\JoinTable(name="cms_file_demo_cms_file_article_bis")
     * @Groups({"detail_cms_file"})
     */
    protected $myEntityMultipleSelectField = [];

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"detail_cms_file"})
     */
    protected $myRichTextField;

    public function __construct()
    {
        parent::__construct();
        $this->__constructCmsFileSecondaryAttachmentsTrait();
        $this->myEntityMultipleCheckboxesField = new ArrayCollection();
        $this->myEntityMultipleSelectField = new ArrayCollection();
    }

    public static function getCmsFileType()
    {
        return 'demo';
    }

    public function getName($locale = null)
    {
        return $this->getMyField();
    }

    public function getAbstract($locale = null)
    {
        return html_entity_decode(strip_tags($this->getMyRichTextField()));
    }

    public function getMyField()
    {
        return $this->myField;
    }

    public function setMyField($myField)
    {
        $this->myField = $myField;
        return $this;
    }

    public function getMyRadioField()
    {
        return $this->myRadioField;
    }

    public function setMyRadioField($myRadioField)
    {
        $this->myRadioField = $myRadioField;
        return $this;
    }

    public function getMyMultipleCheckboxesField()
    {
        return $this->myMultipleCheckboxesField;
    }

    public function setMyMultipleCheckboxesField($myMultipleCheckboxesField)
    {
        $this->myMultipleCheckboxesField = $myMultipleCheckboxesField;
        return $this;
    }

    public function getMySelectField()
    {
        return $this->mySelectField;
    }

    public function setMySelectField($mySelectField)
    {
        $this->mySelectField = $mySelectField;
        return $this;
    }

    public function getMyMultipleSelectField()
    {
        return $this->myMultipleSelectField;
    }

    public function setMyMultipleSelectField($myMultipleSelectField)
    {
        $this->myMultipleSelectField = $myMultipleSelectField;
        return $this;
    }

    public function getMyEntityRadioField()
    {
        return $this->myEntityRadioField;
    }

    public function setMyEntityRadioField($myEntityRadioField)
    {
        $this->myEntityRadioField = $myEntityRadioField;
        return $this;
    }

    public function getMyEntityMultipleCheckboxesField()
    {
        return $this->myEntityMultipleCheckboxesField;
    }

    public function setMyEntityMultipleCheckboxesField($myEntityMultipleCheckboxesField)
    {
        $this->myEntityMultipleCheckboxesField = $myEntityMultipleCheckboxesField;
        return $this;
    }

    public function getMyEntitySelectField()
    {
        return $this->myEntitySelectField;
    }

    public function setMyEntitySelectField($myEntitySelectField)
    {
        $this->myEntitySelectField = $myEntitySelectField;
        return $this;
    }

    public function getMyEntityMultipleSelectField()
    {
        return $this->myEntityMultipleSelectField;
    }

    public function setMyEntityMultipleSelectField($myEntityMultipleSelectField)
    {
        $this->myEntityMultipleSelectField = $myEntityMultipleSelectField;
        return $this;
    }

    public function getMyRichTextField()
    {
        return $this->myRichTextField;
    }

    public function setMyRichTextField($myRichTextField)
    {
        $this->myRichTextField = $myRichTextField;
        return $this;
    }
}
