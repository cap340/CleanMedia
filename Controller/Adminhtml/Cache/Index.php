<?php

namespace Cap\CleanMedia\Controller\Adminhtml\Cache;

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Cap\CleanMedia\Model\ResourceModel\Db;
use Cap\CleanMedia\Model\Filesystem\Cache\Collection;
use Cap\CleanMedia\Model\Filesystem\Cache\CollectionFactory;

class Index extends \Cap\CleanMedia\Controller\Adminhtml\Index
{
    /**
     * @var Db
     */
    protected $resourceDb;

    /**
     * @var Collection
     */
    protected $collection;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param FileFactory $fileFactory
     * @param File $driverFile
     * @param Db $resourceDb
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        FileFactory $fileFactory,
        File $driverFile,
        Db $resourceDb,
        CollectionFactory $collectionFactory
    ) {
        parent::__construct($context, $coreRegistry, $resultPageFactory, $fileFactory, $driverFile);
        $this->resourceDb = $resourceDb;
        $this->collection = $collectionFactory->create();
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $inDbNames = $this->resourceDb->getMediaInDbNames()->toArray();
        $collection = $this->collection->addFieldToFilter('basename', [['nin' => $inDbNames]]);

        $count = $collection->count();
        if (!$count) {
            $this->messageManager->addErrorMessage(__('There is nothing to delete in the cache folder.'));
        } else {
            try {
                $items = $collection->toArray()['items'];
                foreach ($items as $item) {
                    $this->driverFile->deleteFile($item['path']);
                }
                $this->messageManager->addSuccessMessage(
                    __('A total of %1 record(s) have been deleted.', $count)
                );
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        $this->_redirect('cleanmedia/index/index');
    }

    /**
     * Acl authorization
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Cap_CleanMedia::cache');
    }
}
