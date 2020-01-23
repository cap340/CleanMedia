<?php

namespace Cap\CleanMedia\Model;

use Cap\CleanMedia\Model\ResourceModel\DbHelper;

/**
 * Main module class
 *
 */
class CleanMedia
{
    /**
     * @var DbHelper
     */
    protected $dbHelper;

    /**
     * CleanMedia constructor.
     *
     * @param DbHelper $dbHelper
     */
    public function __construct(DbHelper $dbHelper)
    {
        $this->dbHelper = $dbHelper;
    }

    /**
     * Return name of media in database
     *
     * @return array
     */
    public function getMediaInDbName()
    {
        $mediaInDbName = [];
        $mediaInDb = $this->dbHelper->getMediaInDb();
        foreach ($mediaInDb as $item) {
            $mediaInDbName[] = preg_replace('/^.+[\\\\\\/]/', '', $item);
        }

        return $mediaInDbName;
    }
}
