<?php

namespace Cap\CleanMedia\Ui\Component;

use Cap\CleanMedia\Model\CleanMedia;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\FileSystemException;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Ui\DataProvider\AddFieldToCollectionInterface;
use Magento\Ui\DataProvider\AddFilterToCollectionInterface;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var AddFieldToCollectionInterface[]
     */
    protected $addFieldStrategies;

    /**
     * @var AddFilterToCollectionInterface[]
     */
    protected $addFilterStrategies;

    /**
     * @var Http
     */
    protected $request;

    /**
     * @var CleanMedia
     */
    protected $cleanMedia;

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
        CleanMedia $cleanMedia,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->request = $request;
        $this->cleanMedia = $cleanMedia;
    }

    /**
     * Get data
     *
     * @return array
     * @throws FileSystemException
     */
    public function getData()
    {
        $this->cleanMedia->collectUnusedMedia();
        $items = $this->cleanMedia->getData()->toArray();
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
