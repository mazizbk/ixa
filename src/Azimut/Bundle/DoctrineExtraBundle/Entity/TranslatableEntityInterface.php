<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-02-03 17:00:04
 */

namespace Azimut\Bundle\DoctrineExtraBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

interface TranslatableEntityInterface
{
    /**
     * @return string
     */
    static function getTranslationClass();

    /**
     * @return EntityTranslationInterface[]|ArrayCollection<EntityTranslationInterface>
     */
    public function getTranslations();
}
