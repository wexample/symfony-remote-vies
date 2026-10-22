<?php

namespace Wexample\SymfonyRemoteVies\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Wexample\SymfonyHelpers\DependencyInjection\AbstractWexampleSymfonyExtension;

class WexampleSymfonyRemoteViesExtension extends AbstractWexampleSymfonyExtension
{
    public function load(
        array $configs,
        ContainerBuilder $container
    ): void {
        $config = $this->processConfiguration(new Configuration(), $configs);
        $container->setParameter('wexample_symfony_remote_vies.url', $config['url']);
        $container->setParameter('wexample_symfony_remote_vies.requester', $config['requester']);

        $this->loadConfig(
            __DIR__,
            $container
        );
    }
}
