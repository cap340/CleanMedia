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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CleanMedia extends Command
{
    /* @var Filesystem */
    protected $_filesystem;
    /* @var ResourceConnection */
    protected $_resource;

    private $_countFiles;
    private $_imageDir;

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
        $limit = $input->getOption('limit');

        $directoryIterator = new RecursiveDirectoryIterator($this->_imageDir);

        $coreRead = $this->_resource->getConnection('core_read');
        $table1   = $this->_resource->getTableName('catalog_product_entity_media_gallery_value_to_entity');
        $table2   = $this->_resource->getTableName('catalog_product_entity_media_gallery');

        // Query images still used by products in database.
        $queryImagesInDb = "SELECT $table2.value"
                ." FROM $table1, $table2"
                ." WHERE $table1.value_id=$table2.value_id";
        $imagesInDbPath  = $coreRead->fetchCol($queryImagesInDb);

        $imagesInDbName = [];
        foreach ($imagesInDbPath as $item) {
            $imagesInDbName [] = preg_replace('/^.+[\\\\\\/]/', '', $item);
        }

        $this->_countFiles = 0;
        foreach (new RecursiveIteratorIterator($directoryIterator) as $file) {
            // Remove cache folder for performance.
            if (strpos($file, "/cache") !== false || is_dir($file)) {
                continue;
            }
            // todo: Iterator
            //  we take fileName to compare with imagesInDbName array and exclude
            //  we use file(full path) to unlink()
            //  we use str_replace to find the relative path (like entries in db we want to remove)
            $fileName = $file->getFilename();
            if ( ! in_array($fileName, $imagesInDbName)) {

                // todo: avoid code duplicate
                //  add method removeImageEntries($file) with $file as variable
                //  handle the --dry-run option

                // --limit=XXX option
                if ($limit) {
                    if ($this->_countFiles < $limit) {
                        $this->removeImageEntries($file);
                    }
                } else {
                    $this->removeImageEntries($file);
                }
            }
        }
        echo $this->_countFiles;
        echo PHP_EOL;
    }

    protected function removeImageEntries($file)
    {
        $this->_countFiles++;
        // todo: action unlink => delete file from media folder
        echo 'unlink(): '.$file;
        echo PHP_EOL;
        // todo:
        //  use $file = fullPath,
        //  remove $imageDir = /var/www/prod/pub/media/catalog/product
        $fileRelativePath = str_replace($this->_imageDir, "", $file);
        // todo: action db => remove value from db
        //  query: select from table2 where value == $fileRelativePath
        //  remove all db entries (each images as a lot of entries in db like small, thumb etc...)
        echo 'db: '.$fileRelativePath;
        echo PHP_EOL;

    }

}
