<?php

namespace App\Repository;

use App\Entity\DocumentFolder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method DocumentFolder|null find($id, $lockMode = null, $lockVersion = null)
 * @method DocumentFolder|null findOneBy(array $criteria, array $orderBy = null)
 * @method DocumentFolder[]    findAll()
 * @method DocumentFolder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DocumentFolderRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, DocumentFolder::class);
    }

    // /**
    //  * @return DocumentFolder[] Returns an array of DocumentFolder objects
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
    public function findOneBySomeField($value): ?DocumentFolder
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
