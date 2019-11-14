<?php

namespace Cap\CleanMedia\Console\Command;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use RecursiveDirectoryIterator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CleanMedia extends Command
{
    protected $countFiles = 0;
    protected $filesSize = 0;

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
                ->setName('cap:clean:media')
                ->setDescription('Remove images of deleted products in /media folder && database entries')
                ->addOption(
                        'limit',
                        null,
                        InputOption::VALUE_REQUIRED,
                        'How many files should be deleted?',
                        100
                );
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
            // remove cache folder for performance.
            if (strpos($file, "/cache") !== false || is_dir($file)) {
                continue;
            }
            // Input option --limit=XXX
            if ($this->countFiles < $input->getOption('limit')) {
                echo $file;
                echo PHP_EOL;
                $this->countFiles++;
                $this->filesSize += filesize($file);
            }
        }
        echo $this->countFiles.' files';
        echo PHP_EOL;
        echo number_format($this->filesSize / 1024 / 1024, '2').' MB';
        echo PHP_EOL;

    }
}
