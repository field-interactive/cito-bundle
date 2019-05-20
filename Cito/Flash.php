<?php

namespace FieldInteractive\CitoBundle\Cito;


final class Flash
{
    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $message;

    public function __construct(string $type, string $message)
    {
        $this->type = $type;
        $this->message = $message;
    }
}
