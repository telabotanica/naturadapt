<?php
/**
 * User: Maxime Cousinou
 * Date: 2019-03-08
 * Time: 12:06
 */

namespace App\Controller;

use App\Entity\Usergroup;
use App\Security\GroupVoter;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GroupController extends AbstractController {
	/**************************************************
	 * GROUPS
	 **************************************************/

	/**
	 * @Route("/groups", name="groups_index")
	 */
	public function groupsIndex ( ObjectManager $manager ) {
		$groups = $manager->getRepository( Usergroup::class )
						  ->getGroupsWithMembers();

		return $this->render( 'pages/group/groups-index.html.twig', [
				'groups' => $groups,
		] );
	}

	/**************************************************
	 * GROUP
	 **************************************************/

	/**
	 * @Route("/groups/new", name="group_new")
	 */
	public function groupNew () {
		return '#TODO';
	}

	/**
	 * @Route("/groups/{groupSlug}/edit", name="group_edit")
	 * @param                                            $groupSlug
	 * @param \Doctrine\Common\Persistence\ObjectManager $manager
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function groupEdit ( $groupSlug, ObjectManager $manager ) {
		$group = $manager->getRepository( Usergroup::class )
						 ->findOneBy( [ 'slug' => $groupSlug ] );

		$this->denyAccessUnlessGranted( GroupVoter::EDIT, $group );

		return new Response( '#TODO' );
	}

	/**
	 * @Route("/groups/{groupSlug}", name="group_index")
	 * @param                                            $groupSlug
	 * @param \Doctrine\Common\Persistence\ObjectManager $manager
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function groupIndex ( $groupSlug, ObjectManager $manager ) {
		$group = $manager->getRepository( Usergroup::class )
						 ->findOneBy( [ 'slug' => $groupSlug ] );

		$userCanRead = $this->isGranted( GroupVoter::READ, $group );
		$userCanEdit = $this->isGranted( GroupVoter::EDIT, $group );

		return $this->render( 'pages/group/group-index.html.twig', [
				'userCanRead' => $userCanRead,
				'group'       => $group,
		] );
	}
}
