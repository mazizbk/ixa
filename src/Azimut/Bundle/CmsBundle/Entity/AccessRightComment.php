<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-04-18 10:36:42
 */

namespace Azimut\Bundle\CmsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;

use Azimut\Bundle\SecurityBundle\Entity\AccessRight;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;

/**
 * @ORM\Entity
 * @ORM\Table(name="cms_access_right_comment")
 *
 * @DynamicInheritanceSubClass(discriminatorValue="comment")
 */
class AccessRightComment extends AccessRight
{
    /**
     * @var Comment
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\CmsBundle\Entity\Comment", inversedBy="accessRights")
     * @ORM\JoinColumn(name="comment_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $comment;

    /**
     * @return Comment
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param Comment $comment
     * @return $this
     */
    public function setComment(Comment $comment)
    {
        $this->comment = $comment;
        $comment->addAccessRight($this);

        return $this;
    }

    /**
     * @return Comment
     */
    public function getObject()
    {
        return $this->comment;
    }

    /**
     * @param $comment
     * @return mixed
     */
    public function setObject($comment)
    {
        return $this->comment = $comment;
    }

    public static function getObjectClass()
    {
        return Comment::class;
    }

    /**
     * @VirtualProperty
     * @Groups({"list_access_rights"})
     */
    public function getObjectId()
    {
        if (null === $this->getObject()) {
            return null;
        }

        return $this->getObject()->getId();
    }

    /**
     * @VirtualProperty
     * @Groups({"detail_access_right", "list_access_rights"})
     */
    public function getAccessRightType()
    {
        return 'comment';
    }
}
