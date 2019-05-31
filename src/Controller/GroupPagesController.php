<?php

namespace App\Controller;

use App\Entity\Page;
use App\Entity\Usergroup;
use App\Security\GroupPageVoter;
use App\Security\GroupVoter;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
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
	 * @param                                            $groupSlug
	 * @param \Doctrine\Common\Persistence\ObjectManager $manager
	 *
	 * @return string
	 */
	public function groupPageNew ( $groupSlug, ObjectManager $manager ) {
		$group = $manager->getRepository( Usergroup::class )
						 ->findOneBy( [ 'slug' => $groupSlug ] );

		$this->denyAccessUnlessGranted( GroupPageVoter::CREATE, $group );

		return new Response( '#TODO' );
	}

	/**
	 * @Route("/groups/{groupSlug}/pages/{pageSlug}/edit", name="group_page_edit")
	 * @param                                            $groupSlug
	 * @param                                            $pageSlug
	 * @param \Doctrine\Common\Persistence\ObjectManager $manager
	 *
	 * @return string
	 */
	public function groupPageEdit ( $groupSlug, $pageSlug, ObjectManager $manager ) {
		$group = $manager->getRepository( Usergroup::class )
						 ->findOneBy( [ 'slug' => $groupSlug ] );

		$page = $manager->getRepository( Page::class )
						->findOneBy( [ 'usergroup' => $group, 'slug' => $pageSlug ] );

		$this->denyAccessUnlessGranted( GroupPageVoter::EDIT, $page );

		return new Response( '#TODO' );
	}

	/**
	 * @Route("/groups/{groupSlug}/pages/{pageSlug}", name="group_page_index")
	 * @param                                            $groupSlug
	 * @param                                            $pageSlug
	 * @param \Doctrine\Common\Persistence\ObjectManager $manager
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function groupPage ( $groupSlug, $pageSlug, ObjectManager $manager ) {
		$group = $manager->getRepository( Usergroup::class )
						 ->findOneBy( [ 'slug' => $groupSlug ] );

		$page = $manager->getRepository( Page::class )
						->findOneBy( [ 'usergroup' => $group, 'slug' => $pageSlug ] );

		return $this->render( 'pages/group/group-page.html.twig', [
				'group' => $group,
				'page'  => $page,
		] );
	}
}
