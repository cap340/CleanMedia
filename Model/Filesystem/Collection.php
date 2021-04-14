<?php

namespace Cap\CleanMedia\Model\Filesystem;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\Data\Collection\Filesystem;

class Collection extends Filesystem
{
    /**
     * Exclude cache folder
     *
     * @var string
     */
    protected $_allowedDirsMask = '/^(?!cache|(?!([^.]))).*/';

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $mediaDirectory;

    /**
     * Directory, where all media are stored.
     *
     * @var string
     */
    protected $path = 'catalog/product';

    /**
     * @param EntityFactoryInterface $_entityFactory
     * @param \Magento\Framework\Filesystem $filesystem
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Exception
     */
    public function __construct(
        EntityFactoryInterface $_entityFactory,
        \Magento\Framework\Filesystem $filesystem
    ) {
        parent::__construct($_entityFactory, $filesystem);
        $this->filesystem = $filesystem;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->mediaDirectory->create($this->path);
        $path = rtrim($this->mediaDirectory->getAbsolutePath($this->path), '/') . '/';
        $this->addTargetDir($path)->setCollectRecursively(true);
    }

    /**
     * Add order to sort the collection in listing ui_component
     *
     * @param $field
     * @param $direction
     *
     * @return \Magento\Framework\Data\Collection
     */
    public function addOrder($field, $direction): \Magento\Framework\Data\Collection
    {
        return $this->setOrder($field, $direction);
    }

    /**
     * Generate item row basing on the filename
     *
     * Keep only 'basename', rest could be customized
     *
     * @param string $filename
     *
     * @return array
     * @throws \Exception
     */
    protected function _generateRow($filename): array
    {
        $ctime = $this->mediaDirectory->stat($this->mediaDirectory->getRelativePath($filename))['ctime'];
        $upload_date = \DateTime::createFromFormat("U", $ctime)->format('Y-m-d H:i:s');

        return [
            'path' => $filename,
            'basename' => basename($filename), //phpcs:ignore
            'size' => $this->mediaDirectory->stat($this->mediaDirectory->getRelativePath($filename))['size'],
            'upload' => $upload_date,
            'relativePath' => $this->mediaDirectory->getRelativePath($filename)
        ];
    }
}
