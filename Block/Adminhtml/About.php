<?php

namespace Cap\CleanMedia\Block\Adminhtml;

use Cap\CleanMedia\Helper\Data;
use Magento\Framework\View\Element\Template;

class About extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * About constructor.
     *
     * @param Template\Context $context
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * @return mixed
     */
    public function moduleVersion()
    {
        return $this->helper->getModuleVersion();
    }
}
