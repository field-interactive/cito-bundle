<?php

namespace FieldInteractive\CitoBundle\Command;

use FieldInteractive\CitoBundle\Service\SocialMedia;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;


class GetFacebookPosts extends Command
{
    private $socialMediaService;

    public function __construct(SocialMedia $service)
    {
        $this->socialMediaService = $service;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('cito:social-media:facebook-posts')
            ->setDescription('Loads latest Facebook posts')
            ->setHelp('This command allows you to load the latest Facebook posts')
            ->addArgument('user', InputArgument::OPTIONAL, 'The user which posts you want');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $user = $input->getArgument('user');

        try {
            $this->socialMediaService->getFacebookPosts($user);
            $output->writeln("Facebook posts loadet");
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
        }

    }
}
