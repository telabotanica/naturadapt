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

class GroupPagesController extends AbstractController {
	/**
	 * @Route("/groups/{groupSlug}/pages", name="group_pages_index")
	 */
	public function groupPages ( $groupSlug ) {
		return '#TODO';
	}

	/**
	 * @Route("/groups/{groupSlug}/pages/new", name="group_page_new")
	 */
	public function groupPageNew ( $groupSlug ) {
		return '#TODO';
	}

	/**
	 * @Route("/groups/{groupSlug}/pages/{pageSlug}/edit", name="group_page_edit")
	 */
	public function groupPageEdit ( $groupSlug, $pageSlug ) {
		return '#TODO';
	}

	/**
	 * @Route("/groups/{groupSlug}/pages/{pageSlug}", name="group_page_index")
	 */
	public function groupPage ( $groupSlug, $pageSlug ) {
		/**
		 * @var $user \App\Entity\User
		 */
		$user = $this->getUser();

		$group = $this->getDoctrine()
					  ->getRepository( Usergroup::class )
					  ->findOneBy( [ 'slug' => $groupSlug ] );

		$page = $this->getDoctrine()
					 ->getRepository( Page::class )
					 ->findOneBy( [ 'usergroup' => $group, 'slug' => $pageSlug ] );

		return $this->render( 'pages/group/group-page.html.twig', [
				'group' => $group,
				'page'  => $page,
		] );
	}
}
