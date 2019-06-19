<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Usergroup;
use App\Entity\UsergroupMembership;
use App\Security\GroupVoter;
use App\Security\UserVoter;
use App\Service\UsergroupMembersManager;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class GroupMembersController extends AbstractController {
	/**
	 * @Route("/groups/{groupSlug}/members", name="group_members_index")
	 * @param                                            $groupSlug
	 * @param \Symfony\Component\HttpFoundation\Request  $request
	 * @param \Doctrine\Common\Persistence\ObjectManager $manager
	 * @param \App\Service\UsergroupMembersManager       $usergroupMembersManager
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function groupMembers (
			$groupSlug,
			Request $request,
			ObjectManager $manager,
			UsergroupMembersManager $usergroupMembersManager
	) {
		$this->denyAccessUnlessGranted( UserVoter::LOGGED );

		/**
		 * @var \App\Entity\Usergroup $group
		 */
		$group = $manager->getRepository( Usergroup::class )
						 ->findOneBy( [ 'slug' => $groupSlug ] );

		if ( !$group ) {
			throw $this->createNotFoundException( 'The group does not exist' );
		}

		$this->denyAccessUnlessGranted( GroupVoter::READ, $group );

		$page     = $request->query->get( 'page', 0 );
		$per_page = 10;

		$filters = $request->query->get( 'form', [] );

		if ( $this->isGranted( GroupVoter::ADMIN, $group ) ) {
			$dataFilters = array_merge( $filters, [
					'group'  => $group,
					'status' => UsergroupMembership::STATUS_ALL,
			] );
		}
		else {
			$dataFilters = array_merge( $filters, [
					'group' => $group,
			] );
		}

		$data = $usergroupMembersManager->getFormAndMembers(
				$dataFilters,
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
				'group'   => $group,
				'form'    => $data[ 'form' ]->createView(),
				'members' => $data[ 'members' ],
				'pager'   => [
						'base_url' => $request->getPathInfo() . '?' . http_build_query( [ 'form' => $filters ] ) . '&',
						'page'     => $page,
						'last'     => ceil( $data[ 'total' ] / $per_page ) - 1,
				],
		] );
	}

	/**
	 * @Route("/groups/{groupSlug}/members/new", name="group_member_new")
	 */
	public function groupMemberNew (
			$groupSlug,
			ObjectManager $manager
	) {
		$group = $manager->getRepository( Usergroup::class )
						 ->findOneBy( [ 'slug' => $groupSlug ] );

		$user = $this->getUser();

		$this->denyAccessUnlessGranted( UserVoter::LOGGED );

		$membership = $manager->getRepository( UsergroupMembership::class )
							  ->getMembership( $user, $group );

		if ( !empty( $membership ) ) {
			if ( $membership->getStatus() === UsergroupMembership::STATUS_BANNED ) {
				$this->addFlash( 'messages.group.user_banned' );
			}

			return $this->redirectToRoute( 'group_index', [ 'groupSlug' => $groupSlug ] );
		}

		$membership = new UsergroupMembership();
		$membership->setUsergroup( $group );
		$membership->setUser( $user );
		$membership->setRole( UsergroupMembership::ROLE_USER );
		$membership->setJoinedAt( new \DateTime() );

		if ( $this->isGranted( GroupVoter::JOIN, $group ) ) {
			$membership->setStatus( UsergroupMembership::STATUS_MEMBER );

			$this->addFlash( 'notice', 'messages.group.joined' );
		}
		else {
			$membership->setStatus( UsergroupMembership::STATUS_PENDING );

			$this->addFlash( 'notice', 'messages.group.candidature_sent' );
		}

		// TODO : Add Log

		$manager->persist( $membership );
		$manager->flush();

		return $this->redirectToRoute( 'group_index', [ 'groupSlug' => $groupSlug ] );
	}

	/**
	 * @Route("/groups/{groupSlug}/members/{userId}/admin/{status}", name="group_member_admin")
	 * @param                                            $groupSlug
	 * @param                                            $userId
	 * @param                                            $status
	 * @param \Doctrine\Common\Persistence\ObjectManager $manager
	 *
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 * @throws \Exception
	 */
	public function groupMemberJoin (
			$groupSlug,
			$userId,
			$status,
			ObjectManager $manager
	) {
		/**
		 * @var \App\Entity\Usergroup $group
		 */
		$group = $manager->getRepository( Usergroup::class )
						 ->findOneBy( [ 'slug' => $groupSlug ] );

		if ( !$group ) {
			throw $this->createNotFoundException( 'The group does not exist' );
		}

		$this->denyAccessUnlessGranted( GroupVoter::ADMIN, $group );

		$user = $manager->getRepository( User::class )
						->findOneBy( [ 'id' => $userId ] );

		if ( !$user ) {
			throw $this->createNotFoundException( 'The user does not exist' );
		}

		$membership = $manager->getRepository( UsergroupMembership::class )
							  ->getMembership( $user, $group );

		switch ( $status ) {
			case UsergroupMembership::STATUS_MEMBER:
				if ( empty( $membership ) ) {
					$membership = new UsergroupMembership();
					$membership->setUsergroup( $group );
					$membership->setUser( $user );

					$manager->persist( $membership );
				}

				$membership->setJoinedAt( new \DateTime() );
				$membership->setStatus( UsergroupMembership::STATUS_MEMBER );

				$this->addFlash( 'notice', 'messages.group.user_set_member' );
				break;

			case 'remove':
				if ( !empty( $membership ) ) {
					$manager->remove( $membership );

					$this->addFlash( 'notice', 'messages.group.user_set_remove' );
				}
				break;

			case UsergroupMembership::STATUS_BANNED:
				if ( !empty( $membership ) ) {
					$membership->setStatus( UsergroupMembership::STATUS_BANNED );

					$this->addFlash( 'notice', 'messages.group.user_set_banned' );
				}
				break;

			case UsergroupMembership::ROLE_ADMIN:
				if ( !empty( $membership ) ) {
					$membership->setRole( UsergroupMembership::ROLE_ADMIN );
					$membership->setStatus( UsergroupMembership::STATUS_MEMBER );

					$this->addFlash( 'notice', 'messages.group.user_set_admin' );
				}
				break;

			case UsergroupMembership::ROLE_USER:
				if ( !empty( $membership ) ) {
					$membership->setRole( UsergroupMembership::ROLE_USER );
					$membership->setStatus( UsergroupMembership::STATUS_MEMBER );

					$this->addFlash( 'notice', 'messages.group.user_set_user' );
				}
				break;
		}

		$manager->flush();

		// TODO : Add Log

		return $this->redirectToRoute( 'group_members_index', [ 'groupSlug' => $groupSlug ] );
	}
}
