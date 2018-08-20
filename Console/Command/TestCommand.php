<?php
namespace Cap\M2DeletedProductImage\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Input\InputOption;
use Magento\Framework\App\Filesystem\DirectoryList;

class TestCommand extends Command
{
    /**
     * Init command
     */
    protected function configure()
    {
        $this
            ->setName('cap:test-command')
            ->setDescription('For Testing')
            ->addOption('dry-run');
    }

    /**
     * Execute Command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void;
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $isDryRun = $input->getOption('dry-run');
        $filesize = 0;
        $countFiles = 0;


        if(!$isDryRun) {
            $output->writeln('WARNING: this is not a dry run. If you want to do a dry-run, add --dry-run.');
            $question = new ConfirmationQuestion('Are you sure you want to continue? [Yes/No] ', false);
            $this->questionHelper = $this->getHelper('question');
            if (!$this->questionHelper->ask($input, $output, $question)) {
                return;
            }
        }

        $table = array();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $filesystem = $objectManager->get('Magento\Framework\Filesystem');
        $directory = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $imageDir = $directory->getAbsolutePath() . DIRECTORY_SEPARATOR . 'catalog' . DIRECTORY_SEPARATOR . 'product';
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $mediaGallery = $resource->getConnection()->getTableName('catalog_product_entity_media_gallery');
        $coreRead = $resource->getConnection('core_read');
        $i = 0;
        $directoryIterator = new \RecursiveDirectoryIterator($imageDir);

        // Init Tables names and QUERY
        $table1 = $resource->getTableName('catalog_product_entity_media_gallery_value_to_entity');
        $table2 = $resource->getTableName('catalog_product_entity_media_gallery');
        $query = "SELECT $table2.value FROM $table1, $table2 WHERE $table1.value_id=$table2.value_id"; // array with ALL USED IMAGES by PRODUCTS
        $results = $coreRead->fetchCol($query);

        foreach (new \RecursiveIteratorIterator($directoryIterator) as $file) {

            if (strpos($file, "/cache") !== false || is_dir($file)) {
                continue;
            }

            $filePath = str_replace($imageDir, "", $file);
            if (empty($filePath)) continue;

            // CHECK if image file in "/media/catalog/product" folder IS USED by a product
            if(!in_array($filePath, $results)) {

                $row = array();
                $row[] = $filePath;
                $filesize += filesize($file);
                $countFiles++;

                echo '## REMOVING: ' . $filePath . ' ##';

                if (!$isDryRun) {
                    unlink($file);
                } else {
                    echo ' -- DRY RUN';
                }

                echo PHP_EOL;
                $i++;
              }

        }

        $output->writeln("Found " . number_format($filesize / 1024 / 1024, '2') . " MB unused images in $countFiles files");

    }
}
