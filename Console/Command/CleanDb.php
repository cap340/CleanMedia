<?php

namespace Cap\CleanMedia\Console\Command;

use Cap\CleanMedia\Model\ResourceModel\Product\Image as ResourceImage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class CleanDb extends Command
{
    /**
     * @var ResourceImage
     */
    protected $resourceImage;

    /**
     * CleanMedia constructor.
     *
     * @param ResourceImage $resourceImage
     */
    public function __construct(ResourceImage $resourceImage)
    {
        parent::__construct();
        $this->resourceImage = $resourceImage;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('cap:clean:db')
            ->setDescription('Remove unused media in database')
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Perform a dry-run without deleting any records.'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $isDryRun = $input->getOption('dry-run');

        if (!$isDryRun) {
            $output->writeln(
                'Warning: this is not a dry run. If you want to do a dry-run, add --dry-run.'
            );
            $question = new ConfirmationQuestion('<info>Are you sure you want to continue? [No] </info>', false);
            $questionHelper = $this->getHelper('question');
            if (!$questionHelper->ask($input, $output, $question)) {
                return;
            }
        }

        $countDb = count($this->resourceImage->getConnection()->fetchCol($this->resourceImage->getUnusedImagesInDb()));
        if (!$isDryRun) {
            $sql = $this->resourceImage->getUnusedImagesInDb()->deleteFromSelect('gallery');
            $this->resourceImage->getConnection()->query($sql);
        }

        $output->writeln([
            '<info>Found ' . $countDb . ' value(s) to remove.</info>',
        ]);
    }
}
