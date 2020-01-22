<?php

namespace Cap\CleanMedia\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class MediaGallery extends AbstractDb
{
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('catalog_product_entity_media_gallery', 'value_id');
    }
}
