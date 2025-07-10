<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-08-01
 */

namespace Azimut\Bundle\MediacenterBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="mediacenter_media_declination_presentation")
 *
 * @DynamicInheritanceSubClass(discriminatorValue="presentation")
 */
class MediaDeclinationPresentation extends MediaDeclination
{
    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"detail_media"})
     * @Assert\NotBlank(groups={"embedHtmlRequired"})
     * @Assert\Regex(
     *     pattern="/<iframe[^>]+>[^<]+<\/iframe>/",
     *     message="embed.html.must.contain.iframe"
     * )
     */
    protected $embedHtml;

    public static function getMediaDeclinationType()
    {
        return 'presentation';
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
