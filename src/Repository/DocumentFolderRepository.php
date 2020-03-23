<?php

namespace App\Repository;

use App\Entity\DocumentFolder;
use App\Entity\Usergroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method DocumentFolder|null find( $id, $lockMode = NULL, $lockVersion = NULL )
 * @method DocumentFolder|null findOneBy( array $criteria, array $orderBy = NULL )
 * @method DocumentFolder[]    findAll()
 * @method DocumentFolder[]    findBy( array $criteria, array $orderBy = NULL, $limit = NULL, $offset = NULL )
 */
class DocumentFolderRepository extends ServiceEntityRepository {
	public function __construct ( RegistryInterface $registry ) {
		parent::__construct( $registry, DocumentFolder::class );
	}

	/**
	 * @param \App\Entity\Usergroup $group
	 *
	 * @return mixed
	 */
	public function findForGroup ( Usergroup $group ) {
		return $this->createQueryBuilder( 'd' )
					->andWhere( 'd.usergroup = :group' )
					->setParameter( 'group', $group )
					->orderBy( 'd.title', 'ASC' )
					->getQuery()
					->getResult();
	}
}
