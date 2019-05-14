<?php

namespace FieldInteractive\CitoBundle\Twig;

use FieldInteractive\CitoBundle\Cito\Navigation;
use FieldInteractive\CitoBundle\Cito\Page;
use FieldInteractive\CitoBundle\Cito\Pagelist;
use FieldInteractive\CitoBundle\Service\RouteResolverService;
use ArrayIterator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class CitoExtension extends AbstractExtension
{
    private $request;

    private $projectDir;

    private $supportedLanguages;

    private $translationEnabled;

    private $userAgentEnabled;

    private $routeResolver;

    private $container;

    public function __construct(RequestStack $request, RouteResolverService $routeResolver, string $projectDir, array $supportedLanguages, bool $translationEnabled, bool $userAgentEnabled, ContainerInterface $container)
    {
        $this->request = $request->getCurrentRequest();
        $this->projectDir = $projectDir;
        $this->supportedLanguages = $supportedLanguages;
        $this->translationEnabled = $translationEnabled;
        $this->userAgentEnabled = $userAgentEnabled;
        $this->routeResolver = $routeResolver;
        $this->container = $container;
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
            new TwigFunction('navigation', [$this, 'setNavigation'], ['needs_environment' => true, 'is_safe' => ['html']]),
            new TwigFunction('page', [$this, 'getPage'], ['needs_environment' => true, 'is_safe' => ['html']]),
            new TwigFunction('pagelist', [$this, 'getPagelist'], ['needs_environment' => true]),
            new TwigFunction('language_switch', [$this, 'getLanguageSwitch'], ['needs_environment' => true, 'is_safe' => ['html']]),
        ];
    }

    public function getFilters()
    {
        return array(
            new TwigFilter('ratio', [$this, 'imageRatioAspectFilter']),
        );
    }

    public function getLanguageSwitch(Environment $twig, $template)
    {
        $template = ltrim($template, '/');
        $uri = $this->request->getPathInfo();
        $uri = ltrim(substr($uri, 3), '/');
        $route = $this->routeResolver->resolveRealRoute($uri, $this->request->getLocale(), false);

        $languages = [];
        foreach ($this->supportedLanguages as $locale => $language) {
            $languages[$locale]['locale'] = $locale;
            $languages[$locale]['language'] = $language;
            $languages[$locale]['link'] = $this->routeResolver->resolveRouteByFile($route, $locale);
        }

        return $twig->render($template, [
            'locale' => $this->request->getLocale(),
            'languages' => $languages,
            'route' => $route
        ]);
    }

    /**
     * @param $path
     * @param null  $uri
     * @param array $options
     *
     * @return string
     */
    public function setNavigation(Environment $twig, $path, $options = [], $uri = null)
    {
        $path = ltrim($path, '/');

        if (!isset($uri)) {
            $uri = $this->request->getPathInfo();
        }

        $navHtml = $twig->render($path);
        return Navigation::render($navHtml, $uri, $options);
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
    public function getPage(Environment $twig, $path, $context = [])
    {
        $page = null;

        $path = Page::generateRelativePath($path);
        $template = $twig->load($path);
        $page = new Page($template, $context, $this->userAgentEnabled);

        if ($this->translationEnabled) {
            $page->link = $this->routeResolver->resolveRouteByFile($page->path, $this->request->getLocale());
        }

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
    public function getPagelist(Environment $twig, $options = [])
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
            $pagelist = Pagelist::dir($twig, $this->projectDir.'pages/'.$options['dir'], $current, $options['sortOrder'], $options['sortBy'], $options['filterBy'], $options['limit'], $this->userAgentEnabled);
            foreach ($pagelist as &$page)
            if ($this->translationEnabled) {
                $page->link = '/'.$this->routeResolver->resolveRouteByFile($page->path, $this->request->getLocale());
            }
            return $pagelist;
        }

        if (isset($options['files'])) {
            foreach ($options['files'] as $key => $file) {
                if (!strpos($file, $this->projectDir)) {
                    $options['files'][$key] = $this->projectDir.'pages/'.ltrim($file, '/');
                }
            }

            $pagelist = Pagelist::files($twig, $options['files'], $current, $options['sortOrder'], $options['sortBy'], $options['filterBy'], $this->userAgentEnabled);
            foreach ($pagelist as &$page)
                if ($this->translationEnabled) {
                    $page->link = '/'.$this->routeResolver->resolveRouteByFile($page->path, $this->request->getLocale());
                }
            return $pagelist;
        }

        throw new MissingOptionsException('The option "dir" for directories or "files" for exact files is missing.');
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
