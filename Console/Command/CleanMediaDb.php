<?php
namespace Cap\CleanMedia\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Input\InputOption;
use Magento\Framework\App\Filesystem\DirectoryList;
use Symfony\Component\Console\Helper\ProgressBar;

class CleanMediaDb extends Command
{
    /**
     * Init command
     */
    protected function configure()
    {
        $this
        ->setName('cap:clean:media-db')
        ->setDescription('Remove records of deleted products images in Db')
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

         // Option : --dry-run for testing command without deleting anything
         if(!$isDryRun) {
             $output->writeln('<error>' . 'WARNING: this is not a dry run. If you want to do a dry-run, add --dry-run.' . '</error>');
             $question = new ConfirmationQuestion('Are you sure you want to continue? [Yes/No] ', false);
             $this->questionHelper = $this->getHelper('question');
             if (!$this->questionHelper->ask($input, $output, $question)) {
                 return;
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

         // Action find and delete records in db
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
