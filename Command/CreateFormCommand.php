<?php

namespace FieldInteractive\CitoBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class CreateFormCommand extends Command
{
    private $twig;

    private $namespace;

    private $directory;

    public function __construct(Environment $twig, string $namespace, string $directory, string $name = null)
    {
        $this->twig = $twig;
        $this->namespace = $namespace;
        $this->directory = rtrim($directory, '/').'/';

        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('cito:form:create')
            ->setDescription('Creates a Cito Form')
            ->setHelp('Creates a form to be used for the FormProvider')
            ->addArgument('name', InputArgument::REQUIRED,  'Name of the new form')
            ->addOption('mailer', 'm', InputOption::VALUE_NONE,  'Is a mailer required?')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $fs = new Filesystem();

        $name = $input->getArgument('name');
        $mailer = $input->getOption('mailer');

        $name = ucfirst($name);
        $name = rtrim($name, 'Form').'Form';

        if (!$fs->exists($this->directory)) {
            $fs->mkdir($this->directory);
        }

        $file = $this->directory.$name.'.php';
        $skeleton = __DIR__.'/../Resources/skeletons/FormSkeleton.php.twig';

        try {

            $template = file_get_contents($skeleton);
            $template = $this->twig->createTemplate($template);
            $template = $this->twig->render($template, [
                'name' => $name,
                'mailer' => $mailer,
                'namespace' => $this->namespace
            ]);

            $content = "<?php\n\n$template";
            $fs->dumpFile($file, $content);

            $io->success("You can edit the form under: $file.");
        } catch (LoaderError $e) {
            $io->error("Loader: ".$e->getMessage());
        } catch (SyntaxError $e) {
            $io->error("Syntax: ".$e->getMessage());
        } catch (RuntimeError $e) {
            $io->error("Runtime: ".$e->getMessage());
        }
    }
}
