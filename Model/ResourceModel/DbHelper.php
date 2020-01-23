<?php

namespace Cap\CleanMedia\Model\ResourceModel;

use Magento\Framework\DB\Helper;
use Zend_Db_Select;

/**
 * Class responsible for database query
 *
 */
class DbHelper extends Helper
{
    /**
     *
     * @var array
     */
    protected $mediaInDb = [];

    /**
     * Get media used by products & placeholders
     *
     * @return array
     */
    public function getMediaInDb(): array
    {
        $this->mediaInDb = array_merge($this->fetchMediaInDb(), $this->fetchPlaceholders());
        return $this->mediaInDb;
    }

    /**
     * Fetch media used by products
     *
     * select value of 'catalog_product_entity_media_gallery'
     * where value_id are also in 'catalog_product_entity_media_gallery_value_to_entity'
     *
     * @return array
     */
    private function fetchMediaInDb()
    {
        $sql = $this->getConnection()->select()
            ->from(['gallery' => 'catalog_product_entity_media_gallery'])
            ->join(
                ['to_entity' => 'catalog_product_entity_media_gallery_value_to_entity'],
                'gallery.value_id = to_entity.value_id'
            )
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns('value');

        return $this->getConnection()->fetchCol($sql);
    }

    /**
     * Fetch placeholders in core_config_data table
     *
     * @return array
     */
    private function fetchPlaceholders()
    {
        $sql = $this->getConnection()->select()
            ->from('core_config_data')
            ->where('core_config_data.path LIKE "%placeholder%"')
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns('value');

        return $this->getConnection()->fetchCol($sql);
    }
}
