<?php

namespace App\Repository;

use App\Entity\Skill;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Skill|null find( $id, $lockMode = NULL, $lockVersion = NULL )
 * @method Skill|null findOneBy( array $criteria, array $orderBy = NULL )
 * @method Skill[]    findAll()
 * @method Skill[]    findBy( array $criteria, array $orderBy = NULL, $limit = NULL, $offset = NULL )
 */
class SkillRepository extends ServiceEntityRepository {
	public function __construct ( RegistryInterface $registry ) {
		parent::__construct( $registry, Skill::class );
	}

	// /**
	//  * @return Skill[] Returns an array of Skill objects
	//  */
	/*
	public function findByExampleField($value)
	{
		return $this->createQueryBuilder('s')
			->andWhere('s.exampleField = :val')
			->setParameter('val', $value)
			->orderBy('s.id', 'ASC')
			->setMaxResults(10)
			->getQuery()
			->getResult()
		;
	}
	*/

	/*
	public function findOneBySomeField($value): ?Skill
	{
		return $this->createQueryBuilder('s')
			->andWhere('s.exampleField = :val')
			->setParameter('val', $value)
			->getQuery()
			->getOneOrNullResult()
		;
	}
	*/
}
