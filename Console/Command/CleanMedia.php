<?php

namespace Cap\CleanMedia\Console\Command;

use Cap\CleanMedia\Model\ResourceModel\Db;
use FilesystemIterator;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Symfony\Component\Console\Command\Command;
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
     * @var Filesystem\Directory\ReadInterface
     */
    protected $mediaDirectory;

    /**
     * @var File
     */
    protected $driverFile;

    /**
     * @var Db
     */
    protected $resourceDb;

    /**
     * CleanMedia constructor.
     *
     * @param Db $resourceDb
     * @param Filesystem $filesystem
     * @param File $driverFile
     */
    public function __construct(
        Filesystem $filesystem,
        File $driverFile,
        Db $resourceDb
    ) {
        parent::__construct();
        $this->mediaDirectory = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $this->driverFile = $driverFile;
        $this->resourceDb = $resourceDb;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->setName('cap:clean:media')
            ->setDescription('Remove unused media from deleted products')
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
                'Warning: this is not a dry run. If you want to do a dry-run, add --dry-run.'
            );
            $question = new ConfirmationQuestion('<info>Are you sure you want to continue? [No] </info>', false);
            $questionHelper = $this->getHelper('question');
            if (!$questionHelper->ask($input, $output, $question)) {
                return;
            }
        }

        $mediaPath = $this->mediaDirectory->getAbsolutePath() . $this->path;
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $mediaPath,
                FilesystemIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS
            )
        );

        $inDb = $this->resourceDb->getMediaInDbNames()->toArray();
        $count = 0;
        $size = 0;

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($isNoCache) {
                if (strpos($file, "/cache") !== false) {
                    continue;
                }
            }
            $filename = $file->getFilename();
            if (!in_array($filename, $inDb)) {
                $count++;
                $size += $file->getSize();
                $fileRelativePath = str_replace($mediaPath, '', $file->getPathname());
                if (!$isDryRun) {
                    $output->writeln('<comment>REMOVING: </comment>' . $fileRelativePath);
                    $this->driverFile->deleteFile($file);
                } else {
                    $output->writeln('<comment>DRY-RUN: </comment>' . $fileRelativePath);
                }
            }
        }

        $countDb = $this->resourceDb->countDbValues();
        if (!$isDryRun) {
            $this->resourceDb->deleteDbValues();
        }

        $output->writeln([
            '<info>Found ' . $count . ' files for ' . number_format($size / 1024 / 1024, '2') . ' MB</info>',
            '<info>Found ' . $countDb . ' database value(s) to remove</info>',
        ]);
    }
}
