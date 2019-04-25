<?php

namespace FieldInteractive\CitoBundle\Service;


use function Sodium\add;
use function Sodium\compare;
use FieldInteractive\CitoBundle\Exception\FileNotFoundException;
use Symfony\Component\Yaml\Yaml;

class RouteResolverService
{
    private $enabled;

    private $path;

    private $fileBaseName;

    public function __construct($translationPath, $translationEnabled)
    {
        $this->path = rtrim($translationPath, '/').'/';
        $this->fileBaseName = 'routes.{locale}.yaml';
        $this->enabled = $translationEnabled;
    }

    public function resolveRealRoute($url, $locale, $addLocale = true)
    {
        if (!$this->enabled) {
            return $url;
        }

        $fileName = str_replace('{locale}', $locale, $this->fileBaseName);
        if (!is_file($this->path.$fileName)) {
            throw new FileNotFoundException('File '.$this->path.$fileName.' not found');
        }

        $route = trim($url, '/');

        if (strpos($route, $locale.'/') === 0) {
            $route = substr($route, 3);
        }

        $yaml = Yaml::parseFile($this->path.$fileName);
        $route = $this->findRouteInArray($route, $yaml);
        if (empty($route)) {
            return $url;
        }

        $length = strlen($route);
        if (strpos($route, 'index') == $length - 5) {
            $route = substr($route, 0, $length - 6);
        }

        if ($addLocale) {
            $route = $locale.'/'.$route;
        }

        return $route;
    }

    public function resolveRouteByFile($path, $locale, $addLocale = true)
    {
        $path = trim(str_replace('.html.twig', '', $path), '/');
        if (!$this->enabled) {
            return $path;
        }

        $fileName = str_replace('{locale}', $locale, $this->fileBaseName);
        if (!is_file($this->path.$fileName)) {
            throw new FileNotFoundException('File '.$this->path.$fileName.' not found');
        }

        $yaml = Yaml::parseFile($this->path.$fileName);

        $explodedRoute = explode('/', $path);

        $route = $yaml;
        foreach ($explodedRoute as $item) {
            if (is_array($route) && array_key_exists($item, $route)) {
                $route = $route[$item];
            } else {
                $route = $path;
                break;
            }
        }

        if (is_array($route) && array_key_exists('index', $route)) {
            $route = $route['index'];
        }

        if ($addLocale) {
            $route = $locale.'/'.$route;
        }

        return $route;
    }

    protected function findRouteInArray(string $route, array $array)
    {
        foreach ($array as $key => $item) {
            if (is_array($item)) {
                $part = $this->findRouteInArray($route, $item);
                if (!empty($part)) {
                    return $key.'/'.$part;
                }
            } elseif (is_string($item) && $item === $route) {
                return $key;
            }
        }
        return null;
    }

}
