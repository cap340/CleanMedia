<?php
namespace Cap\M2DeletedProductImage\Console\Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
class HelloWorldCommand extends Command
{
    protected function configure()
    {
        $this->setName('cap:helloworld')->setDescription('Prints hello world.');
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Hello World!');
    }
}
