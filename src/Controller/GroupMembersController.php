<?php
/**
 * User: Maxime Cousinou
 * Date: 2019-03-08
 * Time: 12:06
 */

namespace App\Controller;

use App\Entity\Usergroup;
use App\Entity\Page;
use App\Entity\UsergroupMembership;
use App\Security\GroupVoter;
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

	public function groupMembersTiny ( $groupSlug, ObjectManager $manager ) {
		$group = $manager->getRepository( Usergroup::class )
						 ->findOneBy( [ 'slug' => $groupSlug ] );

		$members = $manager->getRepository( UsergroupMembership::class )
						   ->getMembers( $group, 5 );

		$count = $manager->getRepository( UsergroupMembership::class )
						 ->countMembers( $group );

		return $this->render( 'components/group/group-members--tiny.html.twig', [
				'members' => $members,
				'count'   => $count,
		] );
	}

	public function groupMemberJoinButton ( $groupSlug, ObjectManager $manager ) {
		$group = $manager->getRepository( Usergroup::class )
						 ->findOneBy( [ 'slug' => $groupSlug ] );

		$user = $this->getUser();

		$isMember = $manager->getRepository( UsergroupMembership::class )
							->isMember( $user, $group );

		return $this->render( 'components/group/group-join-button.html.twig', [
				'group'    => $group,
				'isMember' => $isMember,
		] );
	}

	/**
	 * @Route("/groups/{groupSlug}/members/new", name="group_member_new")
	 */
	public function groupMemberNew ( $groupSlug, ObjectManager $manager ) {
		$group = $manager->getRepository( Usergroup::class )
						 ->findOneBy( [ 'slug' => $groupSlug ] );

		$user = $this->getUser();

		$isMember = $manager->getRepository( UsergroupMembership::class )
							->isMember( $user, $group );

		if ( $isMember ) {
			return $this->redirectToRoute( 'group_index', [ 'groupSlug' => $groupSlug ] );
		}

		if ( $this->isGranted( GroupVoter::JOIN, $group ) ) {
			$membership = new UsergroupMembership();
			$membership->setUsergroup( $group );
			$membership->setUser( $user );
			$membership->setJoinedAt( new \DateTime() );
			$membership->setRole( '' );

			$manager->persist( $membership );
			$manager->flush();

			return $this->redirectToRoute( 'group_index', [ 'groupSlug' => $groupSlug ] );
		}

		// TODO
		$this->addFlash( 'notice', 'group.candidature_sent' );

		return $this->redirectToRoute( 'group_index', [ 'groupSlug' => $groupSlug ] );
	}
}
