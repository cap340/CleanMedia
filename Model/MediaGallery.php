<?php

namespace Cap\CleanMedia\Model;

use Magento\Framework\Model\AbstractModel;

class MediaGallery extends AbstractModel
{
    /**
     * Name of object id field
     *
     * @var string
     */
    const MEDIA_ID = 'value_id';

    /**
     * Name of object id field
     *
     * @var string
     */
    protected $_idFieldName = self::MEDIA_ID;

    /**
     * MediaInDb model construct
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\MediaGallery::class);
    }
}
