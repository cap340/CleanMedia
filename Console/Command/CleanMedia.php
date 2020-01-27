<?php

namespace Cap\CleanMedia\Console\Command;

use Cap\CleanMedia\Model\ResourceModel\Db;
use FilesystemIterator;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class CleanMedia extends Command
{
    /**
     * Folder, where all media are stored
     *
     * @var string
     */
    protected $path = 'catalog/product';

    /**
     * @var WriteInterface
     */
    protected $mediaDirectory;

    /**
     * @var QuestionHelper
     */
    protected $questionHelper;

    /**
     * @var Db
     */
    protected $resourceDb;

    /**
     * CleanMedia constructor.
     *
     * @param Db $resourceDb
     * @param Filesystem $filesystem
     */
    public function __construct(
        Db $resourceDb,
        Filesystem $filesystem
    ) {
        parent::__construct();
        $this->resourceDb = $resourceDb;
        $this->mediaDirectory = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
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

        $path = $this->mediaDirectory->getAbsolutePath() . $this->path;
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $path,
                FilesystemIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS
            )
        );

//        $inDbNames = $this->resourceDb->getMediaInDbNames()->toArray();

        $count = 0;
        foreach ($iterator as $file) {
            if ($isNoCache) {
                if (strpos($file, "/cache") !== false) {
                    continue;
                }
                $count++;
            }
            $count++;
        }

        $output->writeln($count);
    }
}
