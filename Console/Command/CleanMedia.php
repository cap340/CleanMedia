<?php

namespace Cap\CleanMedia\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Magento\Framework\App\Filesystem\DirectoryList;

class CleanMedia extends Command
{
    /**
    * Init command
    */
    protected function configure()
    {
        $this
        ->setName('cap:clean:media')
        ->setDescription('Remove images of deleted products in /media folder && database entries')
        ;
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
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $filesystem = $objectManager->get('Magento\Framework\Filesystem');
        $directory = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $imageDir = $directory->getAbsolutePath() . DIRECTORY_SEPARATOR . 'catalog' . DIRECTORY_SEPARATOR . 'product';
        $directoryIterator = new \RecursiveDirectoryIterator($imageDir);

        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $coreRead = $resource->getConnection('core_read');
        $table1 = $resource->getTableName('catalog_product_entity_media_gallery_value_to_entity');
        $table2 = $resource->getTableName('catalog_product_entity_media_gallery');

        // NOTE: Return FileName of Images USED by Products using :
        // value_id from table 'catalog_product_entity_media_gallery_value_to_entity'
        // value of value_id from 'catalog_product_entity_media_gallery'
        $query = "SELECT $table2.value FROM $table1, $table2 WHERE $table1.value_id=$table2.value_id";
        $imagesInDbPath = $coreRead->fetchCol($query);

        // NOTE: Need NAME of files instead of PATH to include files in CACHE folder
        foreach ($imagesInDbPath as $imageInDbPath) {
            $imagesInDbName [] = preg_replace('/^.+[\\\\\\/]/', '', $imageInDbPath);
        }


        /**
        * 1) SCANNING MEDIA FOLDER
        * ------------------------
        */
        $filesize = 0;
        $countFiles = 0;
        $filesizeWithoutCache = 0;
        $countFilesWithoutCache = 0;

        $output->writeln(array(
          '',
          '## SCANNING media folder && cache',
          '---------------------------------',
          '<comment>[NOTE] This is a scan only, nothing will be removed yet.</comment>',
          '<comment>Result will appear excluding files in cache.</comment>',
          '',
        ));

        $question = new ConfirmationQuestion('Continue? [Yes/No] ', false);
        $this->questionHelper = $this->getHelper('question');
        if (!$this->questionHelper->ask($input, $output, $question)) {
            return;
        };
        $output->writeln('');

        $table = new Table($output);
        $table->setHeaders(array('Filepath','Disk Usage (Mb)'));

        $progressBar = new ProgressBar($output);
        $progressBar->setFormat('verbose_nomax');
        $progressBar->start();

        foreach (new \RecursiveIteratorIterator($directoryIterator) as $file) {

            if (is_dir($file)) continue; // exclude empty folder
            $fileName = $file->getFilename(); // NOTE: The SplFileInfo class
            $filePath = str_replace($imageDir, "", $file);
            $fileRealPath = $file->getRealPath();
            $progressBar->advance();

            if (!in_array($fileName,$imagesInDbName)) {
                $valuesToRemove [] = [
                    'fileName' => $fileName,
                    'filePath' => $filePath,
                    'fileRealPath' => $fileRealPath,
                ];
                $filesize += filesize($file);
                $countFiles++;

                // NOTE: Exclude CACHE Folder for Scan display
                if (strpos($file, "/cache") == false) {
                    $valuesToDisplay [] = [$fileName,$filePath];
                    $filesizeWithoutCache += filesize($file);
                    $countFilesWithoutCache++;
                    $table->addRow(array($filePath,number_format($file->getSize() / 1024 / 1024, '2')));
                }
            }
        }
        $progressBar->finish();
        echo PHP_EOL;
        echo PHP_EOL;

        $table->addRows(array(
            new TableSeparator(),
            array('<info>Found ' . $countFilesWithoutCache . ' files</info>', '<info>' . number_format($filesizeWithoutCache / 1024 / 1024, '2') . ' MB</info>'),
            array('<info>' . $countFiles . ' files including cache</info>', '<info>' . number_format($filesize / 1024 / 1024, '2') . ' MB Total</info>'),
        ));
        $table->render();


        /**
        * 2) REMOVING FILES
        * -----------------
        */
        echo PHP_EOL;
        if (empty($valuesToRemove)) exit; // Test if images to remove exists

        $output->writeln(array(
          '',
          '## REMOVING unused images && database entries',
          '---------------------------------------------',
          '<comment>[NOTE] This will remove all unused images found from media folder including cache && database entries.</comment>',
          '',
        ));
        $question = new ConfirmationQuestion('<error>Are you sure you want to continue? [Yes/No]</error> ', false);
        $this->questionHelper = $this->getHelper('question');
        if (!$this->questionHelper->ask($input, $output, $question)) {
            return;
        };
        $output->writeln('');

        $progressBar = new ProgressBar($output, count($valuesToRemove));
        $progressBar->setFormatDefinition('custom', ' %current%/%max% [%bar%] %message% %percent:3s%% %elapsed:6s%/%estimated:-6s%');
        $progressBar->setFormat('custom');
        $progressBar->setMessage('Removing Files');
        $progressBar->start();

        foreach ($valuesToRemove as $value) {
            unlink ($value['fileRealPath']);
            $progressBar->advance();

        }
        $progressBar->finish();
        echo PHP_EOL;
        echo PHP_EOL;


        /**
        * 3) REMOVING DATABASE ENTRIES
        * ----------------------------
        */
        $queryCleanDb = "SELECT $table2.value_id FROM $table2 LEFT OUTER JOIN $table1 ON $table2.value_id = $table1.value_id WHERE $table1.value_id IS NULL";
        $resultsCleanDb = $coreRead->fetchCol($queryCleanDb);

        if (empty($resultsCleanDb)) exit; // Test if exists db value to remove

        $progressBar = new ProgressBar($output, count ($resultsCleanDb));
        $progressBar->setFormatDefinition('custom', ' %current%/%max% [%bar%] %message% %percent:3s%% %elapsed:6s%/%estimated:-6s%');
        $progressBar->setFormat('custom');
        $progressBar->setMessage('Removing Database Entries');
        $progressBar->start();

        foreach ($resultsCleanDb as $value) {
            $dbRecordQuery = "DELETE FROM $table2 WHERE $table2.value_id = $value";
            $coreRead->query($dbRecordQuery);
            $progressBar->advance();
        }
        $progressBar->finish();
        echo PHP_EOL;

        $output->writeln(array(
          '',
          '<options=bold>Done !</>',
          '',
        ));

    }

}
