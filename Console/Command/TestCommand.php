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

        if(!$isDryRun) {
            $output->writeln('WARNING: this is not a dry run. If you want to do a dry-run, add --dry-run.');
            $question = new ConfirmationQuestion('Are you sure you want to continue? [No] ', false);
            $this->questionHelper = $this->getHelper('question');
            if (!$this->questionHelper->ask($input, $output, $question)) {
                return;
            }
        }

        $output->writeln('Hello World!');
    }
}
