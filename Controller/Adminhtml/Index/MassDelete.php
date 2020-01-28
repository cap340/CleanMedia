<?php

namespace Cap\CleanMedia\Controller\Adminhtml\Index;

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Ui\Component\MassAction\Filter;
use Cap\CleanMedia\Model\Filesystem\CollectionFactory;
use Cap\CleanMedia\Model\ResourceModel\Db;

class MassDelete extends \Cap\CleanMedia\Controller\Adminhtml\Index
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Db
     */
    protected $resourceDb;

    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        FileFactory $fileFactory,
        File $driverFile,
        Filter $filter,
        CollectionFactory $collectionFactory,
        Db $resourceDb
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->resourceDb = $resourceDb;
        parent::__construct($context, $coreRegistry, $resultPageFactory, $fileFactory, $driverFile);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $collection = $this->collectionFactory->create();
        $inDb = $this->resourceDb->getMediaInDbNames()->toArray();
        $collection->addFieldToFilter('basename', [['nin' => $inDb]]);

        $items = $collection->getColumnValues('path');

//        echo '<pre>';
//        $ids = $this->getRequest()->getParam('excluded', []);
//        if (!is_array($ids)) {
//            echo 'no values';
//        } else {
//            echo 'values';
//        }
//        print_r($ids);

        $ids = $this->getRequest()->getParams();
        $itemsToDelete = [];

        /**
         * massactions returns ['excluded'] or ['selected'] key in $ids
         */
        if (array_key_exists('selected', $ids)) {
            $itemsToDelete = $ids['selected'];
        } elseif (array_key_exists('excluded', $ids)) { //case excluded : toDelete = collection - excluded
            $itemsToKeep = $ids['excluded'];
            if (!is_array($itemsToKeep)) {
                $itemsToDelete = $items;
            } else {
                $itemsToDelete = array_diff(array_merge($items, $itemsToKeep), array_intersect($items, $itemsToKeep));
            }

        }
        echo count($itemsToDelete);
        echo '<pre>';
        print_r($itemsToDelete);
    }

//    /**
//     * @inheritDoc
//     */
//    public function _execute()
//    {
//        $mediaIds = $this->getRequest()->getParams();
//        if (!is_array($mediaIds)) {
//            $this->messageManager->addErrorMessage(__('Please select one or more medias.'));
//        } else {
//            try {
//                $paths = $mediaIds['selected'];
//                $mediaDeleted = 0;
//                foreach ($paths as $path) {
//                    if ($this->driverFile->isExists($path)) {
//                        $this->driverFile->deleteFile($path);
//                        $mediaDeleted++;
//                    }
//                }
//                $this->messageManager->addSuccessMessage(
//                    __('A total of %1 record(s) have been deleted.', $mediaDeleted)
//                );
//            } catch (Exception $e) {
//                $this->messageManager->addErrorMessage($e->getMessage());
//            }
//        }
//
//        $this->_redirect('*/*/index');
//    }
}
