<?php

namespace Cap\CleanMedia\Cron;

use Cap\CleanMedia\Model\ResourceModel\Db;
use FilesystemIterator;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use Psr\Log\LoggerInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class CleanMedia
{
    /**
     * Folder, where all media are stored
     *
     * @var string
     */
    protected $path = 'catalog/product';
    /**
     * @var Filesystem\Directory\ReadInterface
     */
    protected $mediaDirectory;
    /**
     * @var File
     */
    protected $driverFile;
    /**
     * @var Db
     */
    protected $resourceDb;
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * CleanMedia constructor.
     *
     * @param LoggerInterface $logger
     * @param Filesystem $filesystem
     * @param File $driverFile
     * @param Db $resourceDb
     */
    public function __construct(
        LoggerInterface $logger,
        Filesystem $filesystem,
        File $driverFile,
        Db $resourceDb
    ) {
        $this->mediaDirectory = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $this->driverFile     = $driverFile;
        $this->resourceDb     = $resourceDb;
        $this->logger         = $logger;
    }

    /**
     * Execute cleanmedia
     */
    public function execute()
    {
        $mediaPath = $this->mediaDirectory->getAbsolutePath() . $this->path;
        $iterator  = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $mediaPath,
                FilesystemIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS
            )
        );

        $inDb = $this->resourceDb->getMediaInDbNames()->toArray();
        foreach ($iterator as $file) {
            if (strpos($file, "/cache") !== false) {
                continue;
            }
            $filename = $file->getFilename();
            if (! in_array($filename, $inDb)) {
                try {
                    $this->driverFile->deleteFile($file);
                } catch (FileSystemException $e) {
                    $this->logger->error($e->getMessage());
                }
            }
        }
    }
}
