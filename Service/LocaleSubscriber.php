<?php

namespace FieldInteractive\CitoBundle\Service;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LocaleSubscriber implements EventSubscriberInterface
{
    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array(array('onKernelRequest', 20)),
        );
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $locale = $this->getLocale($request->getPathInfo());
        if (!$locale) {
            $locale = 'en'; // to prevent "Circular reference detected" error
        }
        $request->setLocale($locale);
    }

    private function getLocale(string $path)
    {
        $locale = trim(substr($path, 0, 4), '/');
        if (strlen($locale) !== 2) {
            return false;
        }

        return $locale;
    }
}
