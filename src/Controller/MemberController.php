<?php

namespace App\Controller;

use App\Entity\User;
use App\Security\UserVoter;
use App\Service\FileManager;
use App\Service\UserAnonymize;
use App\Service\UsergroupMembersManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
		if ( !$this->isGranted( UserVoter::LOGGED ) ) {
			$this->addFlash( 'notice', 'messages.user.login_requested' );
		}

		$this->denyAccessUnlessGranted( UserVoter::LOGGED );

		$page     = $request->query->get( 'page', 0 );
		$per_page = 20;

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
	 * @param \Doctrine\ORM\EntityManagerInterface       $manager
	 *
	 * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Symfony\Component\HttpFoundation\Response
	 */
	public function member (
			$user_id,
			EntityManagerInterface $manager
	) {
		if ( !$this->isGranted( UserVoter::LOGGED ) ) {
			$this->addFlash( 'notice', 'messages.user.login_requested' );
		}

		$this->denyAccessUnlessGranted( UserVoter::LOGGED );

		$user = $manager->getRepository( User::class )
						->findOneBy( [ 'id' => $user_id ] );

		if ( !$user ) {
			$this->addFlash( 'error', 'messages.user.unknown' );

			return $this->redirectToRoute( 'homepage' );
		}

		if ( $user->getStatus() !== User::STATUS_ACTIVE ) {
			$this->addFlash( 'error', 'messages.user.inactive' );

			return $this->redirectToRoute( 'homepage' );
		}

		return $this->render( 'pages/member/member-profile.html.twig', [ 'user' => $user ] );
	}

	/**
	 * @Route("/members/{user_id}/avatar", name="member_avatar")
	 *
	 * @param                                            $user_id
	 * @param \Doctrine\ORM\EntityManagerInterface       $manager
	 * @param \App\Service\FileManager                   $fileManager
	 *
	 * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Symfony\Component\HttpFoundation\Response
	 */
	public function memberAvatar (
			$user_id,
			EntityManagerInterface $manager,
			FileManager $fileManager
	) {
		$user = $manager->getRepository( User::class )
						->findOneBy( [ 'id' => $user_id ] );

		if ( !empty( $user ) ) {
			$file = $user->getAvatar();

			if ( !empty( $file ) ) {
				return $fileManager->getFile( $file );
			}
		}

		throw $this->createNotFoundException( 'User does not have an avatar' );
	}

	/**
	 * @Route("/members/{user_id}/delete", name="member_delete")
	 *
	 * @param                        $user_id
	 * @param Request                $request
	 * @param EntityManagerInterface $manager
	 * @param UserAnonymize          $userAnonymize
	 * @return BinaryFileResponse|Response
	 */
	public function memberDelete (
			$user_id,
			Request $request,
			EntityManagerInterface $manager,
			UserAnonymize $userAnonymize
	) {
		$this->denyAccessUnlessGranted(User::ROLE_ADMIN);

		// Delete confirmation form

		$form = $this->createFormBuilder()
					 ->add( 'submit', SubmitType::class )
					 ->getForm();

		$form->handleRequest( $request );

		if ( $form->isSubmitted() && $form->isValid() ) {
			$user = $manager->getRepository( User::class )
							->findOneBy( [ 'id' => $user_id ] );

			if ( !$user ) {
				$this->addFlash( 'error', 'messages.user.unknown' );

				return $this->redirectToRoute( 'homepage' );
			}

			if ( $user->getStatus() === User::STATUS_DISABLED ) {
				$this->addFlash( 'error', 'messages.user.inactive' );

				return $this->redirectToRoute( 'homepage' );
			}
			$userAnonymize->anonymize();
			$manager->flush();

			$this->addFlash( 'notice', 'User deleted' );

			return $this->redirectToRoute( 'members' );
		}

		return $this->render( 'pages/confirm.html.twig', [
				'form' => $form->createView(),
		] );
	}
}
