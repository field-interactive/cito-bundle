<?php

namespace FieldInteractive\CitoBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Yaml\Yaml;

class CitoExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        $configuration = $this->getConfiguration($configs, $container);

        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('field_cito.dir.pages', $config['pages']);
        $container->setParameter('field_cito.dir.posts', $config['posts']);
        $container->setParameter('field_cito.routing.user_agent_enabled', $config['user_agent_enabled']);
        $container->setParameter('field_cito.routing.default_user_agent', $config['default_user_agent']);
        $container->setParameter('field_cito.routing.user_agent_routing', $config['user_agent_routing']);
        $container->setParameter('field_cito.translation.translation_enabled', $config['translation_enabled']);
        $container->setParameter('field_cito.translation.translation_support', $config['translation_support']);

        $this->addAnnotatedClassesToCompile(array(
            'FieldInteractive\\CitoBundle\\Controller\\CitoController',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new Configuration();
    }
}
