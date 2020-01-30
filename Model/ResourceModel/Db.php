<?php

namespace Cap\CleanMedia\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Select;
use Zend_Db_Select;
use Zend_Db_Statement_Interface;

class Db
{
    /**
     * @var ResourceConnection
     */
    protected $connection;

    /**
     * Db constructor.
     *
     * @param ResourceConnection $connection
     */
    public function __construct(
        ResourceConnection $connection
    ) {
        $this->connection = $connection;
    }

    /**
     * Get media in db & placeholders
     *
     * @return DataObject
     */
    public function getMediaInDbNames()
    {
        $items = array_filter(array_merge($this->getMediaInDb(), $this->getPlaceholders()));
        $object = new DataObject();
        foreach ($items as $key => $item) {
            $object[$key] = preg_replace('/^.+[\\\\\\/]/', '', $item);
        }

        return $object;
    }

    /**
     * Get media in db values
     *
     * select value from 'catalog_product_entity_media_gallery'
     * where value_id are in 'catalog_product_entity_media_gallery_value_to_entity'
     *
     * @return array
     */
    private function getMediaInDb()
    {
        $sql = $this->connection->getConnection()->select()
            ->from(['gallery' => 'catalog_product_entity_media_gallery'])
            ->join(
                ['to_entity' => 'catalog_product_entity_media_gallery_value_to_entity'],
                'gallery.value_id = to_entity.value_id'
            )
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns('value');

        return $this->connection->getConnection()->fetchCol($sql);
    }

    /**
     * Get placeholders value from core_config_data
     *
     * @return array
     */
    private function getPlaceholders()
    {
        $sql = $this->connection->getConnection()->select()
            ->from('core_config_data')
            ->where('core_config_data.path LIKE "%placeholder%"')
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns('value');

        return $this->connection->getConnection()->fetchCol($sql);
    }

    /**
     * Returns db values to remove count
     *
     * @return int|void
     */
    public function countDbValues()
    {
        $select = $this->selectDbValues();
        return count($this->connection->getConnection()->fetchCol($select));
    }

    /**
     * Get values to remove in db
     *
     * select value_id from 'catalog_product_entity_media_gallery'
     * where value_id are not in 'catalog_product_entity_media_gallery_value_to_entity'
     *
     * @return Select
     */
    private function selectDbValues()
    {
        return $this->connection->getConnection()->select()
            ->from(['gallery' => 'catalog_product_entity_media_gallery'])
            ->joinLeft(
                ['to_entity' => 'catalog_product_entity_media_gallery_value_to_entity'],
                'gallery.value_id = to_entity.value_id',
                'value_id'
            )
            ->where('to_entity.value_id IS NULL');
    }

    /**
     * @return Zend_Db_Statement_Interface
     */
    public function deleteDbValues()
    {
        $sql = $this->selectDbValues()->deleteFromSelect('gallery');
        return $this->connection->getConnection()->query($sql);
    }
}
