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

    private $supportedLanguages;

    private $translationEnabled;

    public function __construct(RequestStack $request, string $projectDir, array $supportedLanguages, bool $translationEnabled)
    {
        $this->request = $request->getCurrentRequest();
        $this->projectDir = $projectDir;
        $this->supportedLanguages = $supportedLanguages;
        $this->translationEnabled = $translationEnabled;
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
            new \Twig_Function('navigation', [$this, 'setNavigation'], ['needs_environment' => true, 'is_safe' => ['html']]),
            new \Twig_Function('page', [$this, 'getPage'], ['needs_environment' => true, 'is_safe' => ['html']]),
            new \Twig_Function('pagelist', [$this, 'getPagelist'], ['needs_environment' => true]),
            new \Twig_Function('language_switch', [$this, 'getLanguageSwitch'], ['needs_environment' => true, 'is_safe' => ['html']]),
        ];
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('ratio', [$this, 'imageRatioAspectFilter']),
        );
    }

    public function getLanguageSwitch(\Twig_Environment $twig, $template)
    {
        $template = ltrim($template, '/');
        $uri = $this->request->getPathInfo();
        $uri = ltrim(substr($uri, 3), '/');

        return $twig->render($template, [
            'locale' => $this->request->getLocale(),
            'languages' => $this->supportedLanguages,
            'link' => $uri
        ]);
    }

    /**
     * @param $path
     * @param null  $uri
     * @param array $options
     *
     * @return string
     */
    public function setNavigation(\Twig_Environment $twig, $path, $options = [], $uri = null)
    {
        $path = ltrim($path, '/');

        if (!isset($uri)) {
            $uri = $this->request->getPathInfo();
        }

        if (is_file($this->projectDir.'templates/'.$path) || is_file($this->projectDir.'pages/'.$path)) {
            $navHtml = $twig->render($path);
            return Navigation::render($navHtml, $uri, $options);
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

        if ($this->translationEnabled) {
            $uri = substr($uri, 3);
        }
        
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
