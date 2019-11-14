<?php

namespace Cap\CleanMedia\Console\Command;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CleanMedia extends Command
{
    /**
     * @var int
     */
    private $_filesSize;
    /**
     * @var int
     */
    private $_countFiles;

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

        $table = new Table($output);
        $table->setHeaders(array('Filepath', 'Disk Usage (Mb)'));

        $this->_countFiles = 0;
        $this->_filesSize =0;

        foreach (new RecursiveIteratorIterator($directoryIterator) as $file) {
            // Remove cache folder for performance.
            if (strpos($file, "/cache") !== false || is_dir($file)) {
                continue;
            }
            // Input option: --limit=XXX
            if ($this->_countFiles < $input->getOption('limit')) {
                $filePath = str_replace($imageDir, "", $file);
                $this->_countFiles++;
                $this->_filesSize += filesize($file);
                $table->addRow(array($filePath, number_format($file->getSize() / 1024 / 1024, '2')));
            }
        }
        $table->addRows(array(
                new TableSeparator(),
                array('<info>'.$this->_countFiles.' files </info>', '<info>'.number_format($this->_filesSize / 1024 / 1024, '2').' MB Total</info>'),
        ));
        $table->render();
    }
}
