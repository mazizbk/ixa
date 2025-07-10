<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-01-17 12:21:43
 */

namespace Azimut\Bundle\FrontofficeBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();

        $builder->root('azimut_frontoffice')
            ->children()
                ->arrayNode('search_engine')
                    ->children()
                        ->arrayNode('stop_words')
                            ->prototype('array')
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                        ->scalarNode('max_results')->end()
                        ->arrayNode('entities')
                            ->prototype('array')
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $builder;
    }
}
