<?php
/**
 * User: Maxime Cousinou
 * Date: 2019-03-08
 * Time: 12:06
 */

namespace App\Controller;

use App\Entity\Usergroup;
use App\Entity\UsergroupMembership;
use App\Security\GroupVoter;
use App\Security\UserVoter;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class GroupMembersController extends AbstractController {
	/**
	 * @Route("/groups/{groupSlug}/members", name="group_members_index")
	 */
	public function groupMembers ( $groupSlug ) {
		return '#TODO';
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
