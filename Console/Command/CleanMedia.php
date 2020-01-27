<?php

namespace Cap\CleanMedia\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Helper\QuestionHelper;
use Cap\CleanMedia\Model\ResourceModel\Db;

class CleanMedia extends Command
{
    /**
     * @var QuestionHelper
     */
    protected $questionHelper;

    /**
     * @var Db
     */
    protected $resourceDb;

    public function __construct(
        Db $resourceDb,
        $name = null
    ) {
        parent::__construct($name);
        $this->resourceDb = $resourceDb;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('cap:clean:media')
            ->setDescription('Remove media from deleted products')
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Perform a dry-run without deleting any files.'
            )
            ->addOption(
                'no-cache',
                null,
                InputOption::VALUE_NONE,
                'Skip cache folder to avoid performance issues with huge catalog.'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $isNoCache = $input->getOption('no-cache');
        $isDryRun = $input->getOption('dry-run');
        if (!$isDryRun) {
            $output->writeln('WARNING: this is not a dry run. If you want to do a dry-run, add --dry-run.');
            $question = new ConfirmationQuestion('Are you sure you want to continue? [No] ', false);
            $this->questionHelper = $this->getHelper('question');
            if (!$this->questionHelper->ask($input, $output, $question)) {
                return;
            }
        }

        $inDbNames = $this->resourceDb->getMediaInDbNames()->toArray();
        print_r($inDbNames);
    }
}
