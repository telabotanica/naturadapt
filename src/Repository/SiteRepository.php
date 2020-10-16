<?php

namespace App\Repository;

use App\Entity\Site;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Site|null find( $id, $lockMode = NULL, $lockVersion = NULL )
 * @method Site|null findOneBy( array $criteria, array $orderBy = NULL )
 * @method Site[]    findAll()
 * @method Site[]    findBy( array $criteria, array $orderBy = NULL, $limit = NULL, $offset = NULL )
 */
class SiteRepository extends ServiceEntityRepository {
	public function __construct ( ManagerRegistry $registry ) {
		parent::__construct( $registry, Site::class );
	}

	public function search ( $query ) {
		$qb = $this->createQueryBuilder( 's' );

		return $qb->where( $qb->expr()->like( 's.name', ':query' ) )
				  ->setParameter( 'query', '%' . $query . '%' )
				  ->orderBy( 's.name', 'ASC' )
				  ->getQuery()
				  ->getResult();;
	}

	// /**
	//  * @return Site[] Returns an array of Site objects
	//  */
	/*
	public function findByExampleField($value)
	{
		return $this->createQueryBuilder('s')
			->andWhere('s.exampleField = :val')
			->setParameter('val', $value)
			->orderBy('s.id', 'ASC')
			->setMaxResults(10)
			->getQuery()
			->getResult()
		;
	}
	*/

	/*
	public function findOneBySomeField($value): ?Site
	{
		return $this->createQueryBuilder('s')
			->andWhere('s.exampleField = :val')
			->setParameter('val', $value)
			->getQuery()
			->getOneOrNullResult()
		;
	}
	*/
}
