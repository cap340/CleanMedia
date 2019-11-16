<?php

// todo: reflexion on naming convention for methods & variables
// todo: add --dry-run option to avoid double iteration & comment in CHANGELOG.md
// todo: add --limit=XXX option & comment in CHANGELOG.md
// todo: where should I put the limit option ?
// todo: update README.md
// todo: add command magento cap:clean:media --help in README.md for options

namespace Cap\CleanMedia\Console\Command;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Filesystem;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Console\Command\Command;
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
                ->setDescription('Remove images of deleted products in /media folder && database entries')
                ->addOption(
                        'dry-run',
                        null,
                        InputOption::VALUE_NONE,
                        'Perform a dry-run to test the command.'
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
            $helper   = $this->getHelper('question');
            $question = new ConfirmationQuestion('<question>Are you sure you want to continue? [No]</question>', false);
            if ( ! $helper->ask($input, $output, $question)) {
                return;
            }
        }

        // Query images still used by products in database.
        $coreRead = $this->_resource->getConnection('core_read');
        $dbTable1 = $this->_resource->getTableName('catalog_product_entity_media_gallery_value_to_entity');
        $dbTable2 = $this->_resource->getTableName('catalog_product_entity_media_gallery');
        $queryImagesInDb = "SELECT $dbTable2.value"
                ." FROM $dbTable1, $dbTable2"
                ." WHERE $dbTable1.value_id=$dbTable2.value_id";
        $imagesInDbPath  = $coreRead->fetchCol($queryImagesInDb);
        // Return images name of query to compare with media folder iteration.
        $imagesInDbName = [];
        foreach ($imagesInDbPath as $item) {
            $imagesInDbName [] = preg_replace('/^.+[\\\\\\/]/', '', $item);
        }

        $this->_consoleTable = new Table($output);
        $this->_consoleTable->setHeaders(array('Count', 'Filepath', 'Disk Usage (Mb)'));

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
            }
        }
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
     * Remove images entries in /media folder & database.
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
            // unlink($file);
            // todo: remove db entries.
            $this->_consoleTable->addRow(array($this->_countFiles, $fileRelativePath, number_format($file->getSize() / 1024 / 1024, '2')));
        } else {
            $dryRunNotice = preg_filter('/^/', 'DRY_RUN -- ', $fileRelativePath);
            $this->_consoleTable->addRow(array($this->_countFiles, $dryRunNotice, number_format($file->getSize() / 1024 / 1024, '2')));
        }

        // Remove associated database entries.
//        echo 'db: '.$fileRelativePath;
//        echo PHP_EOL;
//        $coreRead = $this->_resource->getConnection('core_read');
//        $dbTable2   = $this->_resource->getTableName('catalog_product_entity_media_gallery');
        // todo: test if this remove all entries or use LIKE ??
        // $query = "DELETE FROM $dbTable2 WHERE $dbTable2.value_id = $fileRelativePath";
        // $coreRead->query($query);
    }

}
