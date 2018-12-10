<?php

namespace FieldInteractive\CitoBundle\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LocaleSubscriber implements EventSubscriberInterface
{
    private $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

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
            $locale = $this->params->get('locale');
        }
        $request->setLocale($locale);
    }

    private function getLocale(string $path)
    {
        $locale = trim(substr($path, 0, 4), '/');
        if (strlen($locale) !== 2) {
            return false;
        }

        $supported = $this->params->get('field_cito.translation.translation_support');
        return array_key_exists($locale, $supported) ? $locale : false;
    }
}
