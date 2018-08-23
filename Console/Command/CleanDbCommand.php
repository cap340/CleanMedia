<?php

namespace Cap\M2DeletedProductImage\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Input\InputOption;
use Magento\Framework\App\Filesystem\DirectoryList;
use Symfony\Component\Console\Helper\ProgressBar;

class CleanDbCommand extends Command
{
    /**
     * Init command
     */
    protected function configure()
    {
        $this
            ->setName('cap:clean-db')
            ->setDescription('Remove images records in db')
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

         //Option : --dry-run for testing command without deleting anything
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
         $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
         $coreRead = $resource->getConnection('core_read');

         // Init Tables names and QUERY
         $table1 = $resource->getTableName('catalog_product_entity_media_gallery_value_to_entity');
         $table2 = $resource->getTableName('catalog_product_entity_media_gallery');
         $query = "SELECT $table2.value_id FROM $table2 LEFT OUTER JOIN $table1 ON $table2.value_id = $table1.value_id WHERE $table1.value_id IS NULL";
         $results = $coreRead->fetchCol($query);
         $resultsCount = count ($results);

         foreach ($results as $file) {

                 echo '## REMOVING: ' . $file . ' ##';

                 if (!$isDryRun) {
                     //unlink($file);
                 } else {
                     echo ' -- DRY RUN';
                 }

                 echo PHP_EOL;

         }

         $output->writeln(array(
           '<info>=================================================</>',
           "<info>" . "Found " . $resultsCount . " entries in db" . "</info>",
           '<info>=================================================</>',
         ));

     }
 }
