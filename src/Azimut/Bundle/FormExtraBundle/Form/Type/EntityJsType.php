<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-02-17 12:23:47
 */

namespace Azimut\Bundle\FormExtraBundle\Form\Type;

use Symfony\Component\Form\AbstractType;

class EntityJsType extends AbstractType
{
    public function getParent()
    {
        return EntityHiddenType::class;
    }
}
