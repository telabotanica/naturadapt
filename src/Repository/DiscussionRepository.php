<?php

namespace App\Repository;

use App\Entity\Discussion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Discussion|null find( $id, $lockMode = NULL, $lockVersion = NULL )
 * @method Discussion|null findOneBy( array $criteria, array $orderBy = NULL )
 * @method Discussion[]    findAll()
 * @method Discussion[]    findBy( array $criteria, array $orderBy = NULL, $limit = NULL, $offset = NULL )
 */
class DiscussionRepository extends ServiceEntityRepository {
	public function __construct ( RegistryInterface $registry ) {
		parent::__construct( $registry, Discussion::class );
	}

	// /**
	//  * @return Discussion[] Returns an array of Discussion objects
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
	public function findOneBySomeField($value): ?Discussion
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
