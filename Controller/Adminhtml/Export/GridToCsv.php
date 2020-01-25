<?php

namespace Cap\CleanMedia\Controller\Adminhtml\Export;

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Ui\Component\MassAction\Filter;

class GridToCsv extends \Magento\Backend\App\Action
{
    /**
     * Massactions filter
     *
     * @var Filter
     */
    protected $filter;

    /**
     * @var WriteInterface
     */
    protected $directory;

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * GridToCsv constructor.
     *
     * @param Context $context
     * @param Filter $filter
     * @param Filesystem $filesystem
     * @param FileFactory $fileFactory
     * @throws FileSystemException
     */
    public function __construct(
        Context $context,
        Filter $filter,
        Filesystem $filesystem,
        FileFactory $fileFactory
    ) {
        $this->filter = $filter;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->fileFactory = $fileFactory;
        parent::__construct($context);
    }

    /**
     * Export to csv
     *
     * @return ResponseInterface
     * @throws FileSystemException
     * @throws LocalizedException
     * @throws Exception
     */
    public function execute()
    {
        $component = $this->filter->getComponent();

        $this->filter->prepareComponent($component);
        $dataProvider = $component->getContext()->getDataProvider();
        $dataProvider->setLimit(0, false);
        $results = $component->getContext()->getDataProvider()->getData();
        $items = $results['items'];

        $name = hash('sha256', microtime());
        $filename = $component->getName() . $name . '.csv';
        $file = 'export/' . $filename;

        $this->directory->create('export');
        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();

        foreach ($items as $item) {
            $stream->writeCsv($item);
        }

        $stream->unlock();
        $stream->close();
        return $this->fileFactory->create(
            $filename,
            [
                'type' => 'filename',
                'value' => $file,
                'rm' => true
            ],
            'var'
        );
    }
}
