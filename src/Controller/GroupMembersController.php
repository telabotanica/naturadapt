<?php

namespace App\Controller;

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
		$group = $manager->getRepository( Usergroup::class )
						 ->findOneBy( [ 'slug' => $groupSlug ] );

		$this->denyAccessUnlessGranted( GroupVoter::READ, $group );

		$page     = $request->query->get( 'page', 0 );
		$per_page = 10;

		$filters = $request->query->get( 'form', [] );

		if ( $this->isGranted( GroupVoter::ADMIN, $group ) ) {
			$dataFilters = array_merge( $filters, [ 'group' => $group, 'status' => UsergroupMembership::STATUS_ALL ] );
		}
		else {
			$dataFilters = array_merge( $filters, [ 'group' => $group ] );
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

		return $this->render( 'pages/member/list.html.twig', [
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
	public function groupMemberNew ( $groupSlug, ObjectManager $manager ) {
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

		$manager->persist( $membership );
		$manager->flush();

		return $this->redirectToRoute( 'group_index', [ 'groupSlug' => $groupSlug ] );
	}
}
