<?php

namespace Cap\CleanMedia\Model;

use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;

/**
 * Main module class
 */
class CleanMedia extends DataObject
{
    /**
     * Object attributes
     *
     * @var array
     */
    protected $_data = [];

    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * CleanMedia constructor.
     *
     * @param DataObjectFactory $dataObjectFactory
     * @param array $data
     */
    public function __construct(
        DataObjectFactory $dataObjectFactory,
        array $data = []
    ) {
        $this->_data = $data;
        parent::__construct($data = []);
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * @return array|DataObject
     */
    public function collectUnusedMedia()
    {
        $items = [
            [
                'id' => 'id_1',
                'name' => 'name_1',
                'path' => 'path_1',
                'size' => 'size_1',
                'ctime' => 'ctime_1'
            ],
            [
                'id' => 'id_2',
                'name' => 'name_2',
                'path' => 'path_2',
                'size' => 'size_2',
                'ctime' => 'ctime_2'
            ],
            [
                'id' => 'id_3',
                'name' => 'name_3',
                'path' => 'path_3',
                'size' => 'size_3',
                'ctime' => 'ctime_3'
            ],
            [
                'id' => 'id_4',
                'name' => 'name_4',
                'path' => 'path_4',
                'size' => 'size_4',
                'ctime' => 'ctime_4'
            ],
        ];
        $this->_data = $this->dataObjectFactory->create();
        $this->_data->addData($items);

        return $this->_data;
    }
}
