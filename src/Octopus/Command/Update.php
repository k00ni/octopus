<?php

namespace Octopus\Command;

use Naucon\File\File;
use Octopus\ConfigurationHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Update extends Command
{
    protected function configure()
    {
        $this->setName('update')
            ->setDescription('Install or update local knowledge (vocabularies and ontologies).')
            ->setDefinition(
                new InputDefinition(array(
                    new InputOption('config', 'c', InputOption::VALUE_OPTIONAL),
                ))
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // if configuration file was given, use it instead of the one at the same folder
        // level where the octopus application was called.
        if (null !== $input->getOption('config')) {
            $configurationFilepath = $input->getOption('config');
        } else {
            $configurationFilepath = './octopus.json';
        }

        $configurationHandler = new ConfigurationHandler(__DIR__  . '/../../../');
        $configurationHandler->setup(
            $configurationFilepath,
            new File(__DIR__ .'/../../../repository')
        );
        $result = $configurationHandler->install();

        $output->writeln($result);
    }
}
