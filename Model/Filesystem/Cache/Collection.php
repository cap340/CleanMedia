<?php

namespace Cap\CleanMedia\Model\Filesystem\Cache;

use Exception;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;

class Collection extends \Magento\Framework\Data\Collection\Filesystem
{
    /**
     * @var WriteInterface
     */
    protected $mediaDirectory;

    /**
     * Folder, where all media are stored
     *
     * @var string
     */
    protected $path = 'catalog/product/cache';

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Collection constructor.
     *
     * @param EntityFactoryInterface $entityFactory
     * @param Filesystem $filesystem
     * @throws FileSystemException
     * @throws Exception
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        Filesystem $filesystem
    ) {
        parent::__construct($entityFactory);
        $this->filesystem = $filesystem;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->mediaDirectory->create($this->path);
        $path = rtrim($this->mediaDirectory->getAbsolutePath($this->path), '/') . '/';
        $this->addTargetDir($path)->setCollectRecursively(true);
    }

    /**
     * Generate item row basing on the filename
     *
     * Keep only 'basename', rest could be customized
     *
     * @param string $filename
     * @return array
     * @throws Exception
     */
    protected function _generateRow($filename)
    {
        return [
            'path' => $filename,
            'basename' => basename($filename),
        ];
    }
}
