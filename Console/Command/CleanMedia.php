<?php

namespace Cap\CleanMedia\Console\Command;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanMedia extends Command
{
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
                ->setName('cap:clean:media')
                ->setDescription('Remove images of deleted products in /media folder && database entries');
        parent::configure();
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $objectManager = ObjectManager::getInstance();
        $filesystem    = $objectManager->get('Magento\Framework\Filesystem');
        $directory     = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $imageDir      = $directory->getAbsolutePath().DIRECTORY_SEPARATOR.'catalog'.DIRECTORY_SEPARATOR.'product';

        $output->writeln('<info>Success Message.</info>');
        $output->writeln('<error>An error encountered.</error>');
    }
}
