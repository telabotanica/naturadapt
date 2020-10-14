<?php

namespace App\Controller;

use App\Entity\File;
use App\Entity\LogEvent;
use App\Entity\Usergroup;
use App\Entity\UsergroupMembership;
use App\Form\UsergroupType;
use App\Security\GroupVoter;
use App\Service\Community;
use App\Service\FileManager;
use App\Service\SlugGenerator;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class GroupController extends AbstractController {
	/**************************************************
	 * GROUPS
	 **************************************************/

	/**
	 * @Route("/groups", name="groups_index")
	 * @param \Doctrine\ORM\EntityManagerInterface       $manager
	 * @param \App\Service\Community                     $community
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function groupsIndex (
        EntityManagerInterface $manager,
        Community $community
	) {
		$groups = $manager->getRepository( Usergroup::class )
						  ->getGroupsWithMembers( $community->getGroup() );

		return $this->render( 'pages/group/groups-index.html.twig', [
				'groups' => $groups,
		] );
	}

	/**************************************************
	 * GROUP
	 **************************************************/

	/**
	 * @Route("/groups/new", name="group_new")
	 * @param \Symfony\Component\HttpFoundation\Request  $request
	 * @param \Doctrine\ORM\EntityManagerInterface       $manager
	 * @param \App\Service\FileManager                   $fileManager
	 * @param \App\Service\SlugGenerator                 $slugGenerator
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function groupNew (
			Request $request,
            EntityManagerInterface $manager,
			FileManager $fileManager,
			SlugGenerator $slugGenerator
	) {
		$this->denyAccessUnlessGranted( GroupVoter::CREATE );

		/**
		 * @var \App\Entity\User $user
		 */
		$user = $this->getUser();

		/**
		 * @var \App\Entity\Usergroup $group
		 */
		$group = new Usergroup();

		$form = $this->createForm( UsergroupType::class, $group );

		$form->handleRequest( $request );

		if ( $form->isSubmitted() && $form->isValid() ) {
			$group->setSlug( $slugGenerator->generateSlug( $group->getName(), Usergroup::class, 'slug' ) );
			$group->setCreatedAt( new DateTime() );

			$manager->persist( $group );

			$membership = new UsergroupMembership();
			$membership->setUsergroup( $group );
			$membership->setUser( $user );
			$membership->setJoinedAt( new DateTime() );
			$membership->setRole( UsergroupMembership::ROLE_ADMIN );
			$membership->setStatus( UsergroupMembership::STATUS_MEMBER );

			$manager->persist( $membership );

			$manager->flush();

			// Logo
			$uploadFile = $form->get( 'logofile' )->getData();

			if ( !empty( $uploadFile ) ) {
				/**
				 * @var \App\Service\UsergroupFileManager $groupFileManager
				 */
				$groupFileManager = $fileManager->getManager( File::USERGROUP_FILES );
				$file             = $groupFileManager->createFromUploadedFile( $uploadFile, $user, $group );

				$manager->persist( $file );

				$group->setLogo( $file );
			}
			// --

			// Cover
			$uploadFile = $form->get( 'coverfile' )->getData();

			if ( !empty( $uploadFile ) ) {
				/**
				 * @var \App\Service\UsergroupFileManager $groupFileManager
				 */
				$groupFileManager = $fileManager->getManager( File::USERGROUP_FILES );
				$file             = $groupFileManager->createFromUploadedFile( $uploadFile, $user, $group );

				$manager->persist( $file );

				$group->setCover( $file );
			}
			// --

			$manager->flush();

			// Log Event

			$log = new LogEvent();
			$log->setType( LogEvent::GROUP_CREATE );
			$log->setUser( $this->getUser() );
			$log->setUsergroup( $group );
			$log->setCreatedAt( new DateTime() );
			$log->setData( [ 'name' => $group->getName() ] );
			$manager->persist( $log );
			$manager->flush();

			// --

			$this->addFlash( 'notice', 'messages.group.group_created' );

			return $this->redirectToRoute( 'group_index', [ 'groupSlug' => $group->getSlug() ] );
		}

		return $this->render( 'pages/group/group-create.html.twig', [
				'group' => $group,
				'form'  => $form->createView(),
		] );
	}

	/**
	 * @Route("/groups/{groupSlug}/edit", name="group_edit")
	 * @param                                            $groupSlug
	 * @param \Symfony\Component\HttpFoundation\Request  $request
	 * @param \Doctrine\ORM\EntityManagerInterface       $manager
	 * @param \App\Service\FileManager                   $fileManager
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @throws \Exception
	 */
	public function groupEdit (
			$groupSlug,
			Request $request,
            EntityManagerInterface $manager,
			FileManager $fileManager
	) {
		/**
		 * @var \App\Entity\User $user
		 */
		$user = $this->getUser();

		/**
		 * @var \App\Entity\Usergroup $group
		 */
		$group    = $manager->getRepository( Usergroup::class )
							->findOneBy( [ 'slug' => $groupSlug ] );
		$original = clone $group;

		if ( !$group ) {
			throw $this->createNotFoundException( 'The group does not exist' );
		}

		$this->denyAccessUnlessGranted( GroupVoter::EDIT, $group );

		$form = $this->createForm( UsergroupType::class, $group );

		$form->handleRequest( $request );

		if ( $form->isSubmitted() && $form->isValid() ) {
			$modifications = [];

			if ( $original->getName() !== $group->getName() ) {
				$modifications[] = 'name';
			}

			if ( $original->getDescription() !== $group->getDescription() ) {
				$modifications[] = 'description';
			}

			if ( $original->getPresentation() !== $group->getPresentation() ) {
				$modifications[] = 'presentation';
			}

			// Logo
			$uploadFile = $form->get( 'logofile' )->getData();

			if ( !empty( $uploadFile ) ) {
				/**
				 * @var \App\Service\UsergroupFileManager $groupFileManager
				 */
				$groupFileManager = $fileManager->getManager( File::USERGROUP_FILES );
				$file             = $groupFileManager->createFromUploadedFile( $uploadFile, $user, $group );

				$manager->persist( $file );

				if ( !empty( $group->getLogo() ) ) {
					$fileManager->deleteFile( $group->getLogo() );
				}
				$group->setLogo( $file );

				$modifications[] = 'logo';
			}
			// --

			// Cover
			$uploadFile = $form->get( 'coverfile' )->getData();

			if ( !empty( $uploadFile ) ) {
				/**
				 * @var \App\Service\UsergroupFileManager $groupFileManager
				 */
				$groupFileManager = $fileManager->getManager( File::USERGROUP_FILES );
				$file             = $groupFileManager->createFromUploadedFile( $uploadFile, $user, $group );

				$manager->persist( $file );

				if ( !empty( $group->getCover() ) ) {
					$fileManager->deleteFile( $group->getCover() );
				}
				$group->setCover( $file );

				$modifications[] = 'cover';
			}
			// --

			if ( $original->getVisibility() !== $group->getVisibility() ) {
				$modifications[] = 'visibility_' . $group->getVisibility();
			}

			$manager->flush();

			// Log Event

			$log = new LogEvent();
			$log->setType( LogEvent::GROUP_EDIT );
			$log->setUser( $this->getUser() );
			$log->setUsergroup( $group );
			$log->setCreatedAt( new DateTime() );
			$log->setData( [ 'name' => $group->getName(), 'modifications' => $modifications ] );
			$manager->persist( $log );
			$manager->flush();

			// --

			$this->addFlash( 'notice', 'messages.group.group_updated' );

			return $this->redirectToRoute( 'group_index', [ 'groupSlug' => $group->getSlug() ] );
		}

		return $this->render( 'pages/group/group-edit.html.twig', [
				'group' => $group,
				'form'  => $form->createView(),
		] );
	}

	/**
	 * @Route("/groups/{groupSlug}", name="group_index")
	 * @param                                            $groupSlug
	 * @param \Doctrine\ORM\EntityManagerInterface       $manager
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function groupIndex ( $groupSlug, EntityManagerInterface $manager ) {
		/**
		 * @var $group \App\Entity\Usergroup
		 */
		$group = $manager->getRepository( Usergroup::class )
						 ->findOneBy( [ 'slug' => $groupSlug ] );

		if ( !$group ) {
			throw $this->createNotFoundException( 'The group does not exist' );
		}

		// Viewing rights is tested in the template

		return $this->render( 'pages/group/group-index.html.twig', [ 'group' => $group ] );
	}

	/**
	 * @Route("/groups/{groupSlug}/delete", name="group_delete")
	 * @param                                            $groupSlug
	 * @param \Symfony\Component\HttpFoundation\Request  $request
	 * @param \Doctrine\ORM\EntityManagerInterface       $manager
	 * @param \App\Service\FileManager                   $fileManager
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @throws \Exception
	 */
	public function groupDelete (
			$groupSlug,
			Request $request,
            EntityManagerInterface $manager,
			FileManager $fileManager
	) {
		/**
		 * @var \App\Entity\Usergroup $group
		 */
		$group = $manager->getRepository( Usergroup::class )
						 ->findOneBy( [ 'slug' => $groupSlug ] );

		$this->denyAccessUnlessGranted( GroupVoter::DELETE, $group );

		// Delete confirmation form

		$form = $this->createFormBuilder()
					 ->add( 'submit', SubmitType::class )
					 ->getForm();

		$form->handleRequest( $request );

		if ( $form->isSubmitted() && $form->isValid() ) {
			$group->setLogo( NULL );
			$group->setCover( NULL );

			foreach ( $group->getLogEvents() as $event ) {
				$manager->remove( $event );
			}

			foreach ( $group->getMembers() as $membership ) {
				$manager->remove( $membership );
			}

			foreach ( $group->getPages() as $page ) {
				$manager->remove( $page );
			}

			foreach ( $group->getArticles() as $article ) {
				$manager->remove( $article );
			}

			foreach ( $group->getDiscussions() as $discussion ) {
				$manager->remove( $discussion );
			}

			foreach ( $group->getDocuments() as $document ) {
				$manager->remove( $document );
			}

			foreach ( $group->getFiles() as $file ) {
				$fileManager->deleteFile( $file );
				$manager->remove( $file );
			}

			$manager->flush();

			$manager->remove( $group );

			// Log Event

			$log = new LogEvent();
			$log->setType( LogEvent::GROUP_DELETE );
			$log->setUser( $this->getUser() );
			$log->setCreatedAt( new DateTime() );
			$log->setData( [ 'name' => $group->getName() ] );
			$manager->persist( $log );

			// --

			$manager->flush();

			$this->addFlash( 'notice', 'messages.group.group_deleted' );

			return $this->redirectToRoute( 'groups_index' );
		}

		return $this->render( 'pages/confirm.html.twig', [
				'form' => $form->createView(),
		] );
	}
}
