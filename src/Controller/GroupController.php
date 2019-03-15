<?php
/**
 * User: Maxime Cousinou
 * Date: 2019-03-08
 * Time: 12:06
 */

namespace App\Controller;

use App\Entity\Usergroup;
use App\Entity\Page;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class GroupController extends AbstractController {
	/**************************************************
	 * GROUPS
	 **************************************************/

	/**
	 * @Route("/groups", name="groups_index")
	 */
	public function groupsIndex () {
		/**
		 * @var $user \App\Entity\User
		 */
		$user = $this->getUser();

		$groups = $this->getDoctrine()
					   ->getRepository( Usergroup::class )
					   ->findAll();

		return $this->render( 'pages/group/groups-index.html.twig', [
				'user'   => $user,
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
	 */
	public function groupEdit ( $groupSlug ) {
		return '#TODO';
	}

	/**
	 * @Route("/groups/{groupSlug}", name="group_index")
	 */
	public function groupIndex ( $groupSlug ) {
		/**
		 * @var $user \App\Entity\User
		 */
		$user = $this->getUser();

		$group = $this->getDoctrine()
					  ->getRepository( Usergroup::class )
					  ->findOneBy( [ 'slug' => $groupSlug ] );

		return $this->render( 'pages/group/group-index.html.twig', [
				'group' => $group,
		] );
	}
}
