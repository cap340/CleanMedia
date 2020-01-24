<?php

namespace Cap\CleanMedia\Controller\Adminhtml\Index;

use Magento\Framework\Exception\FileSystemException;

class MassDelete extends \Cap\CleanMedia\Controller\Adminhtml\Index
{
    /**
     * @inheritDoc
     * @throws FileSystemException
     */
    public function execute()
    {
        //todo: success or failure notification
        $mediaIds = $this->getRequest()->getParams();
        $paths = $mediaIds['selected'];
        foreach ($paths as $path) {
            if ($this->driverFile->isExists($path)) {
                $this->driverFile->deleteFile($path);
            }
        }

        return $this->_redirect('cleanmedia/*/index');
    }
}
