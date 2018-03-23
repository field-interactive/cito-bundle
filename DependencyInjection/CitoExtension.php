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

        $dirPages = '%kernel.project_dir%/pages/';
        if (isset($configs[0]['pages'])) {
            $dirPages = $configs[0]['pages'];
        }

        $container->setParameter('field_cito.dir.pages', $dirPages);

        $this->addAnnotatedClassesToCompile(array(
            'FieldInteractive\\Cito\\Controller\\CitoController',
        ));
    }
}
