<?php

// todo: add --dry-run option to avoid double iteration & comment in CHANGELOG.md
// todo: add --limit=XXX option & comment in CHANGELOG.md
// todo: where should I put the limit option ?
// todo: update README.md
// todo: add command magento cap:clean:media --help in README.md for options

namespace Cap\CleanMedia\Console\Command;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanMedia extends Command
{
    /* @var Filesystem */
    protected $_filesystem;

    /**
     * CleanMedia constructor.
     *
     * @param Filesystem $filesystem
     */
    public function __construct(
            Filesystem $filesystem
    ) {
        $this->_filesystem = $filesystem;
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
                ->setName('cap:clean:media')
                ->setDescription('Remove images of deleted products in /media folder && database entries');
        parent::configure();
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $directory = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $imageDir  = $directory->getAbsolutePath().'catalog'.DIRECTORY_SEPARATOR.'product';
        $directoryIterator = new RecursiveDirectoryIterator($imageDir);

        foreach (new RecursiveIteratorIterator($directoryIterator) as $file) {
            // Remove cache folder for performance.
            if (strpos($file, "/cache") !== false || is_dir($file)) {
                continue;
            }
            echo $file;
            echo PHP_EOL;

        }
//        echo $imageDir;
//        echo PHP_EOL;
    }


}
