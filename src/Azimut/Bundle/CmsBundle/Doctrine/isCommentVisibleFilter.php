<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-04-27 11:40:26
 */

namespace Azimut\Bundle\CmsBundle\Doctrine;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

use Azimut\Bundle\CmsBundle\Entity\Comment;

class isCommentVisibleFilter extends SQLFilter
{
    /***
     * {@inheritdoc}
     */
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        if ($targetEntity->reflClass->name != Comment::class) {
            return '';
        }

        return $targetTableAlias.'.is_visible = true';
    }
}
