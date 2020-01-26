<?php

namespace Cap\CleanMedia\Console\Command;

use Symfony\Component\Console\Command\Command;
use Cap\CleanMedia\Model\ResourceModel\Db;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Cap\CleanMedia\Model\Filesystem\Collection;
use Cap\CleanMedia\Model\Filesystem\CollectionFactory;

class CleanDbCommand extends Command
{
    /**
     * @var Db
     */
    protected $resourceDb;

    /**
     * @var Collection
     */
    protected $fsCollection;

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('cap:clean:db');
    }

    /**
     * CleanDatabaseCommand constructor.
     *
     * @param Db $resourceDb
     * @param CollectionFactory
     * @param null $name
     */
    public function __construct(
        Db $resourceDb,
        CollectionFactory $collectionFactory,
        $name = null
    ) {
        parent::__construct($name);
        $this->resourceDb = $resourceDb;
        $this->fsCollection = $collectionFactory->create();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $test = $this->fsCollection->getItems();
        print_r($test);

//        $inDbName = $this->resourceDb->getMediaInDbNames();
//        print_r($inDbName);
//        $count = $this->resourceDb->getValuesToRemoveCount();
//        $output->writeln($count);
//        $this->resourceDb->deleteDbValuesToRemove();
    }
}
