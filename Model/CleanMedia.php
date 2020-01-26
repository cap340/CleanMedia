<?php

namespace Cap\CleanMedia\Model;

use Cap\CleanMedia\Model\ResourceModel\Db;

/**
 * Main module class
 *
 */
class CleanMedia
{
    /**
     * @var Db
     */
    private $resourceDb;

    /**
     * CleanMedia constructor.
     *
     * @param Db $resourceDb
     */
    public function __construct(Db $resourceDb)
    {
        $this->resourceDb = $resourceDb;
    }

    /**
     * Return name of media in dd
     *
     * @return array
     */
    public function getMediaInDbNames()
    {
        return $this->resourceDb->getMediaInDbNames()->toArray();
    }
}
