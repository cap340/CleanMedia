<?php

namespace Cap\CleanMedia\Console\Command;

use Cap\CleanMedia\Model\ResourceModel\Db;
use FilesystemIterator;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\Driver\File;
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
     * @var File
     */
    protected $fileDriver;

    /**
     * CleanMedia constructor.
     *
     * @param Db $resourceDb
     * @param Filesystem $filesystem
     * @param File $fileDriver
     */
    public function __construct(
        Db $resourceDb,
        Filesystem $filesystem,
        File $fileDriver
    ) {
        parent::__construct();
        $this->resourceDb = $resourceDb;
        $this->mediaDirectory = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $this->fileDriver = $fileDriver;
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
     * @throws FileSystemException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $isNoCache = $input->getOption('no-cache');
        $isDryRun = $input->getOption('dry-run');
        if (!$isDryRun) {
            $output->writeln(
                '<info>Warning: this is not a dry run. If you want to do a dry-run, add --dry-run.</info>'
            );
            $question = new ConfirmationQuestion('<info>Are you sure you want to continue? [No] </info>', false);
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

        $inDb = $this->resourceDb->getMediaInDbNames()->toArray();
        $count = 0;
        $size = 0;
        foreach ($iterator as $file) {
            if ($isNoCache) {
                if (strpos($file, "/cache") !== false) {
                    continue;
                }
            }
            $filename = $file->getFilename();
            if (!in_array($filename, $inDb)) {
                $fileRelativePath = str_replace($path, '', $file->getPathname());
                $size += $file->getSize();
                $count++;
                if (!$isDryRun) {
                    $output->writeln('<comment>REMOVING: </comment>' . $fileRelativePath);
                    $this->fileDriver->deleteFile($file);
                } else {
                    $output->writeln('<comment>DRY-RUN: </comment>' . $fileRelativePath);
                }
            }
        }

        $countDb = $this->resourceDb->getValuesToRemoveCount();
        if (!$isDryRun) {
            $this->resourceDb->deleteDbValuesToRemove();
        }

        $output->writeln([
            '',
            '<info>Found ' . $count . ' files for ' . number_format($size / 1024 / 1024, '2') . ' MB</info>',
            '<info>Found ' . $countDb . ' db entries to remove</info>',
        ]);
    }
}
