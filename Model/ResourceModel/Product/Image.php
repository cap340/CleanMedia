<?php

namespace Cap\CleanMedia\Model\ResourceModel\Product;

use Magento\Framework\App\ResourceConnection;

class Image
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * Image constructor.
     *
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
        $this->connection = $this->resourceConnection->getConnection();
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    public function getAllProductImagesName(): \Magento\Framework\DataObject
    {
        $items = array_filter(array_merge($this->getAllProductImages(), $this->getPlaceholders()));
        $object = new \Magento\Framework\DataObject();
        foreach ($items as $key => $item) {
            $object[$key] = preg_replace('/^.+[\\\\\\/]/', '', $item);
        }

        return $object;
    }

    /**
     * Get images used by product.
     *
     * select value from 'catalog_product_entity_media_gallery'
     * where value_id are in 'catalog_product_entity_media_gallery_value_to_entity'
     *
     * @return array
     */
    private function getAllProductImages(): array
    {
        $sql = $this->connection->select()
                                ->from(['gallery' => 'catalog_product_entity_media_gallery'])
                                ->join(
                                    ['to_entity' => 'catalog_product_entity_media_gallery_value_to_entity'],
                                    'gallery.value_id = to_entity.value_id'
                                )
                                ->reset(\Zend_Db_Select::COLUMNS)
                                ->columns('value');

        return $this->connection->fetchCol($sql);
    }

    /**
     * Get placeholders value from core_config_data
     *
     * @return array
     */
    private function getPlaceholders(): array
    {
        $sql = $this->connection->select()
                                ->from('core_config_data')
                                ->where('core_config_data.path LIKE "%placeholder%"')
                                ->reset(\Zend_Db_Select::COLUMNS)
                                ->columns('value');

        return $this->connection->fetchCol($sql);
    }

    /**
     * @return \Magento\Framework\DB\Select
     */
    public function getUnusedImagesInDb(): \Magento\Framework\DB\Select
    {
        return $this->connection->select()
            ->from(['gallery' => 'catalog_product_entity_media_gallery'])
            ->joinLeft(
                ['to_entity' => 'catalog_product_entity_media_gallery_value_to_entity'],
                'gallery.value_id = to_entity.value_id',
                'value_id'
            )
            ->where('to_entity.value_id IS NULL');
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function getConnection(): \Magento\Framework\DB\Adapter\AdapterInterface
    {
        return $this->connection;
    }
}
