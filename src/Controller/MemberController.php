<?php

namespace App\Controller;

use App\Entity\User;
use App\Security\UserVoter;
use App\Service\FileManager;
use App\Service\UsergroupMembersManager;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MemberController extends AbstractController {
	/**************************************************
	 * MEMBERS
	 **************************************************/

	/**
	 * @Route("/members", name="members")
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param \App\Service\UsergroupMembersManager      $usergroupMembersManager
	 *
	 * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Symfony\Component\HttpFoundation\Response
	 */
	public function members (
			Request $request,
			UsergroupMembersManager $usergroupMembersManager
	) {
		$this->denyAccessUnlessGranted( UserVoter::LOGGED );

		$page     = $request->query->get( 'page', 0 );
		$per_page = 10;

		$filters = $request->query->get( 'form', [] );

		$data = $usergroupMembersManager->getFormAndMembers(
				$filters,
				[ 'page' => $page, 'per_page' => $per_page ]
		);

		foreach ( $filters as $key => $value ) {
			if ( $value == '' || $value == [] ) {
				unset( $filters[ $key ] );
			}
		}
		unset( $filters[ '_token' ] );
		unset( $filters[ 'submit' ] );

		return $this->render( 'pages/member/members-index.html.twig', [
				'form'    => $data[ 'form' ]->createView(),
				'members' => $data[ 'members' ],
				'pager'   => [
						'base_url' => $request->getPathInfo() . '?' . http_build_query( [ 'form' => $filters ] ) . '&',
						'page'     => $page,
						'last'     => ceil( $data[ 'total' ] / $per_page ) - 1,
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
		$this->denyAccessUnlessGranted( UserVoter::LOGGED );

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

	/**
	 * @Route("/members/{user_id}/delete")
	 *
	 * @param                                            $user_id
	 * @param \Doctrine\Common\Persistence\ObjectManager $manager
	 * @param \App\Service\FileManager                   $fileManager
	 *
	 * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Symfony\Component\HttpFoundation\Response
	 */
	public function memberDelete (
			$user_id,
			ObjectManager $manager,
			FileManager $fileManager
	) {
		if ( $this->getUser() && $this->getUser()->isAdmin() ) {
			$user = $manager->getRepository( User::class )
							->findOneById( $user_id );

			$manager->remove( $user );
			$manager->flush();

			$this->addFlash( 'notice', 'User deleted' );
		}

		return $this->redirectToRoute( 'homepage' );
	}
}
