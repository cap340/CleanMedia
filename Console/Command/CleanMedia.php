<?php

// todo: add --dry-run option to avoid double iteration & comment in CHANGELOG.md
// todo: add --limit=XXX option & comment in CHANGELOG.md
// todo: update README.md

namespace Cap\CleanMedia\Console\Command;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

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
    private $_questionHelper;

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
                ->setName('cap:clean:media')
                ->setDescription('Remove images of deleted products in /media folder && database entries')
                ->addOption(
                        'dry-run',
                        null,
                        InputOption::VALUE_NONE,
                        'Perform a dry-run to test without deleting images'
                )
                ->addOption(
                        'limit',
                        null,
                        InputOption::VALUE_REQUIRED,
                        'How many files should be deleted?'
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
        $isDryRun = $input->getOption('dry-run');
        if ( ! $isDryRun) {
            $output->writeln('WARNING: this is not a dry run. If you want to do a dry-run, add --dry-run.');
            $question              = new ConfirmationQuestion('Are you sure you want to continue? [No] ', false);
            $this->_questionHelper = $this->getHelper('question');
            if ( ! $this->_questionHelper->ask($input, $output, $question)) {
                return;
            }
        }

        $objectManager     = ObjectManager::getInstance();
        $filesystem        = $objectManager->get('Magento\Framework\Filesystem');
        $directory         = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $imageDir          = $directory->getAbsolutePath().'catalog'.DIRECTORY_SEPARATOR.'product';
        $directoryIterator = new RecursiveDirectoryIterator($imageDir);
        $resource          = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $coreRead          = $resource->getConnection('core_read');
        $table1            = $resource->getTableName('catalog_product_entity_media_gallery_value_to_entity');
        $table2            = $resource->getTableName('catalog_product_entity_media_gallery');

        $queryImagesInDb = "SELECT $table2.value"
                ." FROM $table1, $table2"
                ." WHERE $table1.value_id=$table2.value_id";
        $imagesInDbPath  = $coreRead->fetchCol($queryImagesInDb);
        $imagesInDbName  = [];
        foreach ($imagesInDbPath as $value) {
            $imagesInDbName [] = preg_replace('/^.+[\\\\\\/]/', '', $value);
        }

        $progressBar = new ProgressBar($output);
        $progressBar->start();
        $table = new Table($output);
        $table->setHeaders(array('Filepath', 'Disk Usage (Mb)'));

        $valuesToRemove    = [];
        $this->_countFiles = 0;
        $this->_filesSize  = 0;

        foreach (new RecursiveIteratorIterator($directoryIterator) as $file) {
            // Remove cache folder for performance.
            if (strpos($file, "/cache") !== false || is_dir($file)) {
                continue;
            }
            $fileName     = $file->getFilename();
            $filePath     = str_replace($imageDir, "", $file);
            $fileRealPath = $file->getRealPath();
            if ( ! in_array($fileName, $imagesInDbName)) {
                $valuesToRemove [] = [
                        'fileName'     => $fileName,
                        'filePath'     => $filePath,
                        'fileRealPath' => $fileRealPath,
                ];
                $table->addRow(array($filePath, number_format($file->getSize() / 1024 / 1024, '2')));
            }
            $this->_countFiles++;
            $this->_filesSize += filesize($file);
            $progressBar->advance();
        }
        $progressBar->finish();
        echo PHP_EOL;

        $table->addRows(array(
                new TableSeparator(),
                array('<info>'.$this->_countFiles.' files </info>', '<info>'.number_format($this->_filesSize / 1024 / 1024, '2').' MB Total</info>'),
        ));
        $table->render();

//        $limit = $input->getOption('limit');
//
//        foreach (new RecursiveIteratorIterator($directoryIterator) as $file) {
//            // Remove cache folder for performance.
//            if (strpos($file, "/cache") !== false || is_dir($file)) {
//                continue;
//            }
//            $filePath = str_replace($imageDir, "", $file);
//            // Input option: --limit=XXX
//            if ($limit) {
//                if ($this->_countFiles < $limit) {
//                    $this->_countFiles++;
//                    $this->_filesSize += filesize($file);
//                    $rows[] = array($filePath, number_format($file->getSize() / 1024 / 1024, '2'));
//                }
//            } else {
//                $this->_countFiles++;
//                $this->_filesSize += filesize($file);
//                $rows[] = array($filePath, number_format($file->getSize() / 1024 / 1024, '2'));
//            }
//        }
//
//        $table->setRows($rows);
//        $table->addRows(array(
//            new TableSeparator(),
//            array('<info>' . $this->_countFiles . ' files </info>', '<info>' . number_format($this->_filesSize / 1024 / 1024, '2') . ' MB Total</info>'),
//        ));
//        $table->render();
    }

}
