<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Usergroup;
use App\Entity\UsergroupMembership;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UsergroupMembership|null find( $id, $lockMode = NULL, $lockVersion = NULL )
 * @method UsergroupMembership|null findOneBy( array $criteria, array $orderBy = NULL )
 * @method UsergroupMembership[]    findAll()
 * @method UsergroupMembership[]    findBy( array $criteria, array $orderBy = NULL, $limit = NULL, $offset = NULL )
 */
class UsergroupMembershipRepository extends ServiceEntityRepository {
	public function __construct ( ManagerRegistry $registry ) {
		parent::__construct( $registry, UsergroupMembership::class );
	}

	public function getMembership ( ?User $user, Usergroup $group ): ?UsergroupMembership {
		if ( empty( $user ) ) {
			return NULL;
		}

		return $this->findOneBy( [ 'user' => $user, 'usergroup' => $group ] );
	}

	public function isMember ( ?User $user, Usergroup $group ): bool {
		$membership = $this->getMembership( $user, $group );

		return !empty( $membership ) && ( $membership->getStatus() === UsergroupMembership::STATUS_MEMBER );
	}

	public function isSubscribed ( ?User $user, Usergroup $group ): bool {
		$membership = $this->getMembership( $user, $group );

		return !empty( $membership ) && ( $membership->shouldReceiveDiscussionsEmails() );
	}

	public function isBanned ( ?User $user, Usergroup $group ): bool {
		$membership = $this->getMembership( $user, $group );

		return !empty( $membership ) && ( $membership->getStatus() === UsergroupMembership::STATUS_BANNED );
	}

	public function isPending ( ?User $user, Usergroup $group ): bool {
		$membership = $this->getMembership( $user, $group );

		return !empty( $membership ) && ( $membership->getStatus() === UsergroupMembership::STATUS_PENDING );
	}

	public function getMembers ( Usergroup $group, $limit = 0 ) {
		$query = $this->createQueryBuilder( 'm' )
					  ->innerJoin( 'm.user', 'u' )
					  ->addSelect( 'u' )
					  ->andWhere( 'm.usergroup = :group' )
					  ->setParameter( 'group', $group );

		if ( !empty( $limit ) ) {
			$query->setMaxResults( $limit );
		}

		return $query->getQuery()
					 ->getResult();
	}

	public function countMembers ( Usergroup $group ) {
		return $this->createQueryBuilder( 'm' )
					->select( 'COUNT(m.id)' )
					->andWhere( 'm.usergroup = :group' )
					->setParameter( 'group', $group )
					->getQuery()
					->getSingleScalarResult();
	}

	// /**
	//  * @return UsergroupMembership[] Returns an array of UsergroupMembership objects
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
	public function findOneBySomeField($value): ?UsergroupMembership
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
