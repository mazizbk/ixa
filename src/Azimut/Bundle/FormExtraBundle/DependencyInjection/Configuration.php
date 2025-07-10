<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-03-17 17:32:00
 */

namespace Azimut\Bundle\FormExtraBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();

        $builder->root('azimut_form_extra')
            ->children()
                ->arrayNode('tinymce')
                    ->children()
                        ->scalarNode('script_url')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('content_css_url')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('templates_url')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $builder;
    }
}
