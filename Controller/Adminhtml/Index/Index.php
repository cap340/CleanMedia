<?php

namespace Cap\CleanMedia\Controller\Adminhtml\Index;

use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

class Index extends \Cap\CleanMedia\Controller\Adminhtml\Index
{
    /**
     * @return Page|ResponseInterface|ResultInterface
     */
    public function execute()
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Cap_CleanMedia::cleanmedia');
        $resultPage->addBreadcrumb(__('Cap'), __('Cap'));
        $resultPage->addBreadcrumb(__('Manage Unused Media'), __('Manage Unused Media'));
        $resultPage->getConfig()->getTitle()->prepend(__('Clean Media'));

        return $resultPage;
    }
}
