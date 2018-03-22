<?php

namespace FieldInteractive\CitoBundle\Cito;

use DOMDocument;
use DOMNode;
use DOMXPath;

/**
 * Cito framework.
 *
 * Takes a normal unordered list as its source and
 * marks currently active node and parent node
 *
 * @author Marc Harding <info@marcharding.de>
 */
class Navigation
{
    /**
     * @var const
     */
    const CLASS_ACTIVE = 'active';

    /**
     * @var const
     */
    const CLASS_OPEN = 'open';

    /**
     * @var const
     */
    const ID_SELECTED = 'active';

    /**
     * @var const
     */
    const ID_OPEN = 'open';

    /**
     * @var string
     */
    private $requestedUri;

    /**
     * @var DOMDocument
     */
    private $dom;

    /**
     * @var DOMXPath
     */
    private $xp;

    /**
     * Initialize navigation.
     *
     * @param Config $config
     * @param string $navigationSource XML/HTML source, unordered list
     * @param string $requestedUri     requested URI
     */
    public function __construct($navigationSource, $requestedUri = false)
    {
        $this->requestedUri = $requestedUri;
        $this->dom = new \DOMDocument();
        $this->dom->preserveWhiteSpace = false;
        $this->dom->formatOutput = true;
        $this->dom->recover = true;

        if (is_file($navigationSource)) {
            $this->dom->load($navigationSource);
        } else {
            $this->dom->loadXML($navigationSource);
        }

        $this->xp = new \DOMXPath($this->dom);

        $this->processNodes($this->dom->documentElement->parentNode);
    }

    /**
     * Process node.
     *
     * @param DOMNode $node
     *
     * @return bool|void
     */
    public function processNodes(DOMNode $node)
    {
        // get all link nodes
        $allLinkNodes = $this->xp->query('.//a', $node);
        $allLinkNodesSortedByLength = [];
        foreach ($allLinkNodes as $linkNode) {
            $allLinkNodesSortedByLength[] = [
                'href' => $linkNode->getAttribute('href'),
                'node' => $linkNode,
            ];
        }

        // sort by link-length
        usort(
            $allLinkNodesSortedByLength,
            function ($a, $b) {
                $lengthA = strlen($a['href']);
                $lengthB = strlen($b['href']);
                if ($lengthA == $lengthB) {
                    return 0;
                }

                return ($lengthA < $lengthB) ? 1 : -1;
            }
        );

        // set best matching link node to active, all ancestors to open
        $requestedUri = $this->requestedUri;
        while (DIRECTORY_SEPARATOR !== $requestedUri) {
            foreach ($allLinkNodesSortedByLength as $currentLinkNode) {
                if ($currentLinkNode['href'] === $requestedUri) {
                    $currentLinkNode['node']->setAttribute('class', trim($currentLinkNode['node']->getAttribute('class').' '.self::CLASS_ACTIVE));

                    // set nearest li ancestor to active
                    $ancestorListNode = $this->xp->query('./ancestor::li', $currentLinkNode['node'])->item(
                        $this->xp->query('./ancestor::li', $currentLinkNode['node'])->length - 1
                    );
                    $ancestorListNode->setAttribute('class', trim($ancestorListNode->getAttribute('class').' '.self::CLASS_ACTIVE));

                    // set all other li ancestors to open and all links inside them to open
                    $remainingAncestorListNodes = $this->xp->query('ancestor::li', $ancestorListNode);
                    foreach ($remainingAncestorListNodes as $remainingAncestorListNode) {
                        $remainingAncestorListNode->setAttribute('class', trim($remainingAncestorListNode->getAttribute('class').' '.self::CLASS_OPEN));
                        $descendantAnchorNode = $this->xp->query('descendant::a', $remainingAncestorListNode)->item(0);
                        $descendantAnchorNode->setAttribute('class', trim($descendantAnchorNode->getAttribute('class').' '.self::CLASS_OPEN));
                    }

                    return;
                }
            }
            $requestedUri = dirname($requestedUri);
        }

        return false;
    }

    /**
     * Restrict navigation to given range.
     *
     * Examples:
     * $range = 3, only show level 3
     * $range = array( 1 => 0 ), only show level 1 and 0 nested levels
     *
     * @param array $range
     *
     * @return DOMNode $nodeList
     */
    public function filterRange($range)
    {
        if (is_int($range)) {
            $level = $range;
            $depth = 0;
        } else {
            reset($range);
            $level = key($range);
            $depth = current($range);
        }

        if (1 === $level) {
            $nodeList = $this->xp->query('/descendant-or-self::*[ name() = "ul" and count( ancestor-or-self::ul ) >= '.$level.'  ]')->item(0);
        } else {
            $nodeList = $this->xp->query('/descendant-or-self::*[ name() = "ul" and count( ancestor-or-self::ul ) >= '.$level.' and ( descendant-or-self::*[ contains(@class, "open") or contains(@class, "active") ] or ancestor-or-self::*[ contains(@class, "open")  or contains(@class, "active") ] ) ]')->item(0);
        }

        if (!$nodeList) {
            return false;
        }

        if (false === $depth) {
            return $nodeList;
        }

        $depthNodelist = $this->xp->query('./descendant::*[ name() = "ul" and count( ancestor-or-self::ul ) > '.($level + $depth).' ]', $nodeList);

        foreach ($depthNodelist as $node) {
            $node->parentNode->removeChild($node);
        }

        if ($nodeList) {
            return $nodeList;
        } else {
            return false;
        }
    }

    /**
     * Create breadcrumb for current page.
     *
     * @return DOMNode $nodeList
     */
    public function filterBreadcrumb()
    {
        $nodeList = $this->xp->query('//li[ contains(@class, "open") or contains(@class, "active")]');
        if (0 !== $nodeList->length) {
            $ul = $this->dom->createElement('ul');
            foreach ($nodeList as $node) {
                $li = $this->dom->createElement('li');
                $li->appendChild($node->firstChild);
                $ul->appendChild($li);
            }

            return $ul;
        }
    }

    /**
     * Static helper function to render Navigation.
     *
     * @param string $navigationSource XML/HTML source, unordered list
     * @param string $requestedUri     requested URI
     * @param string $options          Navigation options
     *
     * @return string
     */
    public static function render($navigationFile, $requestedUri = false, $options = null)
    {
        $instance = new Navigation($navigationFile, $requestedUri);
        if ($options) {
            if (isset($options['breadcrumb'])) {
                $temp = $instance->filterBreadcrumb();
                if ($temp) {
                    return $instance->dom->saveXML($temp);
                }
            }
            if (isset($options['range']) && $temp = $instance->filterRange($options['range'])) {
                return $instance->dom->saveXML($temp);
            }
        } else {
            return $instance->dom->saveXML($instance->dom->documentElement);
        }
    }
}
