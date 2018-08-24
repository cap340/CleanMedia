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
        ->setName('cap:clean-media')
        ->setDescription('Remove images of deleted products in /media folder')
        ->addOption('exclude-db')
        ->addOption('exclude-cache')
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
         $isExcludeDb = $input->getOption('exclude-db');
         $isExcludeCache = $input->getOption('exclude-cache');

         $filesize = 0;
         $countFiles = 0;

         // Option : --dry-run for testing command without deleting anything
         if(!$isDryRun) {
             $output->writeln('<error>' . 'WARNING: this is not a dry run. If you want to do a dry-run, add --dry-run.' . '</error>');
             $question = new ConfirmationQuestion('Are you sure you want to continue? [Yes/No] ', false);
             $this->questionHelper = $this->getHelper('question');
             if (!$this->questionHelper->ask($input, $output, $question)) {
                 return;
             }
         }

         // Option : --exclude-cache
         if($isExcludeCache) {
           function cacheOption($file) {
             return strpos($file, "/cache") !== false || is_dir($file); // exclude empty folder & /cache
           }
         } else {
           function cacheOption($file) {
             return is_dir($file); // exclude empty folder
           }
         }

         $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
         $filesystem = $objectManager->get('Magento\Framework\Filesystem');
         $directory = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
         $imageDir = $directory->getAbsolutePath() . DIRECTORY_SEPARATOR . 'catalog' . DIRECTORY_SEPARATOR . 'product';
         $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
         $coreRead = $resource->getConnection('core_read');
         $i = 0;
         $directoryIterator = new \RecursiveDirectoryIterator($imageDir);

         // Init Tables names and QUERY
         $table1 = $resource->getTableName('catalog_product_entity_media_gallery_value_to_entity');
         $table2 = $resource->getTableName('catalog_product_entity_media_gallery');
         $query = "SELECT $table2.value FROM $table1, $table2 WHERE $table1.value_id=$table2.value_id"; // ALL USED IMAGES by PRODUCTS
         $results = $coreRead->fetchCol($query);

         // Action find and delete images
         foreach (new \RecursiveIteratorIterator($directoryIterator) as $file) {

             if (cacheOption($file)) {
                 continue;
             }

             $filePath = str_replace($imageDir, "", $file);
             if (empty($filePath)) continue;

             // CHECK if image in '/media' folder IS USED by any product
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

         $output->writeln(array(
           '<info>=================================================</>',
           "<info>" . "Found " . number_format($filesize / 1024 / 1024, '2') . " MB unused images in $countFiles files" . "</info>",
           '<info>=================================================</>',
         ));

         // Action find and delete records in db
         if(!$isExcludeDb) {
             $output->writeln('<error>' . 'Cleaning Database' . '</error>');

             $queryCleanDb = "SELECT $table2.value_id FROM $table2 LEFT OUTER JOIN $table1 ON $table2.value_id = $table1.value_id WHERE $table1.value_id IS NULL";
             $resultsCleanDb = $coreRead->fetchCol($queryCleanDb);
             $resultsCleanDbCount = count ($resultsCleanDb);

             foreach ($resultsCleanDb as $dbRecordToClean) {

               echo '## REMOVING: ' . $dbRecordToClean . ' ##';

               if (!$isDryRun) {
                $dbRecordQuery = "DELETE FROM $table2 WHERE $table2.value_id = $dbRecordToClean";
                $coreRead->query($dbRecordQuery);

               } else {
                   echo ' -- DRY RUN';
               }

               echo PHP_EOL;
               $i++;
             }

             $output->writeln(array(
               '<info>=================================================</>',
               "<info>" . "Found " . $resultsCleanDbCount . " entries in db" . "</info>",
               '<info>=================================================</>',
             ));

         }

     }
 }
