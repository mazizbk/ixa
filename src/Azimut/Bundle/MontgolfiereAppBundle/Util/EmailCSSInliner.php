<?php
/**
 * Created by mikaelp on 2018-11-13 2:12 PM
 */

namespace Azimut\Bundle\MontgolfiereAppBundle\Util;


use Assetic\AssetManager;
use Symfony\Component\Templating\EngineInterface;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

class EmailCSSInliner
{
    /**
     * @var EngineInterface
     */
    protected $engine;
    /**
     * @var AssetManager
     */
    protected $assetManager;
    /**
     * @var string
     */
    protected $asseticDir;
    /**
     * @var CssToInlineStyles
     */
    protected $cssToInlineStyles;

    public function __construct(EngineInterface $engine, AssetManager $assetManager, $asseticDir)
    {
        $this->engine = $engine;
        $this->assetManager = $assetManager;
        $this->asseticDir = $asseticDir;
        $this->cssToInlineStyles = new CssToInlineStyles();
    }

    public function render($templateName, array $context = [])
    {
        $html = $this->engine->render($templateName, $context);

        $assetLocation = $this->asseticDir.DIRECTORY_SEPARATOR.$this->assetManager->get('email_css')->getTargetPath();
        $css = file_get_contents($assetLocation);

        return $this->cssToInlineStyles->convert($html, $css);

    }
}
