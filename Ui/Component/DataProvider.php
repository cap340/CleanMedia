<?php

namespace Cap\CleanMedia\Ui\Component;

use Cap\CleanMedia\Model\Filesystem\Collection;
use Cap\CleanMedia\Model\Filesystem\CollectionFactory;
use Cap\CleanMedia\Model\ResourceModel\Product\Image as ResourceImage;
use Magento\Ui\DataProvider\AbstractDataProvider;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var ResourceImage
     */
    protected $resourceImage;

    /**
     * DataProvider constructor.
     *
     * @param $name
     * @param $primaryFieldName
     * @param $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param ResourceImage $resourceImage
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        ResourceImage $resourceImage,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->resourceImage = $resourceImage;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        if ( ! $this->collection->isLoaded()) {
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
        $result = $this->resourceImage->getAllProductImagesName()->toArray();

        return $this->collection->addFieldToFilter('basename', [['nin' => $result]]);
    }
}
