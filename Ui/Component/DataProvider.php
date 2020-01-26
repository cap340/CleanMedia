<?php

namespace Cap\CleanMedia\Ui\Component;

use Cap\CleanMedia\Model\ResourceModel\Db;
use Cap\CleanMedia\Model\Filesystem\Collection;
use Cap\CleanMedia\Model\Filesystem\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Ui\DataProvider\AddFieldToCollectionInterface;
use Magento\Ui\DataProvider\AddFilterToCollectionInterface;

/**
 * Ui Grid data provider
 *
 */
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
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var Db
     */
    protected $resourceDb;

    /**
     * DataProvider constructor.
     *
     * @param $name
     * @param $primaryFieldName
     * @param $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param Db $resourceDb
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        Db $resourceDb,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->resourceDb = $resourceDb;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        if (!$this->collection->isLoaded()) {
            $this->collection->load();
        }

        return $this->collection->toArray();
    }

    /**
     * Return filtered collection
     *
     * @return Collection
     */
    public function getCollection(): Collection
    {
        $inDbNames = $this->resourceDb->getMediaInDbNames()->toArray();
        return $this->collection->addFieldToFilter('basename', [['nin' => $inDbNames]]);
    }
}
