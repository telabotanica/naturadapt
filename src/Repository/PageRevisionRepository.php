<?php

namespace App\Repository;

use App\Entity\PageRevision;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PageRevision|null find( $id, $lockMode = NULL, $lockVersion = NULL )
 * @method PageRevision|null findOneBy( array $criteria, array $orderBy = NULL )
 * @method PageRevision[]    findAll()
 * @method PageRevision[]    findBy( array $criteria, array $orderBy = NULL, $limit = NULL, $offset = NULL )
 */
class PageRevisionRepository extends ServiceEntityRepository {
	public function __construct ( ManagerRegistry $registry ) {
		parent::__construct( $registry, PageRevision::class );
	}

	// /**
	//  * @return PageRevision[] Returns an array of PageRevision objects
	//  */
	/*
	public function findByExampleField($value)
	{
		return $this->createQueryBuilder('p')
			->andWhere('p.exampleField = :val')
			->setParameter('val', $value)
			->orderBy('p.id', 'ASC')
			->setMaxResults(10)
			->getQuery()
			->getResult()
		;
	}
	*/

	/*
	public function findOneBySomeField($value): ?PageRevision
	{
		return $this->createQueryBuilder('p')
			->andWhere('p.exampleField = :val')
			->setParameter('val', $value)
			->getQuery()
			->getOneOrNullResult()
		;
	}
	*/
}
