<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:    2016-04-04 15:39:29
 */

namespace Azimut\Bundle\CmsContactBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;
use Azimut\Bundle\CmsBundle\Entity\CmsFileTranslation;

/**
 * @ORM\Entity
 * @ORM\Table(name="cms_cmsfile_contact_translation")
 *
 * @DynamicInheritanceSubClass(discriminatorValue="contact")
 */
class CmsFileContactTranslation extends CmsFileTranslation
{
}
