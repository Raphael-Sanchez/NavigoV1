<?php

namespace UserBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class UserRepository extends EntityRepository
{
    public function searchUser($parameter)
    {
        return $this
            ->createQueryBuilder('u')
            ->innerJoin('u.card', 'c')
            ->where('u.lastName LIKE :term')
            ->orWhere('u.firstName LIKE :term')
            ->orWhere('c.cardNumber LIKE :term')
            ->setMaxResults(1000)
            ->setParameter('term', '%' . $parameter . '%')
            ->getQuery()
            ->getResult()
            ;
    }
}
