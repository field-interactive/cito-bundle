<?php

namespace FieldInteractive\CitoBundle\Twig;

use FieldInteractive\CitoBundle\Cito\Navigation;
use FieldInteractive\CitoBundle\Cito\Page;
use FieldInteractive\CitoBundle\Cito\Pagelist;
use ArrayIterator;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig_Error_Syntax;

class CitoExtension extends \Twig_Extension
{
    private $request;

    private $projectDir;

    public function __construct(RequestStack $request, $projectDir)
    {
        $this->request = $request->getCurrentRequest();
        $this->projectDir = $projectDir;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'citoExtension';
    }

    public function getFunctions()
    {
        return [
            new \Twig_Function('navigation', [$this, 'setNavigation'], ['is_safe' => ['html']]),
            new \Twig_Function('page', [$this, 'getPage'], ['needs_environment' => true, 'is_safe' => ['html']]),
            new \Twig_Function('pagelist', [$this, 'getPagelist'], ['needs_environment' => true]),
        ];
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('ratio', [$this, 'imageRatioAspectFilter']),
        );
    }

    /**
     * @param $path
     * @param null  $uri
     * @param array $options
     *
     * @return string
     */
    public function setNavigation($path, $options = [], $uri = null)
    {
        $path = ltrim($path, '/');

        if (!isset($uri)) {
            $uri = $this->request->getPathInfo();
        }

        if (is_file($this->projectDir.$path)) {
            return Navigation::render($this->projectDir.$path, $uri, $options);
        } elseif (is_file($this->projectDir.'templates/'.$path)) {
            return Navigation::render($this->projectDir.'templates/'.$path, $uri, $options);
        }

        return '';
    }

    /**
     * @param \Twig_Environment $twig
     * @param $path
     * @param array $context
     *
     * @return Page|null
     *
     * @throws Twig_Error_Syntax
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     */
    public function getPage(\Twig_Environment $twig, $path, $context = [])
    {
        $page = null;

        $path = Page::generateRelativePath($path);
        $template = $twig->load($path);
        $page = new Page($template, $context);

        return $page;
    }

    /**
     * @param \Twig_Environment $twig
     * @param array             $options
     *
     * @return \FieldInteractive\CitoBundle\Cito\ArrayIterator|ArrayIterator
     *
     * @throws Twig_Error_Syntax
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     */
    public function getPagelist(\Twig_Environment $twig, $options = [])
    {
        $defaultOptions = [
            'dir' => null,
            'files' => null,
            'sortOrder' => null,
            'sortBy' => null,
            'filterBy' => null,
            'limit' => null,
        ];

        $options = array_merge($defaultOptions, $options);

        // from which template is this method called, prevent recusion and self-listing
        $current = $this->getCurrentPage();

        if (isset($options['dir'])) {
            return Pagelist::dir($twig, $this->projectDir.'pages/'.$options['dir'], $current, $options['sortOrder'], $options['sortBy'], $options['filterBy'], $options['limit']);
        }

        if (isset($options['files'])) {
            foreach ($options['files'] as $key => $file) {
                if (!strpos($file, $this->projectDir)) {
                    $options['files'][$key] = $this->projectDir.'pages/'.ltrim($file, '/');
                }
            }

            return Pagelist::files($twig, $options['files'], $current, $options['sortOrder'], $options['sortBy'], $options['filterBy']);
        }

        return new ArrayIterator([]);
    }

    /**
     * @return string
     */
    private function getCurrentPage()
    {
        $uri = $this->request->getRequestUri();
        $uri = rtrim($uri, '/');
        if (is_file($this->projectDir.'pages/'.$uri.'.html.twig')) {
            return $uri.'.html.twig';
        } else {
            return $uri.'/index.html.twig';
        }
    }

    /**
     * @return float
     */
    public function imageRatioAspectFilter($file)
    {
        // TODO: Inject resource path
        $file = $this->projectDir . 'public/'. $file;
        if(is_file($file)){
            $sizes = array_slice(getimagesize($file), 0, 2);
            return round($sizes[0] / $sizes[1], 4);
        } else {
            return 1;
        }
    }


}
