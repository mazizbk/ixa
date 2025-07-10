<?php

namespace Azimut\Bundle\MontgolfiereAppBundle\Form\Type;

use Azimut\Bundle\MontgolfiereAppBundle\Entity\AnalysisVersion;

trait TinyMCETrait
{

    /**
     * @return array
     */
    protected function getTinymceConfig(AnalysisVersion $analysisVersion): array
    {
        $textColorMap = []; // textcolor_map is not associative
        foreach ($analysisVersion->getColors() as $i => $color) {
            $textColorMap[] = substr($color, 1);
            $textColorMap[] = 'Workcare '.($i + 1);
        }

        return [
            'paste_as_text' => true,
            'toolbar1' => 'bold | undo redo | cut copy paste | removeformat | forecolor | fontsizeselect',
            'toolbar2' => '',
            'plugins' => ['paste textcolor'],
            'textcolor_map' => $textColorMap,
            'fontsize_formats' => '0.75em 1em 1.25em 1.5em 1.75em 2em 3em',
        ];
    }
}
