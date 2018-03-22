<?php

namespace FieldInteractive\CitoBundle\Cito;

/**
 * Cito framework.
 *
 * @author Marc Harding <info@marcharding.de>
 */
class ArrayIterator extends \ArrayIterator
{
    /**
     * Check if first.
     *
     * @return bool
     */
    public function first()
    {
        return parent::key() === $this->getFirstKey();
    }

    /**
     * Check if last.
     *
     * @return bool
     */
    public function last()
    {
        return parent::key() === $this->getLastKey();
    }

    /**
     * return position.
     *
     * @return int Position
     */
    public function position()
    {
        return $this->key();
    }

    /**
     * get first key.
     *
     * @return string
     */
    protected function getFirstKey()
    {
        $array = $this->getArrayCopy();
        reset($array);

        return key($array);
    }

    /**
     * get last key.
     *
     * @return string
     */
    protected function getLastKey()
    {
        $array = $this->getArrayCopy();
        end($array);

        return key($array);
    }
}
