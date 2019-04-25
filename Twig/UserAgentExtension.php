<?php

namespace FieldInteractive\CitoBundle\Twig;

use FieldInteractive\CitoBundle\Controller\CitoController;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig_SimpleFunction;


class UserAgentExtension extends AbstractExtension
{
    private $env;
    private $enabled;
    private $routingData;
    private $defaultRoute;

    public function __construct(Environment $env, $enabled, $routingData, $defaultRoute)
    {
        $this->env = $env;
        $this->enabled = $enabled;
        $this->routingData = $routingData;
        $this->defaultRoute = $defaultRoute;
    }

    public function getFunctions()
    {
        return array(
            new TwigFunction('include_ua', array($this, 'includeUserAgent')),
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
