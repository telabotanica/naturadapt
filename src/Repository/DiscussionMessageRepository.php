<?php

namespace App\Repository;

use App\Entity\DiscussionMessage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method DiscussionMessage|null find($id, $lockMode = null, $lockVersion = null)
 * @method DiscussionMessage|null findOneBy(array $criteria, array $orderBy = null)
 * @method DiscussionMessage[]    findAll()
 * @method DiscussionMessage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DiscussionMessageRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, DiscussionMessage::class);
    }

    // /**
    //  * @return DiscussionMessage[] Returns an array of DiscussionMessage objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DiscussionMessage
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}