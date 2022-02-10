<?php

namespace App\Repository;

use App\Entity\Document;
use App\Entity\Usergroup;
use App\Traits\SearchableRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Document|null find( $id, $lockMode = NULL, $lockVersion = NULL )
 * @method Document|null findOneBy( array $criteria, array $orderBy = NULL )
 * @method Document[]    findAll()
 * @method Document[]    findBy( array $criteria, array $orderBy = NULL, $limit = NULL, $offset = NULL )
 */
class DocumentRepository extends ServiceEntityRepository {
	use SearchableRepositoryTrait;

	public function __construct ( ManagerRegistry $registry ) {
		parent::__construct( $registry, Document::class );
	}

	/**
	 * @param \App\Entity\Usergroup $group
	 *
	 * @return Document[] Returns an array of Document objects
	 */
	public function findRootDocuments ( Usergroup $group ) {
		return $this->createQueryBuilder( 'd' )
					->andWhere( 'd.usergroup = :group' )
					->setParameter( 'group', $group )
					->andWhere( 'd.folder IS NULL' )
					->orderBy( 'd.title', 'ASC' )
					->getQuery()
					->getResult();
	}

	/*
	public function findOneBySomeField($value): ?Document
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
