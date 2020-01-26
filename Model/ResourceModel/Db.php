<?php

namespace Cap\CleanMedia\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject;
use Zend_Db_Select;

class Db
{
    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * Db constructor.
     *
     * @param ResourceConnection $resource
     */
    public function __construct(ResourceConnection $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Returns resource connection
     *
     * @return ResourceConnection
     */
    public function getResource(): ResourceConnection
    {
        return $this->resource;
    }

    /**
     * Get media in db & placeholders
     *
     * @return DataObject
     */
    public function getMediaInDbNames()
    {
        $items = array_filter(array_merge($this->getMediaInDb(), $this->getPlaceholders()));
        $inDbNames = new DataObject();
        foreach ($items as $key => $item) {
            $inDbNames[$key] = preg_replace('/^.+[\\\\\\/]/', '', $item);
        }

        return $inDbNames;
    }

    /**
     * Get media in db values
     *
     * select value from 'catalog_product_entity_media_gallery'
     * where value_id are in 'catalog_product_entity_media_gallery_value_to_entity'
     *
     * @return array
     */
    protected function getMediaInDb()
    {
        $sql = $this->getResource()->getConnection()->select()
            ->from(['gallery' => 'catalog_product_entity_media_gallery'])
            ->join(
                ['to_entity' => 'catalog_product_entity_media_gallery_value_to_entity'],
                'gallery.value_id = to_entity.value_id'
            )
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns('value');

        return $this->getResource()->getConnection()->fetchCol($sql);
    }

    /**
     * Get placeholders value from core_config_data
     *
     * @return array
     */
    protected function getPlaceholders()
    {
        $sql = $this->getResource()->getConnection()->select()
            ->from('core_config_data')
            ->where('core_config_data.path LIKE "%placeholder%"')
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns('value');

        return $this->getResource()->getConnection()->fetchCol($sql);
    }
}
