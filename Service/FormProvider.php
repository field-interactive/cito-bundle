<?php

namespace FieldInteractive\CitoBundle\Service;

use FieldInteractive\CitoBundle\Exception\FormNotFoundException;
use FieldInteractive\CitoBundle\Form\CitoForm;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class FormProvider
{
    /**
     * @var string
     */
    protected $formPath;

    /**
     * @var string
     */
    protected $formNamespace;

    /**
     * @var array
     */
    protected $forms;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * FormProvider constructor.
     * @param ContainerInterface $container
     * @param LoggerInterface $logger
     * @param string $formDir
     * @param string $formNamespace
     */
    public function __construct(ContainerInterface $container, LoggerInterface $logger, string $formDir, string $formNamespace)
    {
        $this->formPath = $formDir;
        $this->formNamespace = rtrim($formNamespace, '\\').'\\';
        $this->forms = [];
        $this->container = $container;
        $this->logger = $logger;

        $this->createForms();
    }

    /**
     * Build the Forms from the Formclasses
     *
     * @return array
     */
    public function createForms()
    {
        $formDir = [];

        if (!is_dir($this->formPath)) {
            $this->logger->warning('No forms folder found at '.$this->formPath);
            return [];
        }

        $handle = dir($this->formPath)->handle;
        while($item = readdir($handle)) {
            if (!in_array($item, ['.', '..'])) {
                $formDir[] = $item;
            }
        }

        foreach ($formDir as $class) {
            $class = $this->formNamespace.rtrim($class, '.php');
            try {
                if (class_exists($class) && is_subclass_of($class, CitoForm::class) && !(new \ReflectionClass($class))->isAbstract()) {
                    $form = $this->createForm($class);
                    $this->forms[$form->getName()] = $form;
                }
            } catch (\ReflectionException $e) {
                $this->logger->error($e->getMessage(), [
                    'class' => $class,
                ]);
            }
        }

        return $this->forms;
    }

    /**
     * Returns a named Form
     *
     * @param string $name
     * @return \Symfony\Component\Form\Form|null
     */
    public function getForm(string $name)
    {
        if (array_key_exists($this->CamelcaseToSnakecase($name), $this->forms)) {
            return $this->forms[$name];
        }

        return null;
    }

    /**
     * Processes the Forms
     *
     * @param Request $request
     * @return array
     */
    public function processAllForms(Request $request)
    {
        $result = [];
        foreach ($this->forms as $form) {
            try {
                $result[$form->getName()] = $this->processForm($form->getName(), $request);
            } catch (FormNotFoundException $exception) {
                $this->logger->error($exception->getMessage(), []);
            }
        }

        return $result;
    }

    /**
     * Process the postSubmit function of the named Form
     *
     * @param string $name
     * @param Request $request
     * @return null
     *
     * @throws FormNotFoundException
     */
    public function processForm(string $name, Request $request)
    {
        if ($form = $this->getForm($name)) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $class = $this->SnakecaseToCamelcase($form->getName());
                $class = $this->formNamespace.rtrim($class, '.php');
                try {
                    if (class_exists($class) && is_subclass_of($class, CitoForm::class)) {
                        $class::setUp($this->container);
                        $result = $class::postSubmit($form);
                        if ($flash = $class::flashMessage()) {
                            $this->container->get('session')->getFlashBag()->add($flash->type, $flash->message);
                        }
                        $class::reset();
                        return $result;
                    }
                } catch (\ReflectionException $e) {
                    $this->logger->error($e->getMessage(), [
                        'class' => $class,
                    ]);
                }
            }
        }

        throw new FormNotFoundException("Form $name not found!");
    }

    /**
     * Creates the View for the Forms
     *
     * @return array
     */
    public function createFormViews()
    {
        $views = [];
        foreach ($this->forms as $form) {
            $name = $this->SnakecaseToCamelcase($form->getName());
            $views[$name] = $form->createView();
        }

        return $views;
    }

    /**
     * @param string $type
     * @param null $data
     * @param array $options
     * @return FormInterface
     */
    protected function createForm(string $type, $data = null, array $options = array()): FormInterface
    {
        return $this->container->get('form.factory')->create($type, $data, $options);
    }

    protected function SnakecaseToCamelcase(string $string)
    {
        $string = str_replace('_', '', ucwords($string, '_'));
        $string = lcfirst($string);
        return $string;
    }

    protected function CamelcaseToSnakecase(string $string)
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $string, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode('_', $ret);
    }
}
