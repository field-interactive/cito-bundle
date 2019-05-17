<?php


namespace FieldInteractive\CitoBundle\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class WebpImagesCommand extends Command
{
    protected $webroot;

    protected $compiledPath;

    public function __construct($webroot, string $name = null)
    {
        $webroot = rtrim($webroot, '/').'/';
        $this->webroot = $webroot;
        $this->compiledPath = $webroot.'build/images/';

        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('cito:images:webp')
            ->setDescription('Converts Images to WebP')
            ->setHelp('')
            ->addArgument('path', InputArgument::REQUIRED,  'Path to images folder from webroot')
            ->addOption('exclude', 'ex', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,  'Excluded files or directories')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $path = $input->getArgument('path');
        $exclude = $input->getOption('exclude');

        $path = $this->webroot.ltrim($path, '/');

        if (!is_dir($path)) {
            $io->warning("Directory $path not found.");
            return;
        }

        $images = $this->convertFiles($path, $path);
        dump($images);
    }

    protected function convertFiles(string $path, string $basepath)
    {
        $directory = new \DirectoryIterator($path);
        $images = [];

        foreach ($directory as $item) {
            if ($item->isDot()) {
                continue;
            } elseif ($item->isDir()) {
                $images[] = [
                    'dir' => $item->getPathname(),
                    'images' => $this->convertFiles($item->getPathName(), $basepath)
                ];
            }
            $imgPath = str_replace($basepath."\\", '', $item->getPathname());
            $webp = preg_split("/\./", $item->getPathname())[0].'.webp';

            $image = imagecreatefromstring(file_get_contents($item->getPathname()));
//             fopen(, 'r');
            $success = imagewebp($image, $webp);
//            fclose($image);
            $images[] = [
                'name' => $item->getFilename(),
                'path' => $item->getPathname(),
                'webp' => $webp,
                'success' => $success
            ];
        }

        return $images;
    }
}
