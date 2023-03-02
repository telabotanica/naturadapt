<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UsergroupMembership;
use App\Traits\SearchableRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method User|null find( $id, $lockMode = NULL, $lockVersion = NULL )
 * @method User|null findOneBy( array $criteria, array $orderBy = NULL )
 * @method User[]    findAll()
 * @method User[]    findBy( array $criteria, array $orderBy = NULL, $limit = NULL, $offset = NULL )
 */
class UserRepository extends ServiceEntityRepository {
	use SearchableRepositoryTrait;

	public function __construct ( ManagerRegistry $registry ) {
		parent::__construct( $registry, User::class );
	}

	public function getCountries ( $filters = [] ) {
		$qb = $this->createQueryBuilder( 'u' );

		$i = 1;

		/**
		 * GROUP
		 */
		if ( !empty( $filters[ 'group' ] ) ) {
			$qb->innerJoin( 'u.usergroupMemberships', 'g' );

			/**
			 * @var \App\Entity\Usergroup $group
			 */
			$group = $filters[ 'group' ];
			$var   = 'group' . ( $i++ );
			$qb->andWhere( $qb->expr()->eq( 'g.usergroup', ':' . $var ) );
			$qb->setParameter( $var, $group );
		}

		return $qb->select( 'u.country' )
				  ->andWhere( 'u.country IS NOT NULL' )
				  ->distinct()
				  ->orderBy( 'u.country', 'ASC' )
				  ->getQuery()
				  ->getResult();
	}

	/**************************************************
	 * SEARCH
	 *************************************************
	 *
	 * @param \Doctrine\ORM\QueryBuilder $qb
	 * @param array                      $filters
	 */

	protected function searchAddOption ( QueryBuilder &$qb, $filters = [] ) {
		$i = 1;

		$qb->andWhere( $qb->expr()->eq( 'u.status', User::STATUS_ACTIVE ) );

		/**
		 * GROUP
		 */
		if ( !empty( $filters[ 'group' ] ) ) {
			$qb->innerJoin( 'u.usergroupMemberships', 'g' );

			/**
			 * @var \App\Entity\Usergroup $group
			 */
			$group = $filters[ 'group' ];
			$var   = 'group' . ( $i++ );
			$qb->andWhere( $qb->expr()->eq( 'g.usergroup', ':' . $var ) );
			$qb->setParameter( $var, $group );

			if ( empty( $filters[ 'status' ] ) ) {
				$var = 'group' . ( $i++ );
				$qb->andWhere( $qb->expr()->like( 'g.status', ':' . $var ) );
				$qb->setParameter( $var, UsergroupMembership::STATUS_MEMBER );
			}
			else if ( $filters[ 'status' ] !== UsergroupMembership::STATUS_ALL ) {
				$var = 'group' . ( $i++ );
				$qb->andWhere( $qb->expr()->like( 'g.status', ':' . $var ) );
				$qb->setParameter( $var, $filters[ 'status' ] );
			}
		}

		/**
		 * QUERY
		 */
		if ( !empty( $filters[ 'query' ] ) ) {
			$words = array_filter( explode( ' ', $filters[ 'query' ] ), function ( $word ) {
				return !empty( $word );
			} );

			foreach ( $words as $word ) {
				$var = 'word' . ( $i++ );
				$qb->andWhere( $qb->expr()->orX(
						$qb->expr()->like( 'u.name', ':' . $var ),
						$qb->expr()->like( 'u.displayName', ':' . $var ),
						$qb->expr()->like( 'u.presentation', ':' . $var ),
						$qb->expr()->like( 'u.bio', ':' . $var )
				) )
				   ->setParameter( $var, '%' . $word . '%' );
			}
		}

		/**
		 * COUNTRY
		 */
		if ( !empty( $filters[ 'country' ] ) ) {
			if ( !is_array( $filters[ 'country' ] ) ) {
				$filters[ 'country' ] = [ $filters[ 'country' ] ];
			}

			$query = [];
			foreach ( $filters[ 'country' ] as $country ) {
				$var     = 'country' . ( $i++ );
				$query[] = $qb->expr()->eq( 'u.country', ':' . $var );
				$qb->setParameter( $var, $country );
			}
			$qb->andWhere( new Orx( $query ) );
		}

		/**
		 * INSCRIPTION TYPE
		 */
		if ( !empty( $filters[ 'inscriptionType' ] ) ) {
			if ( !is_array( $filters[ 'inscriptionType' ] ) ) {
				$filters[ 'inscriptionType' ] = [ $filters[ 'inscriptionType' ] ];
			}

			$query = [];
			foreach ( $filters[ 'inscriptionType' ] as $inscriptionType ) {
				$var     = 'inscriptionType' . ( $i++ );
				$query[] = $qb->expr()->eq( 'u.inscriptionType', ':' . $var );
				$qb->setParameter( $var, $inscriptionType );
			}
			$qb->andWhere( new Orx( $query ) );
		}

		/**
		 * FAVORITE ENVIRONMENT
		 */
		if ( !empty( $filters[ 'favoriteEnvironment' ] ) ) {
			if ( !is_array( $filters[ 'favoriteEnvironment' ] ) ) {
				$filters[ 'favoriteEnvironment' ] = [ $filters[ 'favoriteEnvironment' ] ];
			}

			$query = [];
			foreach ( $filters[ 'favoriteEnvironment' ] as $favoriteEnvironment ) {
				$var     = 'favoriteEnvironment' . ( $i++ );
				$query[] = $qb->expr()->eq( 'u.favoriteEnvironment', ':' . $var );
				$qb->setParameter( $var, $favoriteEnvironment );
			}
			$qb->andWhere( new Orx( $query ) );
		}

		/**
		 * SKILLS
		 */
		if ( !empty( $filters[ 'skills' ] ) ) {
			if ( !is_array( $filters[ 'skills' ] ) ) {
				$filters[ 'skills' ] = [ $filters[ 'skills' ] ];
			}

			$qb->innerJoin( 'u.skills', 's' );

			$query = [];
			foreach ( $filters[ 'skills' ] as $skill ) {
				$var     = 'skill' . ( $i++ );
				$query[] = $qb->expr()->eq( 's.id', ':' . $var );
				$qb->setParameter( $var, $skill );
			}
			$qb->andWhere( new Orx( $query ) );
		}
	}

	public function searchCount ( $filters, $options = [] ) {
		$qb = $this->createQueryBuilder( 'u' );
		$qb->select( 'COUNT(u)' );

		$this->searchAddOption( $qb, $filters );

		return $qb->getQuery()
				  ->getSingleScalarResult();
	}

	public function search ( $filters, $options = [] ) {
		$options = array_merge( [ 'page' => 0, 'limit' => 20 ], $options );

		$qb = $this->createQueryBuilder( 'u' );

		$this->searchAddOption( $qb, $filters );

		/**
		 * GROUP
		 */
		if ( !empty( $filters[ 'group' ] ) ) {
			if ( !empty( $filters[ 'status' ] ) && ( $filters[ 'status' ] === UsergroupMembership::STATUS_ALL ) ) {
				$qb->addOrderBy( 'g.status', 'DESC' );
			}
			$qb->addOrderBy( 'g.joinedAt', 'DESC' );
		}
		else {
			$qb->addOrderBy( 'u.createdAt', 'DESC' );
		}

		return $qb->setFirstResult( $options[ 'limit' ] * $options[ 'page' ] )
				  ->setMaxResults( $options[ 'limit' ] )
				  ->getQuery()
				  ->getResult();
	}

	// /**
	//  * @return User[] Returns an array of User objects
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
	public function findOneBySomeField($value): ?User
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
