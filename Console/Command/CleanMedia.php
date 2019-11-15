<?php

// todo: add --dry-run option to avoid double iteration & comment in CHANGELOG.md
// todo: add --limit=XXX option & comment in CHANGELOG.md
// todo: where should I put the limit option ?
// todo: update README.md
// todo: add command magento cap:clean:media --help in README.md for options

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
    private $_diskUsage;
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
                        'How many files should be deleted. Use with --limit=XXX'
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
        $limit    = $input->getOption('limit');
        if ( ! $isDryRun) {
            $output->writeln('WARNING: this is not a dry run. If you want to do a dry-run, add --dry-run.');
            $question              = new ConfirmationQuestion('<question>Are you sure you want to continue? [No]</question>', false);
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

        // Query images still used by products in db.
        $queryImagesInDb = "SELECT $table2.value"
                ." FROM $table1, $table2"
                ." WHERE $table1.value_id=$table2.value_id";
        $imagesInDbPath  = $coreRead->fetchCol($queryImagesInDb);
        // todo: check if path && name is necessary ?
        $imagesInDbName = [];
        foreach ($imagesInDbPath as $value) {
            $imagesInDbName [] = preg_replace('/^.+[\\\\\\/]/', '', $value);
        }

        $output->writeln('scanning media folder ('.$imageDir.')');
        $progressBar = new ProgressBar($output);
        $progressBar->setFormat('[%bar%] %elapsed:6s%');
        $progressBar->start();
        $table = new Table($output);
        $table->setHeaders(array('Count', 'Filepath', 'Disk Usage (Mb)'));

        $this->_countFiles = 0;
        $this->_diskUsage  = 0;
        foreach (new RecursiveIteratorIterator($directoryIterator) as $file) {
            // Remove cache folder for performance.
            if (strpos($file, "/cache") !== false || is_dir($file)) {
                continue;
            }
            $fileName = $file->getFilename();
            if ( ! in_array($fileName, $imagesInDbName)) {
                // handle the --limit=XXX option
                if ($limit) {
                    if ($this->_countFiles < $limit) {
                        $this->removeUnusedImages($imageDir, $file, $isDryRun, $table);
                    }
                } else {
                    $this->removeUnusedImages($imageDir, $file, $isDryRun, $table);
                }
                $progressBar->advance();
            }
        }
        $progressBar->finish();
        echo PHP_EOL;
        $table->addRows(array(
                new TableSeparator(),
                array(
                        '<info>'.$this->_countFiles.'</info>',
                        '<info>files</info>',
                        '<info>'.number_format($this->_diskUsage / 1024 / 1024, '2').' MB Total</info>',
                ),
        ));
        $table->render();
        $output->writeln('<info>Done...</info>');
    }

    /**
     * Remove Unused Images. Add this method to handle the --limit=XXX option
     * inside the recursive iterator foreach loop and avoid duplicate code issue.
     *
     * @param $imageDir
     * @param $file
     * @param $isDryRun
     * @param $table
     */
    protected function removeUnusedImages($imageDir, $file, $isDryRun, $table)
    {
        $fileName          = $file->getFilename();
        $filePath          = str_replace($imageDir, "", $file);
        $fileRealPath      = $file->getRealPath();
        $valuesToRemove [] = [
                'fileName'     => $fileName,
                'filePath'     => $filePath,
                'fileRealPath' => $fileRealPath,
        ];
        $this->_countFiles++;
        $this->_diskUsage += filesize($file);

        // handle the --dry-run option
        if ( ! $isDryRun) {
            $table->addRow(array($this->_countFiles, $filePath, number_format($file->getSize() / 1024 / 1024, '2')));
            // unlink($file);
            // todo: remove database entry
        } else {
            $dryRunNotice = preg_filter('/^/', 'DRY_RUN -- ', $filePath);
            $table->addRow(array($this->_countFiles, $dryRunNotice, number_format($file->getSize() / 1024 / 1024, '2')));
        }
    }

}
