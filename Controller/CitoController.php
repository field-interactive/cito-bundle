<?php

namespace FieldInteractive\CitoBundle\Controller;

use FieldInteractive\CitoBundle\Form\ContactType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CitoController extends Controller
{
    private $pagesPath;

    public function __construct($pagesPath)
    {
        $this->pagesPath = $pagesPath;
    }

    /**
     * @Route("/{url}", name="field_cito_z", requirements={"url": "((?!_wdt|_profiler|_error).+)?"})
     */
    public function zAction(Request $request, $url)
    {
        $url = rtrim($url, '/');

        $translation = $this->getParameter('field_cito.translation.translation_enabled');
        if ($translation) {
            $url = substr($url, 3);
        }

        if ($this->getParameter('field_cito.routing.user_agent_enabled') === true && !$this->isUARouted($url)) {
            $browserData = $this->getBrowserData($_SERVER['HTTP_USER_AGENT']);

            $routingData = $this->getParameter('field_cito.routing.user_agent_routing');

            $selectedRoute = $this->getParameter('field_cito.routing.default_user_agent');
            foreach ($routingData as $route => $browsers) {
                if (strpos($browsers, strtolower($browserData['name'])) !== false) {
                    $exploded = explode(',', $browsers);

                    foreach ($exploded as $browser) {
                        preg_match('/([\d]+)/', $browser, $matches);
                        $ver = isset($matches[0]) ? $matches[0] : "?";

                        if($ver !== "?") {
                            if (strpos($browser, '>') !== false) {
                                if ($browserData['version'] > $ver) {
                                    $selectedRoute = $route;
                                    break;
                                }
                            } elseif (strpos($browser, '<') !== false) {
                                if ($browserData['version'] < $ver) {
                                    $selectedRoute = $route;
                                    break;
                                }
                            } else {
                                if ($browserData['version'] === $ver) {
                                    $selectedRoute = $route;
                                    break;
                                }
                            }
                        } else {
                            $selectedRoute = $route;
                            break;
                        }
                    }
                }
            }

            if (is_file($this->pagesPath.$selectedRoute . "/" . $url.'.html.twig')) {
                return $this->render($selectedRoute . "/" . $url.'.html.twig');
            } elseif (is_file($this->pagesPath.$selectedRoute . ($url !== "" ? "/" : "") . $url.'/index.html.twig')) {
                return $this->render($selectedRoute . "/" . $url.'/index.html.twig');
            } else {
                $errMsg = $selectedRoute . "/" . $url.' not found! Searched for '.$this->pagesPath.$selectedRoute . "/" . $url.'.html.twig and '.$this->pagesPath.$selectedRoute . ($url !== "" ? "/" : "") . $url.'/index.html.twig!';
                throw $this->createNotFoundException($errMsg);
            }
        }

        if (is_file($this->pagesPath.$url.'.html.twig')) {
            return $this->render($url.'.html.twig');
        } elseif (is_file($this->pagesPath.$url.'/index.html.twig')) {
            return $this->render($url.'/index.html.twig');
        }

        if (is_file($this->pagesPath.$url.'.html.twig')) {
            return $this->render($url.'.html.twig');
        } elseif (is_file($this->pagesPath.$url.'/index.html.twig')) {
            return $this->render($url.'/index.html.twig');
        }

        $errMsg = $url.' not found! Searched for '.$this->pagesPath.$url.'.html.twig and '.$this->pagesPath.$url.'/index.html.twig!';
        throw $this->createNotFoundException($errMsg);
    }

    /**
     * Gets the locale out of the url if it's in
     * 
     * return string|false
     */
    protected function getLocaleFromUrl(string $url)
    {
        $locale = trim(substr($url, 0, 3), '/');
        if (strlen($locale) > 2 || strlen($locale) < 2) {
            return false;
        }

        return $locale;
    }
    
    /**
     * Checks if UserAgent is Routed
     */
    protected function isUARouted(string $url)
    {
        $exploded = explode('/', $url);
        $uaRoute = $exploded[0];

        $routingData = $this->getParameter('field_cito.routing.user_agent_routing');
        foreach ($routingData as $route => $browsers) {
            if ($route === $uaRoute) {
                return true;
            }
        }
        return false;
    }

    /**
     * Gets the browser data
     */
    protected function getBrowserData($userAgent)
    {
        $matches = [];

        if (strpos($userAgent, 'Opera') !== false) {
            $browserName = 'Opera';
            preg_match_all('/Version\/([\d]+)/', $_SERVER['HTTP_USER_AGENT'], $matches);
        } elseif (strpos($userAgent, 'OPR/') !== false) {
            $browserName = 'Opera';
            preg_match_all('/OPR\/([\d]+)/', $_SERVER['HTTP_USER_AGENT'], $matches);
        } elseif (strpos($userAgent, 'Edge') !== false) {
            $browserName = 'Edge';
            preg_match_all('/Edge\/([\d]+)/', $_SERVER['HTTP_USER_AGENT'], $matches);
        } elseif (strpos($userAgent, 'Chrome') !== false) {
            $browserName = 'Chrome';
            preg_match_all('/Chrome\/([\d]+)/', $_SERVER['HTTP_USER_AGENT'], $matches);
        } elseif (strpos($userAgent, 'Safari') !== false) {
            $browserName = 'Safari';
            preg_match_all('/Version\/([\d]+)/', $_SERVER['HTTP_USER_AGENT'], $matches);
        } elseif (strpos($userAgent, 'Firefox') !== false) {
            $browserName = 'Firefox';
            preg_match_all('/Firefox\/([\d]+)/', $_SERVER['HTTP_USER_AGENT'], $matches);
        } elseif (strpos($userAgent, 'MSIE') !== false) {
            $browserName = 'IE';
            preg_match_all('/MSIE ([\d]+)/', $_SERVER['HTTP_USER_AGENT'], $matches);
        } elseif (strpos($userAgent, 'Trident/7') !== false) {
            $browserName = 'IE';
            preg_match_all('/rv:([\d]+)/', $_SERVER['HTTP_USER_AGENT'], $matches);
        } else {
            $browserName = 'unknown';
        }

        $version = isset($matches[1]) ? $matches[1][0] : 'unknown';

        return ['name' => $browserName, 'version' => $version];
    }
}
