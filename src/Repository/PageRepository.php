<?php

namespace App\Repository;

use App\Entity\Page;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Page|null find( $id, $lockMode = NULL, $lockVersion = NULL )
 * @method Page|null findOneBy( array $criteria, array $orderBy = NULL )
 * @method Page[]    findAll()
 * @method Page[]    findBy( array $criteria, array $orderBy = NULL, $limit = NULL, $offset = NULL )
 */
class PageRepository extends ServiceEntityRepository {
	public function __construct ( RegistryInterface $registry ) {
		parent::__construct( $registry, Page::class );
	}

	// /**
	//  * @return Page[] Returns an array of Page objects
	//  */
	/*
	public function findByExampleField($value)
	{
		return $this->createQueryBuilder('p')
			->andWhere('p.exampleField = :val')
			->setParameter('val', $value)
			->orderBy('p.id', 'ASC')
			->setMaxResults(10)
			->getQuery()
			->getResult()
		;
	}
	*/

	/*
	public function findOneBySomeField($value): ?Page
	{
		return $this->createQueryBuilder('p')
			->andWhere('p.exampleField = :val')
			->setParameter('val', $value)
			->getQuery()
			->getOneOrNullResult()
		;
	}
	*/
}
