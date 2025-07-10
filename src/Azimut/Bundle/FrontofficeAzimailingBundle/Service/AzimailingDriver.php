<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-10-23 16:22:25
 */

namespace Azimut\Bundle\FrontofficeAzimailingBundle\Service;

class AzimailingDriver
{
    /**
     * @var int
     */
    private $accountId;

    /**
     * @var int
     */
    private $groupId;

    /**
     * @var string
     */
    private $shortname;

    public function __construct($accountId, $groupId, $shortname)
    {
        $this->accountId = $accountId;
        $this->groupId = $groupId;
        $this->shortname = $shortname;
    }

    /**
     * Get newletter archives list as HTML content from Azimailing
     *
     * @return string
     */
    public function getArchives()
    {
        $url = sprintf('https://extrazimut.net/azimailing/diffusion/httprequest-liste-newsletter.asp?id_compte=%s&shortName=%s', $this->accountId, $this->shortname);

        return file_get_contents($url);
    }
}
