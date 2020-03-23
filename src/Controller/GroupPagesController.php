<?php

namespace App\Controller;

use App\Entity\File;
use App\Entity\LogEvent;
use App\Entity\Page;
use App\Entity\PageRevision;
use App\Entity\Usergroup;
use App\Form\PageType;
use App\Security\GroupPageVoter;
use App\Security\GroupVoter;
use App\Service\FileManager;
use App\Service\SlugGenerator;
use DateTime;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GroupPagesController extends AbstractController {
	/**************************************************
	 * PAGES
	 **************************************************/

	/**
	 * @Route("/groups/{groupSlug}/pages", name="group_pages_index")
	 * @param                                            $groupSlug
	 * @param \Doctrine\Common\Persistence\ObjectManager $manager
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function groupPagesIndex (
			$groupSlug,
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

		$this->denyAccessUnlessGranted( GroupVoter::READ, $group );

		return $this->render( 'pages/page/pages-index.html.twig', [
				'group' => $group,
		] );
	}

	/**************************************************
	 * PAGE
	 **************************************************/

	/**
	 * @Route("/groups/{groupSlug}/pages/new", name="group_page_new")
	 * @param                                                            $groupSlug
	 * @param \Symfony\Component\HttpFoundation\Request                  $request
	 * @param \Doctrine\Common\Persistence\ObjectManager                 $manager
	 * @param \App\Service\FileManager                                   $fileManager
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
			FileManager $fileManager,
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

		/**
		 * @var \App\Entity\User $user
		 */
		$user = $this->getUser();

		$page = new Page();
		$form = $this->createForm( PageType::class, $page );

		$form->handleRequest( $request );

		if ( $form->isSubmitted() && $form->isValid() ) {
			$page->setAuthor( $this->getUser() );
			$page->setUsergroup( $group );
			$page->setCreatedAt( new DateTime() );
			$page->setSlug( $slugGenerator->generateSlug( $page->getTitle(), Page::class, 'slug', [ 'usergroup' => $group ] ) );

			$manager->persist( $page );

			// Cover
			$uploadFile = $form->get( 'coverfile' )->getData();

			if ( !empty( $uploadFile ) ) {
				/**
				 * @var \App\Service\UsergroupFileManager $groupFileManager
				 */
				$groupFileManager = $fileManager->getManager( File::USERGROUP_FILES );
				$file             = $groupFileManager->createFromUploadedFile( $uploadFile, $user, $group );

				$manager->persist( $file );

				$page->setCover( $file );
			}
			// --

			// Create revision

			$revision = new PageRevision();
			$revision->setUser( $user );
			$revision->setPage( $page );
			$revision->setCreatedAt( new DateTime() );
			$revision->setData( [ 'title' => $page->getTitle(), 'body' => $page->getBody() ] );
			$manager->persist( $revision );

			$manager->flush();

			// Log Event

			$log = new LogEvent();
			$log->setType( LogEvent::PAGE_CREATE );
			$log->setUser( $this->getUser() );
			$log->setUsergroup( $group );
			$log->setCreatedAt( new DateTime() );
			$log->setData( [ 'page' => $page->getId(), 'title' => $page->getTitle() ] );
			$manager->persist( $log );
			$manager->flush();

			// --

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
	 * @Route("/groups/{groupSlug}/pages/{pageSlug}/edit", name="group_page_edit")
	 * @param                                                            $groupSlug
	 * @param                                                            $pageSlug
	 * @param \Symfony\Component\HttpFoundation\Request                  $request
	 * @param \Doctrine\Common\Persistence\ObjectManager                 $manager
	 *
	 * @param \App\Service\FileManager                                   $fileManager
	 * @param \Symfony\Component\Routing\Generator\UrlGeneratorInterface $router
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function groupPageEdit (
			$groupSlug,
			$pageSlug,
			Request $request,
			ObjectManager $manager,
			FileManager $fileManager,
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

		/**
		 * @var \App\Entity\User $user
		 */
		$user = $this->getUser();

		$form = $this->createForm( PageType::class, $page );

		$form->handleRequest( $request );

		if ( $form->isSubmitted() && $form->isValid() ) {
			$page->setEditedAt( new DateTime() );

			// Cover
			$uploadFile = $form->get( 'coverfile' )->getData();

			if ( !empty( $uploadFile ) ) {
				/**
				 * @var \App\Service\UsergroupFileManager $groupFileManager
				 */
				$groupFileManager = $fileManager->getManager( File::USERGROUP_FILES );
				$file             = $groupFileManager->createFromUploadedFile( $uploadFile, $user, $group );

				$manager->persist( $file );

				if ( !empty( $page->getCover() ) ) {
					$fileManager->deleteFile( $page->getCover() );
				}
				$page->setCover( $file );
			}
			// --

			// Create revision

			$revision = new PageRevision();
			$revision->setUser( $user );
			$revision->setPage( $page );
			$revision->setCreatedAt( new DateTime() );
			$revision->setData( [ 'title' => $page->getTitle(), 'body' => $page->getBody() ] );
			$manager->persist( $revision );

			$manager->flush();

			// Log Event

			$log = new LogEvent();
			$log->setType( LogEvent::PAGE_EDIT );
			$log->setUser( $this->getUser() );
			$log->setUsergroup( $group );
			$log->setCreatedAt( new DateTime() );
			$log->setData( [ 'page' => $page->getId(), 'title' => $page->getTitle() ] );
			$manager->persist( $log );
			$manager->flush();

			// --

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
	 * @Route("/groups/{groupSlug}/pages/{pageSlug}", name="group_page_index")
	 * @param                                            $groupSlug
	 * @param                                            $pageSlug
	 * @param \Doctrine\Common\Persistence\ObjectManager $manager
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function groupPageIndex (
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

		if ( !$this->isGranted( GroupPageVoter::READ, $page ) ) {
			return $this->redirectToRoute( 'group_index', [ 'groupSlug' => $group->getSlug() ] );
		}

		return $this->render( 'pages/page/page-index.html.twig', [
				'group' => $group,
				'page'  => $page,
		] );
	}

	/**
	 * @Route("/groups/{groupSlug}/pages/{pageSlug}/delete", name="group_page_delete")
	 * @param                                            $groupSlug
	 * @param                                            $pageSlug
	 * @param \Symfony\Component\HttpFoundation\Request  $request
	 * @param \Doctrine\Common\Persistence\ObjectManager $manager
	 * @param \App\Service\FileManager                   $fileManager
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function groupPageDelete (
			$groupSlug,
			$pageSlug,
			Request $request,
			ObjectManager $manager,
			FileManager $fileManager
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

		// Delete confirmation form

		$form = $this->createFormBuilder()
					 ->add( 'submit', SubmitType::class )
					 ->getForm();

		$form->handleRequest( $request );

		if ( $form->isSubmitted() && $form->isValid() ) {
			if ( !empty( $page->getCover() ) ) {
				$fileManager->deleteFile( $page->getCover() );
			}

			// Log Event

			$log = new LogEvent();
			$log->setType( LogEvent::PAGE_DELETE );
			$log->setUser( $this->getUser() );
			$log->setUsergroup( $group );
			$log->setCreatedAt( new DateTime() );
			$log->setData( [ 'page' => $page->getId(), 'title' => $page->getTitle() ] );
			$manager->persist( $log );

			// --

			$manager->remove( $page );

			$manager->flush();

			$this->addFlash( 'notice', 'messages.page.page_deleted' );

			return $this->redirectToRoute( 'group_index', [ 'groupSlug' => $group->getSlug() ] );
		}

		return $this->render( 'pages/confirm.html.twig', [
				'form' => $form->createView(),
		] );
	}
}
