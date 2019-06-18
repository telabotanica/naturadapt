<?php

namespace App\Controller;

use App\Entity\Page;
use App\Entity\Usergroup;
use App\Form\PageType;
use App\Security\GroupPageVoter;
use App\Service\SlugGenerator;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GroupPagesController extends AbstractController {
	/**
	 * @Route("/groups/{groupSlug}/pages", name="group_pages_index")
	 */
	public function groupPages ( $groupSlug ) {
		// TODO : Create list

		return new Response( "#TODO" );
	}

	/**
	 * @Route("/groups/{groupSlug}/pages/new", name="group_page_new")
	 * @param                                                            $groupSlug
	 * @param \Symfony\Component\HttpFoundation\Request                  $request
	 * @param \Doctrine\Common\Persistence\ObjectManager                 $manager
	 * @param \App\Service\SlugGenerator                                 $slugGenerator
	 * @param \Symfony\Component\Routing\Generator\UrlGeneratorInterface $router
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function groupPageNew (
			$groupSlug,
			Request $request,
			ObjectManager $manager,
			SlugGenerator $slugGenerator,
			UrlGeneratorInterface $router
	) {
		/**
		 * @var \App\Entity\Usergroup $group
		 */
		$group = $manager->getRepository( Usergroup::class )
						 ->findOneBy( [ 'slug' => $groupSlug ] );

		if ( !$group ) {
			throw $this->createNotFoundException( 'The group does not exist' );
		}

		$this->denyAccessUnlessGranted( GroupPageVoter::CREATE, $group );

		$page = new Page();
		$form = $this->createForm( PageType::class, $page );

		$form->handleRequest( $request );

		if ( $form->isSubmitted() && $form->isValid() ) {
			$page->setAuthor( $this->getUser() );
			$page->setUsergroup( $group );
			$page->setCreatedAt( new \DateTime() );
			$page->setSlug( $slugGenerator->generateSlug( $page->getTitle(), Page::class, 'slug', [ 'usergroup' => $group ] ) );

			// TODO : Add Versioning
			// TODO : Add Log

			$manager->persist( $page );
			$manager->flush();

			$this->addFlash( 'notice', 'messages.page.page_created' );

			return $this->redirectToRoute( 'group_page_index', [ 'groupSlug' => $group->getSlug(), 'pageSlug' => $page->getSlug() ] );
		}

		return $this->render( 'pages/page/page-create.html.twig', [
				'group'  => $group,
				'page'   => $page,
				'form'   => $form->createView(),
				'upload' => $router->generate( 'file_upload', [ 'groupId' => $group->getId() ] ),
		] );
	}

	/**
	 * @Route("/groups/{groupSlug}/pages/{pageSlug}", name="group_page_index")
	 * @param                                            $groupSlug
	 * @param                                            $pageSlug
	 * @param \Doctrine\Common\Persistence\ObjectManager $manager
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function groupPage (
			$groupSlug,
			$pageSlug,
			ObjectManager $manager
	) {
		/**
		 * @var \App\Entity\Usergroup $group
		 */
		$group = $manager->getRepository( Usergroup::class )
						 ->findOneBy( [ 'slug' => $groupSlug ] );

		/**
		 * @var \App\Entity\Page $page
		 */
		$page = $manager->getRepository( Page::class )
						->findOneBy( [ 'usergroup' => $group, 'slug' => $pageSlug ] );

		return $this->render( 'pages/page/page-index.html.twig', [
				'group' => $group,
				'page'  => $page,
		] );
	}

	/**
	 * @Route("/groups/{groupSlug}/pages/{pageSlug}/edit", name="group_page_edit")
	 * @param                                                            $groupSlug
	 * @param                                                            $pageSlug
	 * @param \Symfony\Component\HttpFoundation\Request                  $request
	 * @param \Doctrine\Common\Persistence\ObjectManager                 $manager
	 *
	 * @param \Symfony\Component\Routing\Generator\UrlGeneratorInterface $router
	 *
	 * @return string
	 */
	public function groupPageEdit (
			$groupSlug,
			$pageSlug,
			Request $request,
			ObjectManager $manager,
			UrlGeneratorInterface $router
	) {
		/**
		 * @var \App\Entity\Usergroup $group
		 */
		$group = $manager->getRepository( Usergroup::class )
						 ->findOneBy( [ 'slug' => $groupSlug ] );

		if ( !$group ) {
			throw $this->createNotFoundException( 'The group does not exist' );
		}

		/**
		 * @var \App\Entity\Page $page
		 */
		$page = $manager->getRepository( Page::class )
						->findOneBy( [ 'usergroup' => $group, 'slug' => $pageSlug ] );

		if ( !$page ) {
			throw $this->createNotFoundException( 'The page does not exist' );
		}

		$this->denyAccessUnlessGranted( GroupPageVoter::EDIT, $page );

		$form = $this->createForm( PageType::class, $page );

		$form->handleRequest( $request );

		if ( $form->isSubmitted() && $form->isValid() ) {
			$page->setEditedAt( new \DateTime() );

			// TODO : Add Versioning
			// TODO : Add Log

			$manager->flush();

			$this->addFlash( 'notice', 'messages.page.page_updated' );

			return $this->redirectToRoute( 'group_page_index', [ 'groupSlug' => $group->getSlug(), 'pageSlug' => $page->getSlug() ] );
		}

		return $this->render( 'pages/page/page-edit.html.twig', [
				'group'  => $group,
				'page'   => $page,
				'form'   => $form->createView(),
				'upload' => $router->generate( 'file_upload', [ 'groupId' => $group->getId() ] ),
		] );
	}

	/**
	 * @Route("/groups/{groupSlug}/pages/{pageSlug}/delete", name="group_page_delete")
	 * @param                                            $groupSlug
	 * @param                                            $pageSlug
	 * @param \Doctrine\Common\Persistence\ObjectManager $manager
	 *
	 * @return string
	 */
	public function groupPageDelete (
			$groupSlug,
			$pageSlug,
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

		/**
		 * @var \App\Entity\Page $page
		 */
		$page = $manager->getRepository( Page::class )
						->findOneBy( [ 'usergroup' => $group, 'slug' => $pageSlug ] );

		if ( !$page ) {
			throw $this->createNotFoundException( 'The page does not exist' );
		}

		$this->denyAccessUnlessGranted( GroupPageVoter::DELETE, $page );

		$manager->remove( $page );
		$manager->flush();

		$this->addFlash( 'notice', 'messages.page.page_deleted' );

		return $this->redirectToRoute( 'group_index', [ 'groupSlug' => $group->getSlug() ] );
	}
}
