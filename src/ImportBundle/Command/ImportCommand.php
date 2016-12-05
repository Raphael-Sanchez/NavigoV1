<?php

namespace ImportBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// ...
class ImportCommand extends Command
{

	protected function configure()
	{
	    $this
	    	->setName('navigo:import')
	    	->addArgument('whitch', InputArgument::REQUIRED, 'Whitch base.')
	    ;
	}

	// ...
	public function execute(InputInterface $input, OutputInterface $output)
	{

	    $output->writeln("Start");

	    $argument = $input->getArgument('whitch');
	    
	    // retrieve the argument value using getArgument()
	    $output->writeln('getArgument: ' . $argument);

	    if (isset($argument) and $argument == 'cards')
	    {
	    	$cards_processor = $this->getApplication()->getKernel()->getContainer()->get('cards_processor');
	    	$cards_processor->importCards();
	    }
	    else if (isset($argument) and $argument == 'users')
	    {
	    	$users_processor = $this->getApplication()->getKernel()->getContainer()->get('users_processor');
	    	$users_processor->importUsers();
	    }
	    else
        {
	    	var_dump('Argument not valid, pass users or cards');
	    }
	    $output->writeln("Done");
	}
}
