<?php

namespace App\Repository;

use App\Entity\File;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method File|null find( $id, $lockMode = NULL, $lockVersion = NULL )
 * @method File|null findOneBy( array $criteria, array $orderBy = NULL )
 * @method File[]    findAll()
 * @method File[]    findBy( array $criteria, array $orderBy = NULL, $limit = NULL, $offset = NULL )
 */
class FileRepository extends ServiceEntityRepository {
	public function __construct ( ManagerRegistry $registry ) {
		parent::__construct( $registry, File::class );
	}

	// /**
	//  * @return File[] Returns an array of File objects
	//  */
	/*
	public function findByExampleField($value)
	{
		return $this->createQueryBuilder('f')
			->andWhere('f.exampleField = :val')
			->setParameter('val', $value)
			->orderBy('f.id', 'ASC')
			->setMaxResults(10)
			->getQuery()
			->getResult()
		;
	}
	*/

	/*
	public function findOneBySomeField($value): ?File
	{
		return $this->createQueryBuilder('f')
			->andWhere('f.exampleField = :val')
			->setParameter('val', $value)
			->getQuery()
			->getOneOrNullResult()
		;
	}
	*/
}
