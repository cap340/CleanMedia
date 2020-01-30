<?php

namespace Cap\CleanMedia\Controller\Adminhtml\Index;

use Cap\CleanMedia\Model\Filesystem\CollectionFactory;
use Cap\CleanMedia\Model\ResourceModel\Db;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Ui\Component\MassAction\Filter;

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

    /**
     * MassDelete constructor.
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param FileFactory $fileFactory
     * @param File $driverFile
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param Db $resourceDb
     */
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
     * @return ResponseInterface|ResultInterface|void
     * @throws LocalizedException
     */
    public function execute()
    {
        $collection = $this->collectionFactory->create();
        $inDb = $this->resourceDb->getMediaInDbNames()->toArray();
        $collection->addFieldToFilter('basename', [['nin' => $inDb]]);

        $items = $collection->getColumnValues('path');

        $ids = $this->getRequest()->getParams();
        $itemsToDelete = [];

        if (!is_array($ids)) {
            $this->messageManager->addErrorMessage(__('Please select one or more media.'));
        } else {
            /**
             * massactions returns ['excluded'] or ['selected'] key in $ids
             */
            if (array_key_exists('selected', $ids)) {
                $itemsToDelete = $ids['selected'];
            } elseif (array_key_exists('excluded', $ids)) { //case excluded : toDelete = collection - excluded
                $itemsToKeep = $ids['excluded'];
                if (!is_array($itemsToKeep)) { //select all returns 'excluded' with empty value
                    $itemsToDelete = $items;
                } else {
                    $itemsToDelete = array_diff(
                        array_merge($items, $itemsToKeep),
                        array_intersect($items, $itemsToKeep)
                    );
                }
            }
            foreach ($itemsToDelete as $item) {
                $this->driverFile->deleteFile($item);
            }
            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been deleted.', count($itemsToDelete))
            );
        }

        $this->_redirect('*/*/index');
    }

    /**
     * Acl authorization
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Cap_CleanMedia::delete');
    }
}
