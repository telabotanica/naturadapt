<?php

namespace App\Repository;

use App\Entity\LogEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LogEvent|null find( $id, $lockMode = NULL, $lockVersion = NULL )
 * @method LogEvent|null findOneBy( array $criteria, array $orderBy = NULL )
 * @method LogEvent[]    findAll()
 * @method LogEvent[]    findBy( array $criteria, array $orderBy = NULL, $limit = NULL, $offset = NULL )
 */
class LogEventRepository extends ServiceEntityRepository {
	public function __construct ( ManagerRegistry $registry ) {
		parent::__construct( $registry, LogEvent::class );
	}

	/**
	 * @param array $groups
	 *
	 * @return mixed
	 */
	public function findForGroups ( $groups = [] ) {
		return $this->createQueryBuilder( 'l' )
					->andWhere( 'l.usergroup IN (:groups)' )
					->setParameter( 'groups', $groups )
					->orderBy( 'l.createdAt', 'DESC' )
					->setMaxResults( 10 )
					->getQuery()
					->getResult();
	}

	// /**
	//  * @return LogEvent[] Returns an array of LogEvent objects
	//  */
	/*
	public function findByExampleField($value)
	{
		return $this->createQueryBuilder('l')
			->andWhere('l.exampleField = :val')
			->setParameter('val', $value)
			->orderBy('l.id', 'ASC')
			->setMaxResults(10)
			->getQuery()
			->getResult()
		;
	}
	*/

	/*
	public function findOneBySomeField($value): ?LogEvent
	{
		return $this->createQueryBuilder('l')
			->andWhere('l.exampleField = :val')
			->setParameter('val', $value)
			->getQuery()
			->getOneOrNullResult()
		;
	}
	*/
}
