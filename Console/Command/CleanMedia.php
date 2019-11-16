<?php

// fixme: database entries part very slow !!
// fixme: 12.000 files / 35.000 db entries => 8min VS 9s without removing db entries !!!

namespace Cap\CleanMedia\Console\Command;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Filesystem;
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
    /* @var Filesystem */
    protected $_filesystem;
    /* @var ResourceConnection */
    protected $_resource;

    private $_imageDir;
    private $_countFiles;
    private $_diskUsage;
    private $_consoleTable;

    /**
     * CleanMedia constructor.
     *
     * @param Filesystem         $filesystem
     * @param ResourceConnection $resource
     */
    public function __construct(
            Filesystem $filesystem,
            ResourceConnection $resource
    ) {
        $this->_filesystem = $filesystem;
        $this->_imageDir   = $this->_filesystem
                        ->getDirectoryRead(DirectoryList::MEDIA)
                        ->getAbsolutePath().'catalog'.DIRECTORY_SEPARATOR.'product';
        $this->_resource   = $resource;
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
                ->setName('cap:clean:media')
                ->setDescription('Remove images of deleted products in media folder & database entries')
                ->addOption(
                        'dry-run',
                        null,
                        InputOption::VALUE_NONE,
                        'Perform a dry-run to test the command: --dry-run'
                )
                ->addOption(
                        'limit',
                        null,
                        InputOption::VALUE_REQUIRED,
                        'How many files should be deleted: --limit=XXX'
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
            $helper   = $this->getHelper('question');
            $question = new ConfirmationQuestion('<question>Are you sure you want to continue? [No] </question>', false);
            if ( ! $helper->ask($input, $output, $question)) {
                return;
            }
        }

        $coreRead = $this->_resource->getConnection('core_read');
        $dbTable1 = $this->_resource->getTableName('catalog_product_entity_media_gallery_value_to_entity');
        $dbTable2 = $this->_resource->getTableName('catalog_product_entity_media_gallery');
        // Query images still used by products in database.
        $imagesInDb     = "SELECT $dbTable2.value"
                ." FROM $dbTable1, $dbTable2"
                ." WHERE $dbTable1.value_id=$dbTable2.value_id";
        $imagesInDbPath = $coreRead->fetchCol($imagesInDb);
        // Return images name of query to compare with media folder iteration.
        $imagesInDbName = [];
        foreach ($imagesInDbPath as $item) {
            $imagesInDbName [] = preg_replace('/^.+[\\\\\\/]/', '', $item);
        }

        $output->writeln('scanning media folder: '.$this->_imageDir.'');
        $this->_consoleTable = new Table($output);
        $this->_consoleTable->setHeaders(array('Count', 'Filepath', 'Disk Usage (Mb)'));
        $progressBar = new ProgressBar($output);
        $progressBar->setFormat('[%bar%] %elapsed:6s%');
        $progressBar->start();

        $this->_countFiles = 0;
        $this->_diskUsage  = 0;
        $directoryIterator = new RecursiveDirectoryIterator($this->_imageDir);

        foreach (new RecursiveIteratorIterator($directoryIterator) as $file) {
            // Exclude cache folder for performance.
            if (strpos($file, "/cache") !== false || is_dir($file)) {
                continue;
            }
            $fileName = $file->getFilename();
            if ( ! in_array($fileName, $imagesInDbName)) {
                // --limit=XXX option
                if ($limit) {
                    if ($this->_countFiles < $limit) {
                        $this->removeImageEntries($file, $isDryRun);
                    }
                } else {
                    $this->removeImageEntries($file, $isDryRun);
                }
                $progressBar->advance();
            }
        }
        $progressBar->finish();
        echo PHP_EOL;
        $this->_consoleTable->addRows(array(
                new TableSeparator(),
                array(
                        '<info>'.$this->_countFiles.'</info>',
                        '<info>files</info>',
                        '<info>'.number_format($this->_diskUsage / 1024 / 1024, '2').' MB Total</info>',
                ),
        ));
        $this->_consoleTable->render();
    }

    /**
     * Remove images entries in media folder & database.
     *
     * @param $file
     * @param $isDryRun --dry-run option
     */
    private function removeImageEntries($file, $isDryRun)
    {
        $this->_countFiles++;
        $this->_diskUsage += filesize($file);
        $fileRelativePath = str_replace($this->_imageDir, "", $file);
        // --dry-run option
        if ( ! $isDryRun) {
            $this->_consoleTable->addRow(array(
                    $this->_countFiles,
                    $fileRelativePath,
                    number_format($file->getSize() / 1024 / 1024, '2'),
            ));
            unlink($file);
            // Remove associated database entries.
            // fixme: very slow...
//            $coreRead = $this->_resource->getConnection('core_read');
//            $dbTable2 = $this->_resource->getTableName('catalog_product_entity_media_gallery');
//            $query = "DELETE FROM $dbTable2"
//                    ." WHERE $dbTable2.value = '".$fileRelativePath."'";
//            $coreRead->query($query);
        } else {
            $this->_consoleTable->addRow(array(
                    $this->_countFiles,
                    preg_filter('/^/', 'DRY_RUN -- ', $fileRelativePath),
                    number_format($file->getSize() / 1024 / 1024, '2'),
            ));
        }
    }

}
