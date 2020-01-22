<?php

namespace Cap\CleanMedia\Model;

use Exception;
use FilesystemIterator;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Phrase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Main module class
 */
class CleanMedia extends DataObject
{
    /**
     * Folder, where all backups are stored
     *
     * @var string
     */
    protected $mediaPath = 'import';

    /**
     * Object attributes
     *
     * @var array
     */
    protected $_data = [];

    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * Filesystem
     *
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * File driver
     *
     * @var File
     */
    protected $driverFile;

    /**
     * CleanMedia constructor.
     *
     * @param DataObjectFactory $dataObjectFactory
     * @param Filesystem $filesystem
     * @param File $driverFile
     * @param array $data
     */
    public function __construct(
        DataObjectFactory $dataObjectFactory,
        Filesystem $filesystem,
        File $driverFile,
        array $data = []
    ) {
        $this->_data = $data;
        parent::__construct($data = []);
        $this->dataObjectFactory = $dataObjectFactory;
        $this->filesystem = $filesystem;
        $this->driverFile = $driverFile;
    }

    /**
     * Collect unused medias
     *
     * Recursively read the media directory excluding cache,
     * remove medias already in db & placeholders,
     * collect filename info in [_data:protected] array for the Ui DataProvider.
     *
     * @return array
     * @throws FileSystemException
     */
    public function collectUnusedMedia()
    {
        $path = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath() . $this->mediaPath;
        $result = [];
        $count = 0;
        $flags = FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS;
        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, $flags),
                RecursiveIteratorIterator::CHILD_FIRST
            );
            /** @var FilesystemIterator $file */
            foreach ($iterator as $file) {
                if (!strpos($file, "/cache") === false) {
                    continue;
                }
                if ($this->driverFile->isDirectory($file)) {
                    continue;
                }
                $count++;
                $result[] = [
                    'id' => (int)$count,
                    'path' => $file->getPathname(),
                    'name' => $file->getFilename(),
                    'size' => $file->getSize(),
                    'ctime' => $file->getCTime()
                ];
            }
        } catch (Exception $e) {
            throw new FileSystemException(new Phrase($e->getMessage()), $e);
        }

        $this->_data = $this->dataObjectFactory->create();
        $this->_data->addData($result);

        return $this->_data;
    }
}
