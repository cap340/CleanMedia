<?php

namespace Cap\CleanMedia\Console\Command;

use Cap\CleanMedia\Model\ResourceModel\Db;
use FilesystemIterator;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
            ->setDescription('Remove unused media from deleted products');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
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
            $filename = $file->getFilename();
            if (!in_array($filename, $inDb)) {
                $fileRelativePath = str_replace($mediaPath, '', $file->getPathname());
                $output->writeln($fileRelativePath);
                $count++;
                $size += $file->getSize();
            }
        }

        $countDb = $this->resourceDb->countDbValues();

        $output->writeln([
            '<info>Found ' . $count . ' files for ' . number_format($size / 1024 / 1024, '2') . ' MB</info>',
            '<info>Found ' . $countDb . ' database value(s) to remove</info>',
        ]);
    }
}
