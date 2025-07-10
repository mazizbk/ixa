<?php
/**
 * Created by mikaelp on 6/1/2017 2:03 PM
 */

namespace Azimut\Component\AssetExtra\VersionStrategy;


use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;

class CapifonyVersionStrategy implements VersionStrategyInterface
{
    /**
     * @var string
     */
    private $projectRoot;

    /**
     * @var string
     */
    private $revisionFilename;

    /**
     * @var string
     */
    private $format;

    /**
     * @var string
     */
    private $version;

    /**
     * @param string $projectRoot
     * @param string $revisionFilename
     * @param string $format
     */
    public function __construct($projectRoot, $revisionFilename, $format)
    {
        $this->projectRoot = $projectRoot;
        $this->revisionFilename = $revisionFilename;
        $this->format = $format;

        if(!is_file($this->projectRoot.DIRECTORY_SEPARATOR.$this->revisionFilename)) {
            $this->version = '1';
        }
    }


    /**
     * {@inheritdoc}
     */
    public function getVersion($path)
    {
        if(!$this->version) {
            $this->version = $this->loadVersion();
        }

        return $this->version;
    }

    /**
     * {@inheritdoc}
     */
    public function applyVersion($path)
    {
        $version = $this->getVersion($path);

        if ('' === $version) {
            return $path;
        }

        $versionized = sprintf($this->format, ltrim($path, '/'), $version);

        if ($path && '/' === $path[0]) {
            return '/'.$versionized;
        }

        return $versionized;
    }

    private function loadVersion()
    {
        return substr(file_get_contents($this->projectRoot.DIRECTORY_SEPARATOR.$this->revisionFilename), 0, 8);
    }
}
