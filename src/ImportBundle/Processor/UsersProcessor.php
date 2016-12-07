<?php

namespace ImportBundle\Processor;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use UserBundle\Entity\User;
use Doctrine\ORM\Query\ResultSetMapping;

class UsersProcessor
{
    private $container;
    private $em;

    public function __construct(Container $container) {
        $this->container = $container;
        $this->em = $container->get('doctrine')->getEntityManager();
    }

    public function importUsers() {

    	$fileContent = file_get_contents(__DIR__ . '/../../../imports/users.lst', FILE_USE_INCLUDE_PATH);
	    $tabContent = explode(PHP_EOL, $fileContent);
        $tabContentWithoutDuplicateEntry = array_unique($tabContent);

        if(array_search('', $tabContentWithoutDuplicateEntry) !== false) {
            $key = array_search('', $tabContentWithoutDuplicateEntry);
            unset($tabContentWithoutDuplicateEntry[$key]);
        }

        $limit = count($tabContentWithoutDuplicateEntry);
    	$offset = 0;

        //$this->em->getConnection()->exec('DELETE FROM `user` WHERE 1');

        $start = microtime(true);
        $startCurrent = $start;

        while ($offset < $limit) {

            $cards = $this->em->getRepository('CardBundle:Card')->findBy(array("user" => null), null, 100);
			$tabContentSlice = array_slice($tabContentWithoutDuplicateEntry, $offset, 100);
            $count = 0;

            foreach ($tabContentSlice as $key => $username) {
                $count++;
                $splitUsername = explode(' ', $username);
                $password = uniqid();

                $user = new User();
                $user->setFirstname(ucfirst($splitUsername[0]));
                $user->setLastname(strtoupper($splitUsername[1]));
                $user->setRole('ROLE_USER');
                $user->setPasswordCheck('NULL');
                $passwordHash = hash("sha256", $password);
                $user->setPasswordHash('NULL');
                $user->setPassword($passwordHash);
                $user->setCard($cards[$key]);

                $this->em->persist($user);
                if ($count == 20) {
                    $this->em->flush();
                    $count = 0;
                }
            }
            
            $this->em->clear();

            $currentTime = microtime(true);
            $totalTime = ($currentTime -$start)/60;
            $time = $currentTime - $startCurrent;
            $startCurrent = $currentTime;

            var_dump('Depuis :' . round($totalTime, 2) . ' mn, Requete courante :' . round($time, 2) . ' s, total entrées : ' . $offset . '/' . $limit);
			$offset += 100;
    	}
		
    }
}