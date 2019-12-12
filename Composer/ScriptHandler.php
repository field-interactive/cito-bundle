<?php

namespace FieldInteractive\CitoBundle\Composer;

use Symfony\Component\ClassLoader\ClassCollectionLoader;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\PhpExecutableFinder;
use Composer\Script\Event;
use Symfony\Component\Yaml\Yaml;

class ScriptHandler
{
    /**
     * Composer variables are declared static so that an event could update
     * a composer.json and set new options, making them immediately available
     * to forthcoming listeners.
     */
    protected static $options = array(
        'symfony-bin-dir' => 'bin',
        'symfony-public-dir' => 'public',
        'symfony-config-dir' => 'config',
        'symfony-template-dir' => 'templates',
        'cito-pages-dir' => 'pages',
    );

    /**
     * @param Event $event
     */
    public static function installTemplateFiles(Event $event)
    {
        $options = static::getOptions($event);
        $pagesDir = $options['cito-pages-dir'];
        $publicDir = $options['symfony-public-dir'];
        $templateDir = $options['symfony-template-dir'];
        $fs = new Filesystem();

        if (!$fs->exists($pagesDir . '/index.html.twig')) {
            $fs->mirror(__DIR__ . '/../Resources/pages/', $pagesDir . '/');
        }

        $citoBase = false;
        if ($fs->exists($templateDir . '/base.html.twig')) {
            $baseFile = file_get_contents($templateDir . '/base.html.twig');
            $citoBase = strpos($baseFile, '{# cito #}') ? true : false;
        }
        if (!$citoBase) {
            $fs->mirror(__DIR__ . '/../Resources/templates/', $templateDir . '/', null, ['override' => true]);
        }

        if (!$fs->exists($publicDir.'/assets/')) {
            $fs->mirror(__DIR__ . '/../Resources/public/', $publicDir);
            $fs->mkdir($publicDir.'/assets/fonts');
            $fs->mkdir($publicDir.'/assets/images');
        }
    }

    /**
     * @param Event $event
     */
     public static function installConfiguration(Event $event)
     {
         $options = static::getOptions($event);
         $configDir = $options['symfony-config-dir'];
         $pagesDir = $options['cito-pages-dir'];
         $fs = new Filesystem();

         if(!$fs->exists($configDir . '/packages/cito.yaml')) {
             $fs->copy(__DIR__ . '/../Resources/config/packages/cito.yaml', $configDir . '/packages/cito.yaml');
         }
         if(!$fs->exists($configDir . '/routes/z_cito.yaml')) {
             $fs->copy(__DIR__ . '/../Resources/config/routes/z_cito.yaml', $configDir . '/routes/z_cito.yaml');
         }

         if(!$fs->exists($configDir . '/routes/liip_imagine.yaml')) {
             $fs->copy(__DIR__ . '/../Resources/config/routes/imagine.yaml', $configDir . '/routes/liip_imagine.yaml');
         }

         // Add imaginebundle to bundles.php
         $contents = require $configDir . '/bundles.php';
         if (!array_key_exists("Liip\ImagineBundle\LiipImagineBundle", $contents)) {
             $content = file_get_contents($configDir . '/bundles.php');
             $lines = explode("\n", $content);
             array_pop($lines);
             array_pop($lines);
             $lines[] = "\tLiip\ImagineBundle\LiipImagineBundle::class => ['all' => true],";
             $lines[] = "];";
             $lines[] = "";
             $content = implode("\n", $lines);
             file_put_contents($configDir . '/bundles.php', $content);
         }
     }

    /**
     * @param Event $event
     */
    public static function installJavascriptFiles(Event $event)
    {
        $options = static::getOptions($event);
        $publicDir = $options['symfony-public-dir'];
        $fs = new Filesystem();

        $fs->mirror(__DIR__ . '/../Skeleton/', $publicDir.'/../');

        // Additional information
        echo 'You can now do a npm install for the javascript packages.'.PHP_EOL;
        echo 'You can use Webpack to compile sass, javascripts and more.';
    }

