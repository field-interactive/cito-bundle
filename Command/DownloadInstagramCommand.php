<?php

namespace FieldInteractive\CitoBundle\Command;

use FieldInteractive\CitoBundle\Service\SocialMediaService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;


class DownloadInstagramCommand extends Command
{
    private $socialMediaService;

    public function __construct(SocialMediaService $service)
    {
        $this->socialMediaService = $service;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('cito:social:instagram:download')
            ->setDescription('Downloads latest Instagram posts')
            ->setHelp('This command allows you to download the latest Instagram posts')
            ->addOption('user', 'u', InputArgument::OPTIONAL, 'The user which posts you want', null)
            ->addOption('count', 'c', InputArgument::OPTIONAL, 'The amount of posts you want', 10);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $user = $input->getOption('user');        
        $count = $input->getOption('count');

        try {
            $this->socialMediaService->downloadInstagramFeed($user, $count);
            $output->writeln("Instagram posts loadet");
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
        }
    }
}
