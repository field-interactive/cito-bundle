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

        if (is_file($this->pagesPath.$url.'.html.twig')) {
            return $this->render($url.'.html.twig');
        } elseif (is_file($this->pagesPath.$url.'/index.html.twig')) {
            return $this->render($url.'/index.html.twig');
        }

        $locale = $this->getParameter('locale');
        $urlLocale = $this->urlLocale($url);
        if ($locale && !$urlLocale) {
            return $this->redirect($locale.'/'.$url);
        }

        $errMsg = $url.' not found! Searched for '.$this->pagesPath.$url.'.html.twig and '.$this->pagesPath.$url.'/index.html.twig!';
        throw $this->createNotFoundException($errMsg);
    }

    protected function urlLocale(string $url)
    {
        $locale = trim(substr($url, 0, 3), '/');
        if (strlen($locale) > 2 || strlen($locale) < 2) {
            return false;
        }

        return $locale;
    }
}
