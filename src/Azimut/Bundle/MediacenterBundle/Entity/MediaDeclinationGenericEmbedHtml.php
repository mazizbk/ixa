<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-03-16 17:13:14
 */

namespace Azimut\Bundle\MediacenterBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="mediacenter_media_declination_generic_embed_html")
 *
 * @DynamicInheritanceSubClass(discriminatorValue="generic_embed_html")
 */
class MediaDeclinationGenericEmbedHtml extends MediaDeclination
{
    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"detail_media"})
     * @assert\NotBlank(groups={"embedHtmlRequired"})
     */
    protected $embedHtml;

    public static function getMediaDeclinationType()
    {
        return 'generic_embed_html';
    }

    public function getEmbedHtml()
    {
        return $this->embedHtml;
    }

    public function setEmbedHtml($embedHtml)
    {
        $embedHtml = preg_replace('/width="\d+"/', 'width="100%"', $embedHtml);
        $embedHtml = preg_replace('/height="\d+"/', 'height="100%"', $embedHtml);

        $this->embedHtml = $embedHtml;

        return $this;
    }
}
