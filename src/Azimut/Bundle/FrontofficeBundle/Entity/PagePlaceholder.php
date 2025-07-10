<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2016-01-22 15:23:07
 */

namespace Azimut\Bundle\FrontofficeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class PagePlaceholder extends Page
{
    public function getPageType()
    {
        return 'placeholder';
    }

    public function getMetaTitle($locale = null)
    {
        return null;
    }

    public function getMetaDescription($locale = null)
    {
        return null;
    }
}
