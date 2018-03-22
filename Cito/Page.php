<?php

namespace FieldInteractive\CitoBundle\Cito;

/**
 * Class Page.
 */
class Page
{
    private $template;

    /**
     * @var array
     */
    public $blocks = [];

    /**
     * @var string
     */
    public $link;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $fullPath;

    /**
     * @var string
     */
    public $path;

    /**
     * @var array
     */
    public $context;

    /**
     * @var array
     */
    private $blockErrors;

    /**
     * Page constructor.
     *
     * @param \Twig_TemplateWrapper $template
     * @param $fullPath
     * @param array $context
     */
    public function __construct(\Twig_TemplateWrapper $template, $context = [])
    {
//        $fullPath = realpath($fullPath);

        $this->template = $template;
        $this->fullPath = $template->getSourceContext()->getPath();
        $this->path = $this->generateRelativePath($this->fullPath);
        $this->link = $this->generateSelfLink($this->fullPath);
        $this->name = $this->getTemplateName($this->fullPath);

        $this->context = $context;
        foreach ($template->getBlockNames() as $name) {
            try {
                $this->blocks[$name] = $template->renderBlock($name, $this->context);
            } catch (\Throwable $e) {
                $this->blockErrors[$name] = $e->getMessage();
            }
        }
    }

    /**
     * @return mixed
     */
    public function getBlockErrors()
    {
        return $this->blockErrors;
    }

    /**
     * @param $blockName
     *
     * @return mixed|null
     */
    public function getBlockError($blockName)
    {
        if (array_key_exists($blockName, $this->blockErrors)) {
            return $this->blockErrors[$blockName];
        }

        return null;
    }

    /**
     * @param $absolutePath
     *
     * @return bool|string
     */
    public static function generateRelativePath($absolutePath)
    {
        if ($start = strpos($absolutePath, '/pages/')) {
            return substr($absolutePath, $start + 7);
        } elseif ($start = strpos($absolutePath, '/templates/')) {
            return substr($absolutePath, $start + 11);
        }

        return $absolutePath;
    }

    /**
     * @param $path
     *
     * @return string
     */
    public static function generateSelfLink($path)
    {
        if ($start = strpos($path, '/pages/')) {
            $path = substr($path, $start + 7);
        } elseif ($start = strpos($path, '/templates/')) {
            $path = substr($path, $start + 11);
        }

        return '/'.trim(str_replace(['.html.twig', 'index'], '', $path), '/');
    }

    /**
     * @param $path
     *
     * @return mixed
     */
    public static function getTemplateName($path)
    {
        $pathArray = explode('/', $path);

        return end($pathArray);
    }
}
