<?php

namespace App\Service;

use App\Entity\Skill;
use App\Entity\User;
use App\Repository\SkillRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Container\ContainerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Intl\Intl;

class UsergroupMembersManager {
	private $manager;
	private $formFactory;

	public function __construct ( ObjectManager $manager, ContainerInterface $container ) {
		$this->manager     = $manager;
		$this->formFactory = $container->get( 'form.factory' );
	}

	public function getFormAndMembers ( $filters, $options = [] ) {
		$manager = $this->manager;

		/**
		 * @var \App\Repository\UserRepository $usersRepository
		 */
		$usersRepository = $manager->getRepository( User::class );

		// Countries

		$countries = array_map( function ( $item ) {
			return $item[ 'country' ];
		}, $usersRepository->getCountries( $filters ) );

		$countriesNames = array_filter( array_flip( Intl::getRegionBundle()->getCountryNames() ), function ( $item ) use ( $countries ) {
			return in_array( $item, $countries );
		} );

		// Map Skills

		if ( !empty( $filters[ 'skills' ] ) ) {
			$filters[ 'skills' ] = array_map( function ( $id ) use ( $manager ) {
				return $manager->getRepository( Skill::class )->findOneBy( [ 'id' => $id ] );
			}, $filters[ 'skills' ] );
		}

		// Build form

		$form = $this->formFactory->createBuilder( FormType::class, $filters )
								  ->setMethod( 'get' )
								  ->add( 'country', ChoiceType::class, [
										  'required' => FALSE,
										  'expanded' => TRUE,
										  'multiple' => TRUE,
										  'choices'  => $countriesNames,
								  ] )
								  ->add( 'inscriptionType', ChoiceType::class, [
										  'required' => FALSE,
										  'expanded' => TRUE,
										  'multiple' => TRUE,
										  'choices'  => array_combine( [
												  'pages.member.list.filters.inscription_type.labels.' . User::TYPE_PRIVATE,
												  'pages.member.list.filters.inscription_type.labels.' . User::TYPE_PROFESSIONNAL,
										  ], [
												  User::TYPE_PRIVATE,
												  User::TYPE_PROFESSIONNAL,
										  ] ),
								  ] )
								  ->add( 'skills', EntityType::class, [
										  'class'                     => Skill::class,
										  'required'                  => FALSE,
										  'expanded'                  => TRUE,
										  'multiple'                  => TRUE,
										  'query_builder'             => function ( SkillRepository $repository ) {
											  return $repository->createQueryBuilder( 'u' )
																->orderBy( 'u.slug', 'ASC' );
										  },
										  'choice_translation_domain' => 'skills',
										  'choice_label'              => 'slug',
								  ] )
								  ->add( 'query', SearchType::class, [
										  'required' => FALSE,
								  ] )
								  ->add( 'submit', SubmitType::class )
								  ->getForm();

		$total   = $usersRepository->searchCount( $filters );
		$members = $usersRepository->search( $filters, [ 'page' => $options[ 'page' ], 'limit' => $options[ 'per_page' ] ] );

		return [
				'form'    => $form,
				'total'   => $total,
				'members' => $members,
		];
	}
}
