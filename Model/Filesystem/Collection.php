<?php

namespace Cap\CleanMedia\Model\Filesystem;

use DateTime;
use Exception;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;

/**
 * Filesystem collection
 *
 */
class Collection extends \Magento\Framework\Data\Collection\Filesystem
{
    /**
     * Exclude cache folder
     *
     * @var string
     */
    protected $_allowedDirsMask = '/^(?!cache|(?!([^.]))).*/';

    /**
     * @var WriteInterface
     */
    protected $mediaDirectory;

    /**
     * Folder, where all media are stored
     *
     * @var string
     */
    protected $_path = 'catalog/product';

    /**
     * @var Filesystem
     */
    protected $_filesystem;

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
        $this->_filesystem = $filesystem;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->mediaDirectory->create($this->_path);
        $path = rtrim($this->mediaDirectory->getAbsolutePath($this->_path), '/') . '/';
        $this->addTargetDir($path)->setCollectRecursively(true);
    }

    /**
     * Add order to sort the collection in listing ui_component
     *
     * @param $field
     * @param $direction
     * @return \Magento\Framework\Data\Collection
     */
    public function addOrder($field, $direction)
    {
        return $this->setOrder($field, $direction);
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
        $ctime = $this->mediaDirectory->stat($this->mediaDirectory->getRelativePath($filename))['ctime'];
        $upload_date = DateTime::createFromFormat("U", $ctime)->format('Y-m-d H:i:s');
        //todo: fix The use of function basename() is discouraged
        return [
            'path' => $filename,
            'basename' => basename($filename),
            'size' => $this->mediaDirectory->stat($this->mediaDirectory->getRelativePath($filename))['size'],
            'upload' => $upload_date,
            'relativePath' => $this->mediaDirectory->getRelativePath($filename)
        ];
    }
}
