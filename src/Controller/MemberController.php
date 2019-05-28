<?php
/**
 * User: Maxime Cousinou
 * Date: 2019-04-19
 * Time: 11:36
 */

namespace App\Controller;

use App\Entity\Skill;
use App\Entity\User;
use App\Repository\SkillRepository;
use App\Service\FileManager;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Intl\Intl;
use Symfony\Component\Routing\Annotation\Route;

class MemberController extends AbstractController {
	/**************************************************
	 * MEMBERS
	 **************************************************/

	/**
	 * @Route("/members", name="members")
	 *
	 * @param \Symfony\Component\HttpFoundation\Request  $request
	 * @param \Doctrine\Common\Persistence\ObjectManager $manager
	 *
	 * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Symfony\Component\HttpFoundation\Response
	 */
	public function members (
			Request $request,
			ObjectManager $manager
	) {
		$page = $request->query->get( 'page', 0 );

		/**
		 * @var \App\Repository\UserRepository $usersRepository
		 */
		$usersRepository = $manager->getRepository( User::class );

		$countries      = array_map( function ( $item ) {
			return $item[ 'country' ];
		}, $usersRepository->getCountries() );
		$countriesNames = array_filter( array_flip( Intl::getRegionBundle()->getCountryNames() ), function ( $item ) use ( $countries ) {
			return in_array( $item, $countries );
		} );

		$filters = $request->query->get( 'form' );
		unset( $filters[ '_token' ] );
		unset( $filters[ 'submit' ] );

		if ( !empty( $filters[ 'skills' ] ) ) {
			$filters[ 'skills' ] = array_map( function ( $id ) use ( $manager ) {
				return $manager->getRepository( Skill::class )->findOneBy( [ 'id' => $id ] );
			}, $filters[ 'skills' ] );
		}

		$form = $this->createFormBuilder( $filters )
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

		$per_page = 5;
		$total    = $usersRepository->searchCount( $filters );
		$members  = $usersRepository->search( $filters, [ 'page' => $page, 'limit' => $per_page ] );

		return $this->render( 'pages/member/list.html.twig', [
				'form'    => $form->createView(),
				'members' => $members,
				'pager'   => [
						'base_url' => $request->getPathInfo() . '?' . http_build_query( [ 'form' => $filters ] ) . '&',
						'page'     => $page,
						'last'     => ceil( $total / $per_page ) - 1,
				],
		] );
	}

	/**************************************************
	 * MEMBER
	 **************************************************/

	/**
	 * @Route("/members/{user_id}", name="member")
	 *
	 * @param                                            $user_id
	 * @param \Doctrine\Common\Persistence\ObjectManager $manager
	 * @param \App\Service\FileManager                   $fileManager
	 *
	 * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Symfony\Component\HttpFoundation\Response
	 */
	public function member (
			$user_id,
			ObjectManager $manager,
			FileManager $fileManager
	) {
		$user = $manager->getRepository( User::class )
						->findOneById( $user_id );

		return $this->render( 'pages/member/view.html.twig', [ 'user' => $user ] );
	}

	/**
	 * @Route("/members/{user_id}/avatar", name="member_avatar")
	 *
	 * @param                                            $user_id
	 * @param \Doctrine\Common\Persistence\ObjectManager $manager
	 * @param \App\Service\FileManager                   $fileManager
	 *
	 * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Symfony\Component\HttpFoundation\Response
	 */
	public function memberAvatar (
			$user_id,
			ObjectManager $manager,
			FileManager $fileManager
	) {
		$user = $manager->getRepository( User::class )
						->findOneById( $user_id );

		if ( !empty( $user ) ) {
			$file = $user->getAvatar();

			if ( !empty( $file ) ) {
				return $fileManager->getFile( $file );
			}
		}

		throw $this->createNotFoundException( 'User does not have an avatar' );
	}
}
