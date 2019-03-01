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
		$user = $this->getUser ();

		$groups = $this->getDoctrine ()
					   ->getRepository ( Usergroup::class )
					   ->findAll ();

		return $this->render ( 'pages/group/groups-index.html.twig', [
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
	 * @Route("/groups/{slug}/edit", name="group_edit")
	 */
	public function groupEdit ( $slug ) {
		return '#TODO';
	}

	/**
	 * @Route("/groups/{slug}", name="group_index")
	 */
	public function groupIndex ( $slug ) {
		/**
		 * @var $user \App\Entity\User
		 */
		$user = $this->getUser ();

		$group = $this->getDoctrine ()
					  ->getRepository ( Usergroup::class )
					  ->findOneBy ( [ 'slug' => $slug ] );

		return $this->render ( 'pages/group/group-index.html.twig', [
				'group' => $group,
		] );
	}

	/**************************************************
	 * GROUP MEMBERS
	 **************************************************/

	/**
	 * @Route("/groups/{slug}/members", name="group_members_index")
	 */
	public function groupMembers ( $slug ) {
		return '#TODO';
	}

	/**
	 * @Route("/groups/{slug}/members/new", name="group_member_new")
	 */
	public function groupMemberNew ( $slug ) {
		return '#TODO';
	}

	/**************************************************
	 * GROUP ARTICLES
	 **************************************************/

	/**
	 * @Route("/groups/{slug}/articles", name="group_articles_index")
	 */
	public function groupArticles ( $slug ) {
		return '#TODO';
	}

	/**
	 * @Route("/groups/{slug}/articles/new", name="group_article_new")
	 */
	public function groupArticleNew ( $slug ) {
		return '#TODO';
	}

	/**
	 * @Route("/groups/{slug}/articles/{articleSlug}/edit", name="group_article_edit")
	 */
	public function groupArticleEdit ( $slug, $articleSlug ) {
		return '#TODO';
	}

	/**
	 * @Route("/groups/{slug}/articles/{articleSlug}", name="group_article_index")
	 */
	public function groupArticle ( $slug, $articleSlug ) {
		return '#TODO';
	}

	/**************************************************
	 * GROUP PAGES
	 **************************************************/

	/**
	 * @Route("/groups/{slug}/pages", name="group_pages_index")
	 */
	public function groupPages ( $slug ) {
		return '#TODO';
	}

	/**
	 * @Route("/groups/{slug}/pages/new", name="group_page_new")
	 */
	public function groupPageNew ( $slug ) {
		return '#TODO';
	}

	/**
	 * @Route("/groups/{slug}/pages/{pageSlug}/edit", name="group_page_edit")
	 */
	public function groupPageEdit ( $slug, $pageSlug ) {
		return '#TODO';
	}

	/**
	 * @Route("/groups/{slug}/pages/{pageSlug}", name="group_page_index")
	 */
	public function groupPage ( $slug, $pageSlug ) {
		/**
		 * @var $user \App\Entity\User
		 */
		$user = $this->getUser ();

		$group = $this->getDoctrine ()
					  ->getRepository ( Usergroup::class )
					  ->findOneBy ( [ 'slug' => $slug ] );

		$page = $this->getDoctrine ()
					 ->getRepository ( Page::class )
					 ->findOneBy ( [ 'usergroup' => $group, 'slug' => $pageSlug ] );

		return $this->render ( 'pages/group/group-page.html.twig', [
				'group' => $group,
				'page'  => $page,
		] );
	}
}
