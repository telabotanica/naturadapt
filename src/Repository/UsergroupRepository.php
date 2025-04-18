<?php

namespace App\Repository;

use App\Entity\LogEvent;
use App\Entity\Usergroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Usergroup|null find( $id, $lockMode = NULL, $lockVersion = NULL )
 * @method Usergroup|null findOneBy( array $criteria, array $orderBy = NULL )
 * @method Usergroup[]    findAll()
 * @method Usergroup[]    findBy( array $criteria, array $orderBy = NULL, $limit = NULL, $offset = NULL )
 */
class UsergroupRepository extends ServiceEntityRepository {
	public function __construct ( ManagerRegistry $registry ) {
		parent::__construct( $registry, Usergroup::class );
	}

	/**
	 * @param Usergroup|bool $community
	 * @param bool|null $isActive
	 * @return mixed
	 */
	public function getGroupsWithMembers ( $community = FALSE, $isActive = null ) {
		$qb = $this->createQueryBuilder( 'ug' );

		if ( $community ) {
			$qb->andWhere( $qb->expr()->notLike( 'ug.id', '?2' ) )
			   ->setParameter( 2, $community->getId() );
		}

		if ( is_bool($isActive) ) {
			$qb->andWhere( 'ug.isActive = :isActive' )
			   ->setParameter( 'isActive', intval( $isActive ) );
		}

		$qb->leftJoin( 'ug.members', 'm' )
		   ->addSelect( 'm' )
		   ->leftJoin( 'm.user', 'u' )
		   ->addSelect( 'u' );

		$results = $qb->getQuery()
					  ->getResult();

		$logEventRepository = $this->getEntityManager()->getRepository( LogEvent::class );
		uasort( $results, function ( $a, $b ) use ( $logEventRepository ) {
			$aLog = $logEventRepository->findOneBy( [ 'usergroup' => $a ], [ 'createdAt' => 'DESC' ] );
			$bLog = $logEventRepository->findOneBy( [ 'usergroup' => $b ], [ 'createdAt' => 'DESC' ] );

			// Si aucun des groupes n'a d'événement de journal, on considère qu'ils sont égaux
			if (!$aLog && !$bLog) {
				return 0;
			}
			
			// Si un seul groupe a un événement de journal, on le met en premier
			if (!$aLog) return 1;
			if (!$bLog) return -1;
			
			// Si les deux ont des événements, on compare leurs dates
			$aDate = $aLog->getCreatedAt()->format('c');
			$bDate = $bLog->getCreatedAt()->format('c');

			if ($aDate == $bDate) {
				return 0;
			}
			return ($aDate < $bDate) ? 1 : -1;
		});


		if ( $community ) {
			array_unshift( $results, $community );
		}

		return $results;
	}

	// /**
	//  * @return Usergroup[] Returns an array of Usergroup objects
	//  */
	/*
	public function findByExampleField($value)
	{
		return $this->createQueryBuilder('u')
			->andWhere('u.exampleField = :val')
			->setParameter('val', $value)
			->orderBy('u.id', 'ASC')
			->setMaxResults(10)
			->getQuery()
			->getResult()
		;
	}
	*/

	/*
	public function findOneBySomeField($value): ?Usergroup
	{
		return $this->createQueryBuilder('u')
			->andWhere('u.exampleField = :val')
			->setParameter('val', $value)
			->getQuery()
			->getOneOrNullResult()
		;
	}
	*/
}