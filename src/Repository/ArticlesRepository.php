<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Article|null find( $id, $lockMode = NULL, $lockVersion = NULL )
 * @method Article|null findOneBy( array $criteria, array $orderBy = NULL )
 * @method Article[]    findAll()
 * @method Article[]    findBy( array $criteria, array $orderBy = NULL, $limit = NULL, $offset = NULL )
 */
class ArticlesRepository extends ServiceEntityRepository {
	public function __construct ( RegistryInterface $registry ) {
		parent::__construct( $registry, Article::class );
	}

	// /**
	//  * @return Article[] Returns an array of Article objects
	//  */
	/*
	public function findByExampleField($value)
	{
		return $this->createQueryBuilder('n')
			->andWhere('n.exampleField = :val')
			->setParameter('val', $value)
			->orderBy('n.id', 'ASC')
			->setMaxResults(10)
			->getQuery()
			->getResult()
		;
	}
	*/

	/*
	public function findOneBySomeField($value): ?Article
	{
		return $this->createQueryBuilder('n')
			->andWhere('n.exampleField = :val')
			->setParameter('val', $value)
			->getQuery()
			->getOneOrNullResult()
		;
	}
	*/
}
