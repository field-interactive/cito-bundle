<?php

namespace FieldInteractive\CitoBundle;

use FieldInteractive\CitoBundle\DependencyInjection\FieldCitoExtension;
use Symfony\Component\Console\Application;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class FieldCitoBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new FieldCitoExtension();
    }

    public function registerCommands(Application $application)
    {
        // noop
    }
}
