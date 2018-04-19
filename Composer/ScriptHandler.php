<?php
/**
 * Created by PhpStorm.
 * User: timkompernass
 * Date: 26.03.2018
 * Time: 15:25
 */

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
        'symfony-template-dir' => 'template',
        'cito-pages-dir' => 'pages',
        'cito-themes-dir' => 'themes',
    );

    /**
     * Updated the required files.
     *
     * @param Event $event
     */
    public static function installRequiredFiles(Event $event)
    {
        $options = static::getOptions($event);
        $configDir = $options['symfony-config-dir'];
        $pagesDir = $options['cito-pages-dir'];
        $publicDir = $options['symfony-public-dir'];
        $templateDir = $options['symfony-template-dir'];
        $fs = new Filesystem();

        if(!file_exists($configDir . '/packages/cito.yaml')) {
            $fs->copy(__DIR__ . '/../Resources/config/packages/cito.yaml', $configDir . '/packages/cito.yaml');
        }

        if(!file_exists($configDir . '/routes/z_cito.yaml')) {
            $fs->copy(__DIR__ . '/../Resources/config/routes/z_cito.yaml', $configDir . '/routes/z_cito.yaml');
        }

        if(!file_exists($pagesDir . '/index.html.twig')) {
            $fs->copy(__DIR__ . '/../Resources/pages/index.html.twig', $pagesDir . '/index.html.twig');
        }

        // Add public files
        if (!file_exists($publicDir . '/.htaccess')) {
            $fs->copy(__DIR__ . '/../Resources/public/.htaccess', $publicDir . '/.htaccess', true);
            $fs->copy(__DIR__ . '/../Resources/public/assets/image/.gitkeep', $publicDir . '/assets/image/.gitkeep');
            $fs->copy(__DIR__ . '/../Resources/public/assets/js/default.js', $publicDir . '/assets/js/default.js');
            $fs->copy(__DIR__ . '/../Resources/public/assets/js/observer.js', $publicDir . '/assets/js/observer.js');
            $fs->copy(__DIR__ . '/../Resources/public/assets/sass/default.sass', $publicDir . '/assets/sass/default.sass');
            $fs->copy(__DIR__ . '/../Resources/public/assets/sass/base.sass', $publicDir . '/assets/sass/base.sass');
            $fs->copy(__DIR__ . '/../Resources/public/assets/sass/grid.sass', $publicDir . '/assets/sass/grid.sass');
        }

        // Add Base.html.twig
        $citoBase = false;
        if (file_exists($templateDir . '/base.html.twig')) {
            $baseFile = file_get_contents($templateDir . '/base.html.twig');
            if (strpos($baseFile, '{{# cito #}}')) {
                $citoBase = true;
            }
        }
        if (!$citoBase) {
            $fs->copy(__DIR__ . '/../Resources/templates/base.html.twig', $templateDir . '/base.html.twig');
        }
    }

    /**
     * update the configuration files
     * @param Event $event
     */
    public static function installConfiguration(Event $event)
    {
        $options = static::getOptions($event);
        $configDir = $options['symfony-config-dir'];
        $pagesDir = $options['cito-pages-dir'];
        $fs = new Filesystem();

        // Add twig config
        if (file_exists($configDir . '/packages/twig.yaml')) {
            $twigYaml = Yaml::parseFile($configDir . '/packages/twig.yaml');
            $pagesDir = (strpos($pagesDir, 'kernel.project_dir')) ? $pagesDir : '%kernel.project_dir%/' . $pagesDir;
            if (!empty($twigYaml)) {
                if (is_array($twigYaml['twig']['paths'])) {
                    if (!in_array($pagesDir, $twigYaml['twig']['paths'])) {
                        $twigYaml['twig']['paths'][] = $pagesDir;
                    }
                }

                $yaml = Yaml::dump($twigYaml);
                $fs->remove($configDir . '/packages/twig.yaml');
                $fs->dumpFile($configDir . '/packages/twig.yaml', $yaml);
            } elseif (empty($twigYaml)) {
                $fs->copy(__DIR__ . '/../Resources/config/packages/twig.yaml', $configDir . '/packages/twig.yaml', true);
            }
        } else {
            $fs->copy(__DIR__ . '/../Resources/config/packages/twig.yaml', $configDir . '/packages/twig.yaml', true);
        }

        // Add Framwork
        $fs->copy(__DIR__ . '/../Resources/config/packages/framework.yaml', $configDir . '/packages/framework.yaml', true);

        // Add imagine config
        if (file_exists($configDir . '/packages/imagine.yaml')) {
            $imagineYaml = Yaml::parseFile($configDir . '/packages/imagine.yaml');
            if (!empty($imagineYaml) && is_array($imagineYaml['liip_imagine']['filter_sets']) && !in_array('picture_macro', $imagineYaml['liip_imagine']['filter_sets'])) {
                $picture_macro = Yaml::parseFile(__DIR__ . '/../Resources/config/packages/imagine.yaml')['liip_imagine']['filter_sets'];
                $imagineYaml['liip_imagine']['filter_sets']['picture_macro'] = $picture_macro['picture_macro'];
                $yaml = Yaml::dump($imagineYaml, 9);
                $fs->remove($configDir . '/packages/imagine.yaml');
                $fs->dumpFile($configDir . '/packages/imagine.yaml', $yaml);
            } elseif (empty($imagineYaml)) {
                $fs->copy(__DIR__ . '/../Resources/config/packages/imagine.yaml', $configDir . '/packages/imagine.yaml', true);
            }
        } else {
            $fs->copy(__DIR__ . '/../Resources/config/packages/imagine.yaml', $configDir . '/packages/imagine.yaml', true);
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

    public static function installTheme(Event $event)
    {
        $options = static::getOptions($event);
        $configDir = $options['symfony-config-dir'];
        $themesDir = $options['cito-themes-dir'];
        $fs = new Filesystem();

        if (file_exists($configDir . '/packages/twig.yaml')) {
            $twigYaml = Yaml::parseFile($configDir . '/packages/twig.yaml');
            $themesDir = (strpos($themesDir, 'kernel.project_dir')) ? $themesDir : '%kernel.project_dir%/' . $themesDir;
            if (!empty($twigYaml)) {
                if (is_array($twigYaml['twig']['paths'])) {
                    if (!in_array($themesDir, $twigYaml['twig']['paths'])) {
                        $twigYaml['twig']['paths'][] = $themesDir;
                    }
                }

                if (is_array($twigYaml['twig']['globals'])) {
                    if (!in_array('theme', $twigYaml['twig']['globals'])) {
                        $twigYaml['twig']['globals']['theme'] = '%theme%';
                    }
                    if (!in_array('theme_path', $twigYaml['twig']['globals'])) {
                        $twigYaml['twig']['globals']['theme_path'] = '%kernel.project_dir%/themes/%theme%';
                    }
                }

                $yaml = Yaml::dump($twigYaml);
                $fs->remove($configDir . '/packages/twig.yaml');
                $fs->dumpFile($configDir . '/packages/twig.yaml', $yaml);
            } elseif (empty($twigYaml)) {
                $fs->copy(__DIR__ . '/../Resources/config/packages/twig.yaml', $configDir . '/packages/twig.yaml', true);
            }
        } else {
            $fs->copy(__DIR__ . '/../Resources/config/packages/twig.yaml', $configDir . '/packages/twig.yaml', true);
        }
    }

    /**
     * update the javascript files
     *
     * @param Event $event
     */
    public static function installJavascriptFiles(Event $event)
    {
        $fs = new Filesystem();

        $fs->copy(__DIR__ . '/../Skeleton/gulpfile.js', 'gulpfile.js', false);
        $fs->copy(__DIR__ . '/../Skeleton/package.json', 'package.json', false);
        $fs->copy(__DIR__ . '/../Skeleton/config.json', 'config.json', false);

        // Additional information
        echo 'You can now do a yarn install for the javascript packages.';
        echo 'You can use Gulp to compile sass, javascripts and more. (See gulpfile.js for more information)';
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
