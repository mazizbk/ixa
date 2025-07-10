<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-02-03 17:09:11
 */

namespace Azimut\Bundle\DoctrineExtraBundle\Entity;

interface EntityTranslationInterface
{
    public function getTranslatable();

    public function setTranslatable($translatable);

    public function getLocale();

    public function setLocale($locale);
}
