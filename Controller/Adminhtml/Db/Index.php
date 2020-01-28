<?php

namespace Cap\CleanMedia\Controller\Adminhtml\Db;

use Cap\CleanMedia\Model\ResourceModel\Db;
use Exception;
use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

class Index extends Action
{
    /**
     * @var Db
     */
    protected $resourceDb;

    /**
     * Index constructor.
     *
     * @param Action\Context $context
     * @param Db $resourceDb
     */
    public function __construct(
        Action\Context $context,
        Db $resourceDb
    ) {
        parent::__construct($context);
        $this->resourceDb = $resourceDb;
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $count = $this->resourceDb->getValuesToRemoveCount();
        if (!$count) {
            $this->messageManager->addErrorMessage(__('There is nothing to remove in the database.'));
        } else {
            try {
                $this->resourceDb->deleteDbValuesToRemove();
                $this->messageManager->addSuccessMessage(
                    __('A total of %1 record(s) have been deleted.', $count)
                );
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        $this->_redirect('cleanmedia/index/index');
    }
}
