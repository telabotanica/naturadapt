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
	public function groupMemberNew ( $groupSlug ) {
		return '#TODO';
	}
}
