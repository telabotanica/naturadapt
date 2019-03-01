<?php

namespace App\Repository;

use App\Entity\UsergroupMembership;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UsergroupMembership|null find( $id, $lockMode = NULL, $lockVersion = NULL )
 * @method UsergroupMembership|null findOneBy( array $criteria, array $orderBy = NULL )
 * @method UsergroupMembership[]    findAll()
 * @method UsergroupMembership[]    findBy( array $criteria, array $orderBy = NULL, $limit = NULL, $offset = NULL )
 */
class UsergroupMembershipRepository extends ServiceEntityRepository {
	public function __construct ( RegistryInterface $registry ) {
		parent::__construct ( $registry, UsergroupMembership::class );
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
