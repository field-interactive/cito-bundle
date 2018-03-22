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
     * The 'CatchAllAction' every other action/route must be above!!
     *
     * @Route("/{url}", name="field_cito_z", requirements={"url": "(.+)?"})
     */
    public function zAction(Request $request, $url)
    {
        $url = rtrim($url, '/');

        if (is_file($this->pagesPath.$url.'.html.twig')) {
            return $this->render($url.'.html.twig');
        } elseif (is_file($this->pagesPath.$url.'/index.html.twig')) {
            return $this->render($url.'/index.html.twig');
        }

        throw $this->createNotFoundException($url.' not found');
    }
}
