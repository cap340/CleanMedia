<?php
namespace Cap\M2DeletedProductImage\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Input\InputOption;
use Magento\Framework\App\Filesystem\DirectoryList;
use Symfony\Component\Console\Helper\ProgressBar;

class RemoveDeletedMediaCommand extends Command
{
    /**
     * Init command
     */
    protected function configure()
    {
        $this
            ->setName('cap:media:remove-deleted')
            ->setDescription('Remove deleted products images from MEDIA FOLDER and records in the catalog_product_entity_media_gallery TABLE')
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
        $filesize = 0;
        $isDryRun = $input->getOption('dry-run');

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

        // INIT TABLES NAME AND QUERY
        $table1 = $resource->getTableName('catalog_product_entity_media_gallery_value_to_entity');
        $table2 = $resource->getTableName('catalog_product_entity_media_gallery');
        $query1 = "SELECT $table2.value FROM $table1, $table2 WHERE $table1.value_id=$table2.value_id"; // array with all USED IMAGES BY PRODUCTS
        $query2 = "SELECT $table2.value FROM $table2 LEFT OUTER JOIN $table1 ON $table2.value_id = $table1.value_id WHERE $table1.value_id IS NULL"; // array with IMAGES OF DELETED PRODUCTS

        // JOBS
        $results1 = $coreRead->fetchCol($query1);//fetchCol for file path
        $results2 = $coreRead->fetchCol($query2);
        $intersection = array_intersect($results1, $results2);//both array
        $finalResults = array_diff ($results2, $intersection);//exclude images to delete used by enable product

        // delete files in MEDIA FOLDER
        foreach ($finalResults as $file) {

          $row = array();
          $row[] = $file;
          $table[] = $row;
          $finalPath = $imageDir . $file;
          $filesize += filesize($finalPath);

          echo '## REMOVING: ' . $file . ' ##';
          if (!$isDryRun) {
              unlink($file);
          } else {
              echo ' -- DRY RUN';
          }
          echo PHP_EOL;

        }

        //END SCRIPT AND WRITEOUT
        $headers = array();
        $headers[] = 'file';
        $this->getHelper('table')
            ->setHeaders($headers)
            ->setRows($table)
            ->render($output);
        $output->writeln("Found " . count($results1) . " image(s) used for PRODUCTS");
        $output->writeln("Found " . count($results2) . " image(s) of DELETED PRODUCTS");
        $output->writeln("Found " . count($intersection) . " image(s) EXCLUDE (image of a deleted product used by another product)");
        $output->writeln("Found " . count($finalResults) . " image(s) to DELETE");
        $output->writeln(number_format($filesize / 1024 / 1024, '2') . " MB to clean");


    }
}
