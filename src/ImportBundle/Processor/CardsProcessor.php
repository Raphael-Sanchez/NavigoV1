<?php

namespace ImportBundle\Processor;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Doctrine\ORM\Query\ResultSetMapping;

class CardsProcessor 
{    

    private $container;
    private $em;

    public function __construct(Container $container) {
        $this->container = $container;
        $this->em = $container->get('doctrine')->getEntityManager();
    }

    public function importCards() {

    	$fileContent = file_get_contents(__DIR__ . '/../../../imports/cards.lst', FILE_USE_INCLUDE_PATH);
	    $tabContent = explode(PHP_EOL, $fileContent);
        while (array_search('', $tabContent) !== false) {
            $key = array_search('', $tabContent);
            unset($tabContent[$key]);
        }
        $limit = count($tabContent);

    	$offset = 0;

        $this->em->getConnection()->exec('DELETE FROM `card` WHERE 1');

    	while ($offset < $limit) {
            var_dump("Import offset " . $offset);
			$tabContentSlice = array_slice($tabContent, $offset, 100000);
	    	$concatContent = implode('"),("', $tabContentSlice);
	    	$concatContent = '("' . $concatContent . '")';

            $this->em->getConnection()->exec('INSERT INTO `card`(`card_number`) VALUES ' . $concatContent);

			$offset += 100000;
    	}

    }
}