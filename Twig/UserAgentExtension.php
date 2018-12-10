<?php

namespace FieldInteractive\CitoBundle\Twig;

use FieldInteractive\CitoBundle\Controller\CitoController;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig_SimpleFunction;


class UserAgentExtension extends \Twig_Extension
{
    private $env;
    private $enabled;
    private $routingData;
    private $defaultRoute;

    public function __construct(\Twig_Environment $env, $enabled, $routingData, $defaultRoute)
    {
        $this->env = $env;
        $this->enabled = $enabled;
        $this->routingData = $routingData;
        $this->defaultRoute = $defaultRoute;
    }

    public function getFunctions()
    {
        return array(
            new Twig_SimpleFunction('include_ua', array($this, 'includeUserAgent')),
        );
    }

    public function includeUserAgent($template, $selectedRoute = 'all')
    {
        if ($selectedRoute === 'all') {
            $selectedRoute = CitoController::getSelectedRoute($this->routingData, $this->defaultRoute);
        }

        if (is_file(CitoController::$pagesPath.$selectedRoute . "/" . $template.'.html.twig')) {
            return $this->env->render($selectedRoute . "/" . $template.'.html.twig');
        } else {
            return "Could not include file: " . CitoController::$pagesPath.$selectedRoute . "/" . $template.'.html.twig';
        }
    }
}
