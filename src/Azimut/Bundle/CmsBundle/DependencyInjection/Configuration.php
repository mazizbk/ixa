<?php
/**
 * Created by mikaelp on 1/31/2017 9:59 AM
 */

namespace Azimut\Bundle\CmsBundle\DependencyInjection;


use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root('azimut_cms');

        $rootNode
            ->children()
                ->arrayNode('comment_ratings')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('feeds')
                ->useAttributeAsKey('name', false)
                ->prototype('array')
                    ->children()
                        ->scalarNode('name')->isRequired()->cannotBeEmpty()->end()
                        ->integerNode('zone_id')->isRequired()->min(1)->end()
                        ->arrayNode('feed_types')
                            ->isRequired()
                            ->requiresAtLeastOneElement()
                            ->prototype('enum')->values(['rss', 'atom'])->isRequired()->cannotBeEmpty()->end()
                        ->end()
                        ->scalarNode('base_url')
                            ->validate()
                                ->ifTrue(function($v) {
                                    return !preg_match('/^https?:\/\/.+/isU', $v);
                                })
                                ->thenInvalid('%s is not a valid URL')
                            ->end()
                        ->end()
                        ->scalarNode('title')->end()
                        ->scalarNode('description')->end()
                    ->end()
                ->end()
            ->end()
        ->end()
        ;

        return $treeBuilder;
    }
}
