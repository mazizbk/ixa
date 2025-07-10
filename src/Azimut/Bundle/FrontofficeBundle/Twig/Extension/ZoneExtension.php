<?php
/**
 * Created by PhpStorm.
 * User: stephane
 * Date: 11/10/2017
 * Time: 17:45
 */

namespace Azimut\Bundle\FrontofficeBundle\Twig\Extension;

use Azimut\Bundle\FrontofficeBundle\Entity\Page;
use Azimut\Bundle\FrontofficeBundle\Entity\PageContent;
use Azimut\Bundle\FrontofficeBundle\Entity\Zone;
use Azimut\Bundle\CmsBundle\Entity\CmsFile;
use Azimut\Bundle\FrontofficeBundle\Service\ZoneRenderer;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ZoneExtension extends \Twig_Extension
{

    /**
     * @var RegistryInterface
     */
    private $registry;
    /**
     * @var ZoneRenderer
     */
    private $zoneRenderer;

    /**
     * ZoneExtension constructor.
     * @param RegistryInterface $registry
     * @param ZoneRenderer      $zoneRenderer
     */
    public function __construct(RegistryInterface $registry, ZoneRenderer $zoneRenderer)
    {
        $this->registry = $registry;
        $this->zoneRenderer = $zoneRenderer;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return "zoneExtension";
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('zoneId', array($this, 'getZoneId')),
            new \Twig_SimpleFunction('renderZone', array($this, 'renderZone'), ['is_safe' => ['html'],]),
            new \Twig_SimpleFunction('renderZoneForm', array($this, 'renderZoneForm'), ['is_safe' => ['html'],]),
            new \Twig_SimpleFunction('renderZoneCmsFileBufferForm', array($this, 'renderZoneCmsFileBufferForm'), ['is_safe' => ['html'],]),
            new \Twig_SimpleFunction('neighboursCmsFileInZone', [$this, 'getNeighboursCmsFileInZone']),
        );
    }

    /**
     * Returns zone id from page and zone name
     * @param Page $page
     * @param string $zoneName
     * @return int
     */
    public function getZoneId(Page $page, $zoneName) {
        $zone = $this->registry->getManager()->getRepository(Zone::class)
            ->findOneByNameAndPage($page->getId(), $zoneName)
        ;
        if (!$zone) {
            throw new \InvalidArgumentException(sprintf('Zone named "%s" not found in page "%s"', $zoneName, $page->getName()));
        }
        return $zone->getId();
    }

    public function renderZone(Page $page, $zoneName, $pagePath, array $zoneOptions = [])
    {
        return $this->zoneRenderer->renderZone($page, $zoneName, $pagePath, $zoneOptions);
    }

    public function renderZoneForm(Page $page, $zoneName)
    {
        return $this->zoneRenderer->renderZoneForm($page, $zoneName)->getContent();
    }

    public function renderZoneCmsFileBufferForm(Page $page, $zoneName)
    {
        return $this->zoneRenderer->renderZoneCmsFileBufferForm($page, $zoneName)->getContent();
    }

    /**
     * Get cms file adjacent siblings in a zone (previous and next cms files)
     *
     * @param PageContent $page
     * @param string $zoneName
     * @param CmsFile $currentCmsFile
     * @param string $locale
     * @return array
     */
    public function getNeighboursCmsFileInZone(PageContent $page, $zoneName, CmsFile $currentCmsFile, $locale) {
        $em = $this->registry->getManager();

        $zone = $em->getRepository(Zone::class)
            ->findOneByNameAndPage($page->getId(), $zoneName)
        ;
        if (!$zone) {
            throw new \InvalidArgumentException(sprintf('Zone named "%s" not found in page "%s"', $zoneName, $page->getName()));
        }

        $query = $em->getRepository(CmsFile::class)
            ->getQueryPublishedByZoneId(
                $zone->getId(),
                null,
                $locale,
                $zone->getZoneDefinition()->getAcceptedAttachmentClasses(),
                $zone->getFilters(),
                null,
                $zone->getPermanentFilters()
            )
        ;

        $cmsFiles = $query->getResult();

        $nextCmsFile = null;
        $previousCmsFile = null;

        foreach ($cmsFiles as $key => $cmsFile) {
            if ($currentCmsFile === $cmsFile) {
                $nextCmsFile = isset($cmsFiles[$key + 1]) ? $cmsFiles[$key+1 ]  : null;
                $previousCmsFile = isset($cmsFiles[$key - 1]) ? $cmsFiles[$key - 1] : null;
            }
        }

        return [
            'previous' => $previousCmsFile,
            'next' => $nextCmsFile,
        ];
    }
}
