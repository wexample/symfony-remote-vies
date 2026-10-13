<?php

namespace Wexample\SymfonyRemoteVies\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Wexample\SymfonyRemoteVies\Service\ViesClient;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('wexample_symfony_remote_vies');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('url')->defaultValue(ViesClient::DEFAULT_URL)->info('Base URL of the VIES REST API.')->end()
                ->scalarNode('requester')->defaultNull()->info('The VAT number of the company checking, prefix included: VIES then gives each check a consultation number, the proof of it.')->end()
            ->end();

        return $treeBuilder;
    }
}