    /**
     * @param Event $event
     */
    public static function postCreateProject(Event $event)
    {
        $options = static::getOptions($event);
        $publicDir = $options['symfony-public-dir'];
        $fs = new Filesystem();

        // Override files added by symfony/webpack-encore-bundle
        $fs->copy(__DIR__ . '/../Skeleton/config.json', 'config.json', true);
        $fs->copy(__DIR__ . '/../Skeleton/package.json', 'package.json', true);
        $fs->copy(__DIR__ . '/../Skeleton/postcss.config.js', 'postcss.config.js', true);
        $fs->copy(__DIR__ . '/../Skeleton/webpack.config.js', 'webpack.config.js', true);

        $fs->remove('assets');
    }

    /**
     * Update the Cito files without delete user changes
     *
     * @param Event $event
     */
    public static function updateCito(Event $event)
    {
        $options = static::getOptions($event);
        $configDir = $options['symfony-config-dir'];
        $fs = new Filesystem();

        /*
         * Update config/packages/cito.yaml
         */
        $local = Yaml::parseFile($configDir . '/packages/cito.yaml');
        $current = Yaml::parseFile(__DIR__ . '/../Resources/config/packages/cito.yaml');

        self::updateArray($local, $current);

        $fs->remove($configDir . '/packages/cito.yaml');
        $fs->dumpFile($configDir . '/packages/cito.yaml', Yaml::dump($local, 99));
    }

    protected static function updateArray(array &$local, array $current)
    {
        foreach ($current as $key => $item) {
            if (!array_key_exists($key, $local)) {
                $local[$key] = $item;
            } elseif (is_array($item)) {
                self::updateArray($local[$key], $item);
            }
        }
    }

    protected static function getOptions(Event $event)
    {
        $options = array_merge(static::$options, $event->getComposer()->getPackage()->getExtra());

        $options['process-timeout'] = $event->getComposer()->getConfig()->get('process-timeout');
        $options['vendor-dir'] = $event->getComposer()->getConfig()->get('vendor-dir');

        return $options;
    }

    protected static function getPhp($includeArgs = true)
    {
        $phpFinder = new PhpExecutableFinder();
        if (!$phpPath = $phpFinder->find($includeArgs)) {
            throw new \RuntimeException('The php executable could not be found, add it to your PATH environment variable and try again');
        }

        return $phpPath;
    }

    protected static function getPhpArguments()
    {
        $ini = null;
        $arguments = array();

        $phpFinder = new PhpExecutableFinder();
        if (method_exists($phpFinder, 'findArguments')) {
            $arguments = $phpFinder->findArguments();
        }

        if ($env = getenv('COMPOSER_ORIGINAL_INIS')) {
            $paths = explode(PATH_SEPARATOR, $env);
            $ini = array_shift($paths);
        } else {
            $ini = php_ini_loaded_file();
        }

        if ($ini) {
            $arguments[] = '--php-ini=' . $ini;
        }

        return $arguments;
    }

    /**
     * Returns a relative path to the directory that contains the `console` command.
     *
     * @param Event $event The command event
     * @param string $actionName The name of the action
     *
     * @return string|null The path to the console directory, null if not found
     */
    protected static function getConsoleDir(Event $event, $actionName)
    {
        $options = static::getOptions($event);

        if (static::useNewDirectoryStructure($options)) {
            if (!static::hasDirectory($event, 'symfony-bin-dir', $options['symfony-bin-dir'], $actionName)) {
                return;
            }

            return $options['symfony-bin-dir'];
        }

        if (!static::hasDirectory($event, 'symfony-app-dir', $options['symfony-app-dir'], 'execute command')) {
            return;
        }

        return $options['symfony-app-dir'];
    }

    private static function removeDecoration($string)
    {
        return preg_replace("/\033\[[^m]*m/", '', $string);
    }
}
