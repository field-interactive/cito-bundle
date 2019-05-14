<?php

namespace FieldInteractive\CitoBundle\Form;


use FieldInteractive\CitoBundle\Cito\Flash;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

abstract class CitoForm extends AbstractType
{
    protected static $success = null;

    /**
     * Handles the data after submit
     *
     * @param Form $form
     * @return mixed
     */
    public static abstract function postSubmit(Form $form);

    /**
     * Message for flashbag
     *
     * @return Flash
     */
    public static function flashMessage() : Flash
    {
        return null;
    }

    /**
     * resets static variables
     */
    public static function reset()
    {
        self::$success = null;
    }
}
