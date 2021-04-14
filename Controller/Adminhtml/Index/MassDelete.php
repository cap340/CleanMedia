<?php

namespace Cap\CleanMedia\Controller\Adminhtml\Index;

use Cap\CleanMedia\Model\Filesystem\Collection;
use Cap\CleanMedia\Model\Filesystem\CollectionFactory;
use Cap\CleanMedia\Model\ResourceModel\Product\Image as ResourceImage;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Ui\Component\MassAction\Filter;

class MassDelete extends Action
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var File
     */
    protected $driverFile;

    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var ResourceImage
     */
    protected $resourceImage;

    /**
     * MassDelete constructor.
     *
     * @param Context $context
     * @param Filter $filter
     * @param File $driverFile
     * @param CollectionFactory $collectionFactory
     * @param ResourceImage $resourceImage
     */
    public function __construct(
        Context $context,
        Filter $filter,
        File $driverFile,
        CollectionFactory $collectionFactory,
        ResourceImage $resourceImage
    ) {
        $this->filter = $filter;
        $this->driverFile = $driverFile;
        $this->collection = $collectionFactory->create();
        $this->resourceImage = $resourceImage;
        parent::__construct($context);
    }

    /**
     * Mass Delete Actions.
     *
     * Returns ['excluded'] or ['selected'] key with grid $ids
     * 1) case 'selected' : delete = selected
     * 2) case 'excluded' : delete = collection - excluded
     * 3) case ALL returns 'excluded' with empty value
     *
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function execute()
    {
        $inDb = $this->resourceImage->getAllProductImagesName()->toArray();
        $this->collection->addFieldToFilter('basename', [['nin' => $inDb]]);

        $items = $this->collection->getColumnValues('path');

        $ids = $this->getRequest()->getParams();
        $delete = [];

        if (! is_array($ids)) {
            $this->messageManager->addErrorMessage(__('Please select one or more media.'));
        } else {
            if (array_key_exists('selected', $ids)) { // Case with 'selected'
                $delete = $ids['selected'];
            } elseif (array_key_exists('excluded', $ids)) { // Case with 'excluded'
                $keep = $ids['excluded'];
                if (! is_array($keep)) { // Case ALL selected
                    $delete = $items;
                } else {
                    $delete = array_diff(
                        array_merge($items, $keep),
                        array_intersect($items, $keep)
                    );
                }
            }
            foreach ($delete as $item) {
                $this->driverFile->deleteFile($item);
            }
            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been deleted.', count($delete))
            );
        }

        $this->_redirect('*/*/index');
    }
}
