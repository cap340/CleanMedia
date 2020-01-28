<?php

namespace Cap\CleanMedia\Controller\Adminhtml\Index;

use Exception;

class Delete extends \Cap\CleanMedia\Controller\Adminhtml\Index
{
    /**
     * @inheritDoc
     */
    public function execute()
    {
        $path = $this->getRequest()->getParam('path');
        $basename = $this->getRequest()->getParam('basename');
        try {
            if ($this->driverFile->isExists($path)) {
                $this->driverFile->deleteFile($path);
            }
            $this->messageManager->addSuccessMessage(
                __('%1 has been deleted.', $basename)
            );
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        $this->_redirect('*/*/index');
    }
}
