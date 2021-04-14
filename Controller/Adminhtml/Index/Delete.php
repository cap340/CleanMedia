<?php

namespace Cap\CleanMedia\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Filesystem\Driver\File;

class Delete extends Action
{
    /**
     * @var File
     */
    protected $driverFile;

    /**
     * Delete constructor.
     *
     * @param Context $context
     * @param File $driverFile
     */
    public function __construct(Context $context, File $driverFile)
    {
        $this->driverFile = $driverFile;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function execute()
    {
        $path = $this->getRequest()->getParam('path');
        $basename = $this->getRequest()->getParam('basename');
        if ($this->driverFile->isExists($path)) {
            $this->driverFile->deleteFile($path);
            $this->messageManager->addSuccessMessage(
                __('%1 has been deleted.', $basename)
            );
        }

        $this->_redirect('*/*/index');
    }
}
