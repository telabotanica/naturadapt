<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Category|null find( $id, $lockMode = NULL, $lockVersion = NULL )
 * @method Category|null findOneBy( array $criteria, array $orderBy = NULL )
 * @method Category[]    findAll()
 * @method Category[]    findBy( array $criteria, array $orderBy = NULL, $limit = NULL, $offset = NULL )
 */
class CategoryRepository extends ServiceEntityRepository {
	public function __construct ( RegistryInterface $registry ) {
		parent::__construct( $registry, Category::class );
	}

	// /**
	//  * @return Category[] Returns an array of Category objects
	//  */
	/*
	public function findByExampleField($value)
	{
		return $this->createQueryBuilder('c')
			->andWhere('c.exampleField = :val')
			->setParameter('val', $value)
			->orderBy('c.id', 'ASC')
			->setMaxResults(10)
			->getQuery()
			->getResult()
		;
	}
	*/

	/*
	public function findOneBySomeField($value): ?Category
	{
		return $this->createQueryBuilder('c')
			->andWhere('c.exampleField = :val')
			->setParameter('val', $value)
			->getQuery()
			->getOneOrNullResult()
		;
	}
	*/
}
