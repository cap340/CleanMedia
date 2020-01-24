<?php

namespace Cap\CleanMedia\Controller\Adminhtml\Index;

use Exception;

class MassDelete extends \Cap\CleanMedia\Controller\Adminhtml\Index
{
    /**
     * @inheritDoc
     */
    public function execute()
    {
        $mediaIds = $this->getRequest()->getParams();
        if (!is_array($mediaIds)) {
            $this->messageManager->addErrorMessage(__('Please select one or more medias.'));
        } else {
            try {
                $paths = $mediaIds['selected'];
                $mediaDeleted = 0;
                foreach ($paths as $path) {
                    if ($this->driverFile->isExists($path)) {
                        $this->driverFile->deleteFile($path);
                        $mediaDeleted++;
                    }
                }
                $this->messageManager->addSuccessMessage(
                    __('A total of %1 record(s) have been deleted.', $mediaDeleted)
                );
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }
}
