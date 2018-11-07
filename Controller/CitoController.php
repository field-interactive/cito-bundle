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

        $locale = $this->getParameter('locale');
        $urlLocale = $this->urlLocale($url);
        if ($locale && !$urlLocale) {
            // redirect to url with default locale
            return $this->redirect($locale.'/'.$url);
        } elseif ($urlLocale) {
            // remove locale to find page
            $url = substr($url, 3);
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
    protected function urlLocale(string $url)
    {
        $locale = trim(substr($url, 0, 3), '/');
        if (strlen($locale) > 2 || strlen($locale) < 2) {
            return false;
        }

        return $locale;
    }
}
