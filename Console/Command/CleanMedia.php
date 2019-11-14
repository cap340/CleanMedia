<?php

namespace Cap\CleanMedia\Console\Command;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use RecursiveDirectoryIterator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanMedia extends Command
{
    protected $countFiles = 0;

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
        $objectManager     = ObjectManager::getInstance();
        $filesystem        = $objectManager->get('Magento\Framework\Filesystem');
        $directory         = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $imageDir          = $directory->getAbsolutePath().'catalog'.DIRECTORY_SEPARATOR.'product';
        $directoryIterator = new RecursiveDirectoryIterator($imageDir);

        foreach (new \RecursiveIteratorIterator($directoryIterator) as $file) {
            if (is_dir($file)) {
                continue;
            }
            echo $file;
            echo PHP_EOL;
            $this->countFiles++;
            echo $this->countFiles;
            echo PHP_EOL;
        }
    }
}
