<?php


namespace FieldInteractive\CitoBundle\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RunWebpackCommand extends Command
{
    protected function configure()
    {
        $this->setName('cito:node:webpack')
            ->setDescription('')
            ->setHelp('')
            ->addArgument('script', InputArgument::REQUIRED,  'Name of script to execute')
            ->addOption('yarn', 'y', InputOption::VALUE_NONE)
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $script = $input->getArgument('script');
        $yarn = $input->getOption('yarn');

        $cmd = "npm run $script";

        if ($yarn) {
            $cmd = "yarn $script";
        }

        $output->writeln('$> '.$cmd);
        $output->write(shell_exec($cmd));
    }
}
