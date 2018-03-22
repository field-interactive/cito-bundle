<?php

namespace FieldInteractive\CitoBundle\Cito;

/**
 * Cito framework.
 *
 * @author Marc Harding <info@marcharding.de>
 */
class Pagelist
{
    /**
     * @var array
     */
    protected $allowedExtensions = [
        'php',
        'twig',
    ];

    /**
     * Sorting method.
     *
     * @param array  $pagelist  Pagelist array
     * @param string $sortOrder Sort order
     * @param string $sortBy    Sort key
     *
     * @return array $pagelist
     */
    public static function applySorting($pagelist, $sortOrder = null, $sortBy = null)
    {
        // sort order
        if (isset($sortOrder)) {
            switch ($sortOrder) {
                case 'asc':
                    $sortOrder = 1;
                    break;

                case 'desc':
                    $sortOrder = -1;
                    break;

                default:
                    $sortOrder = 1;
                    break;
            }
        }

        if (!isset($sortBy)) {
            $sortBy = 'name';
        }

        // sort key
        usort(
            $pagelist,
            function ($a, $b) use ($sortBy, $sortOrder) {
                if (isset($sortBy)) {
                    if (is_array($sortBy)) {
                        $key = key($sortBy);
                        $value = reset($sortBy);
                        if (isset($a->$key)) {
                            $temp = $a->$key;
                            if (isset($temp[$value])) {
                                $a = $temp[$value];
                            }
                        } else {
                            $a = 0;
                        }
                        if (isset($b->$key)) {
                            $temp = $b->$key;
                            if (isset($temp[$value])) {
                                $b = $temp[$value];
                            }
                        } else {
                            $b = 0;
                        }
                    } else {
                        $a = $a->{$sortBy};
                        $b = $b->{$sortBy};
                    }
                }

                if ($a == $b) {
                    return 0;
                }
                $compareResult = ($a < $b) ? 1 : -1;

                return $sortOrder * $compareResult;
            }
        );

        return $pagelist;
    }

    /**
     * Filter method.
     *
     * @param array $pagelist Pagelist array
     * @param $filterFunction Callback function
     *
     * @return array $pagelist
     */
    public static function applyFilter($pageList, $filterFunction = null)
    {
        if (isset($filterFunction)) {
            $pageList = array_filter($pageList, $filterFunction);
        }

        return $pageList;
    }

    /**
     * Limit method.
     *
     * @param string $pagelist Pagelist array
     * @param int    $limit    Limit entries
     *
     * @return array $pagelist
     */
    public static function applyLimit($pageList, $limit = null)
    {
        if (isset($limit)) {
            $pageList = array_slice($pageList, 0, $limit);
        }

        return $pageList;
    }

    /**
     * Static helper method for directory listings.
     *
     * @param \Twig_Environment $environment
     * @param string            $dir         Path to list
     * @param string            $current
     * @param string            $sortOrder
     * @param string            $sortBy
     * @param string            $filter
     * @param int               $limit
     *
     * @return ArrayIterator
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function dir(\Twig_Environment $environment, $dir, $current, $sortOrder = null, $sortBy = 'link', $filter = null, $limit = null)
    {
        $pagelist = [];

        // create new instance
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            if (!strpos(str_replace('\\', '/', $item->getPathname()), $current) && $item->isFile()) {
                $fullPath = $item->getPathname();
                $relativePath = Page::generateRelativePath($fullPath);
                $template = $environment->load($relativePath);
                $page = new Page($template);
                $pagelist[] = $page;
            }
        }

        // apply filter
//        $pagelist = self::applyFilter( $pagelist, $filter );

        // apply sorting
        $pagelist = self::applySorting($pagelist, $sortOrder, $sortBy);

        // apply limit
        $pagelist = self::applyLimit($pagelist, $limit);

        return new ArrayIterator($pagelist);
    }

    /**
     * Static helper method for file listings.
     *
     * @param \Twig_Environment $environment
     * @param array             $files       Files to list
     * @param $current
     * @param null   $sortOrder
     * @param string $sortBy
     * @param null   $filter
     *
     * @return ArrayIterator
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public static function files(\Twig_Environment $environment, $files, $current, $sortOrder = null, $sortBy = null, $filter = null)
    {
        $pagelist = [];
        foreach ($files as $file) {
            if (!strpos($file, $current)) {
                $relativePath = Page::generateRelativePath($file);
                $template = $environment->load($relativePath);
                $page = new Page($template);
                $pagelist[] = $page;
            }
        }

        // apply filter
//        $pagelist = self::applyFilter( $pagelist, $filter );

        if (isset($sortBy)) {
            $pagelist = self::applySorting($pagelist, $sortOrder, $sortBy);
        }

        return new ArrayIterator($pagelist);
    }
}
