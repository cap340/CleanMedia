<?php

namespace Cap\CleanMedia\Ui\DataProvider;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\FileSystemException;
use Magento\Ui\DataProvider\AbstractDataProvider;

class UnusedMediaProvider extends AbstractDataProvider
{
    /**
     * @var \Magento\Ui\DataProvider\AddFieldToCollectionInterface[]
     */
    protected $addFieldStrategies;

    /**
     * @var \Magento\Ui\DataProvider\AddFilterToCollectionInterface[]
     */
    protected $addFilterStrategies;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * Construct
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param Http $request
     * @param CleanMedia $cleanMedia
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        Http $request,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->request = $request;
    }

    /**
     * Get data
     *
     * @return array
     * @throws FileSystemException
     */
    public function getData()
    {
        $items = [
            ['filename_id' => "1", 'filename' => 'filename_1'],
            ['filename_id' => "2", 'filename' => 'filename_2'],
            ['filename_id' => "3", 'filename' => 'filename_3'],
            ['filename_id' => "4", 'filename' => 'filename_4'],
            ['filename_id' => "5", 'filename' => 'filename_5'],
            ['filename_id' => "6", 'filename' => 'filename_6'],
            ['filename_id' => "7", 'filename' => 'filename_7'],
            ['filename_id' => "8", 'filename' => 'filename_8'],
            ['filename_id' => "9", 'filename' => 'filename_9'],
            ['filename_id' => "10", 'filename' => 'filename_10'],
            ['filename_id' => "11", 'filename' => 'filename_11'],
            ['filename_id' => "12", 'filename' => 'filename_12'],
            ['filename_id' => "13", 'filename' => 'filename_13'],
            ['filename_id' => "14", 'filename' => 'filename_14'],
            ['filename_id' => "15", 'filename' => 'filename_15'],
            ['filename_id' => "16", 'filename' => 'filename_16'],
            ['filename_id' => "17", 'filename' => 'filename_17'],
            ['filename_id' => "18", 'filename' => 'filename_18'],
            ['filename_id' => "19", 'filename' => 'filename_19'],
            ['filename_id' => "20", 'filename' => 'filename_20'],
            ['filename_id' => "21", 'filename' => 'filename_21'],
            ['filename_id' => "22", 'filename' => 'filename_22'],
            ['filename_id' => "23", 'filename' => 'filename_23'],
            ['filename_id' => "24", 'filename' => 'filename_24'],
            ['filename_id' => "25", 'filename' => 'filename_25'],
            ['filename_id' => "26", 'filename' => 'filename_26'],
            ['filename_id' => "27", 'filename' => 'filename_27'],
        ];
        $pageSize = (int)$this->request->getParam('paging')['pageSize'];
        $current = (int)$this->request->getParam('paging')['current'];
        $pageOffset = ($current - 1) * $pageSize;

        return [
            'totalRecords' => count($items),
            'items' => array_slice($items, $pageOffset, $pageOffset + $pageSize),
        ];
    }

    public function setLimit($offset, $size)
    {
    }

    public function addOrder($field, $direction)
    {
    }

    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
    }
}
