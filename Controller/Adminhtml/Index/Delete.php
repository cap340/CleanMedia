<?php

namespace Cap\CleanMedia\Controller\Adminhtml\Index;

use Magento\Framework\App\ResponseInterface;

class Delete extends \Cap\CleanMedia\Controller\Adminhtml\Index
{
    /**
     * @inheritDoc
     */
    public function execute()
    {
        $path = $this->getRequest()->getParam('id');
        echo $path;
    }
}
