<?php

namespace App\Repository;

use App\Entity\Usergroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Usergroup|null find( $id, $lockMode = NULL, $lockVersion = NULL )
 * @method Usergroup|null findOneBy( array $criteria, array $orderBy = NULL )
 * @method Usergroup[]    findAll()
 * @method Usergroup[]    findBy( array $criteria, array $orderBy = NULL, $limit = NULL, $offset = NULL )
 */
class UsergroupRepository extends ServiceEntityRepository {
	public function __construct ( RegistryInterface $registry ) {
		parent::__construct( $registry, Usergroup::class );
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
