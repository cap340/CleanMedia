<?php

namespace Cap\CleanMedia\Model\ResourceModel\MediaGallery;

use Cap\CleanMedia\Model\MediaGallery;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Identifier field name for collection items
     *
     * @var string
     */
    protected $_idFieldName = MediaGallery::MEDIA_ID;

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            MediaGallery::class,
            \Cap\CleanMedia\Model\ResourceModel\MediaGallery::class
        );
    }

    /**
     * @return $this|AbstractCollection|void
     * @throws LocalizedException
     */
    protected function _initSelect()
    {
        $this->getSelect()
            ->from(['main_table' => $this->getResource()->getMainTable()])
            ->join(
                ['to_entity' => 'catalog_product_entity_media_gallery_value_to_entity'],
                'main_table.value_id = to_entity.value_id'
            );

        return $this;
    }
}
