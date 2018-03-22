<?php

namespace FieldInteractive\CitoBundle;

use FieldInteractive\CitoBundle\DependencyInjection\CitoExtension;
use Symfony\Component\Console\Application;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CitoBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new CitoExtension();
    }

    public function registerCommands(Application $application)
    {
        // noop
    }
}
