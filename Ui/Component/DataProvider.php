<?php

namespace Cap\CleanMedia\Ui\Component;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\FileSystemException;
use Magento\Ui\DataProvider\AbstractDataProvider;

class DataProvider extends AbstractDataProvider
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
