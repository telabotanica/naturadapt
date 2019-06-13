<?php

namespace App\Controller;

use App\Entity\File;
use App\Entity\Usergroup;
use App\Entity\UsergroupMembership;
use App\Form\UsergroupType;
use App\Security\GroupVoter;
use App\Service\FileManager;
use App\Service\SlugGenerator;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
	 * @param \Symfony\Component\HttpFoundation\Request  $request
	 * @param \Doctrine\Common\Persistence\ObjectManager $manager
	 * @param \App\Service\FileManager                   $fileManager
	 * @param \App\Service\SlugGenerator                 $slugGenerator
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function groupNew (
			Request $request,
			ObjectManager $manager,
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
			$group->setCreatedAt( new \DateTime() );

			$manager->persist( $group );

			$membership = new UsergroupMembership();
			$membership->setUsergroup( $group );
			$membership->setUser( $user );
			$membership->setJoinedAt( new \DateTime() );
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

			$this->addFlash( 'notice', 'messages.group.group_created' );

			return $this->redirectToRoute( 'group_index', [ 'groupSlug' => $group->getSlug() ] );
		}

		return $this->render( 'pages/group/group-create.html.twig', [
				'group' => $group,
				'form'  => $form->createView(),
		] );
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

		return $this->render( 'pages/group/group-index.html.twig', [ 'group' => $group ] );
	}

	/**
	 * @Route("/groups/{groupSlug}/edit", name="group_edit")
	 * @param                                            $groupSlug
	 * @param \Symfony\Component\HttpFoundation\Request  $request
	 * @param \Doctrine\Common\Persistence\ObjectManager $manager
	 * @param \App\Service\FileManager                   $fileManager
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function groupEdit (
			$groupSlug,
			Request $request,
			ObjectManager $manager,
			FileManager $fileManager
	) {
		/**
		 * @var \App\Entity\User $user
		 */
		$user = $this->getUser();

		/**
		 * @var \App\Entity\Usergroup $group
		 */
		$group = $manager->getRepository( Usergroup::class )
						 ->findOneBy( [ 'slug' => $groupSlug ] );

		$this->denyAccessUnlessGranted( GroupVoter::EDIT, $group );

		$form = $this->createForm( UsergroupType::class, $group );

		$form->handleRequest( $request );

		if ( $form->isSubmitted() && $form->isValid() ) {
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
			}
			// --

			$manager->flush();

			$this->addFlash( 'notice', 'messages.group.group_updated' );

			return $this->redirectToRoute( 'group_index', [ 'groupSlug' => $group->getSlug() ] );
		}

		return $this->render( 'pages/group/group-edit.html.twig', [
				'group' => $group,
				'form'  => $form->createView(),
		] );
	}

	/**
	 * @Route("/groups/{groupSlug}/delete", name="group_delete")
	 * @param                                            $groupSlug
	 * @param \Doctrine\Common\Persistence\ObjectManager $manager
	 * @param \App\Service\FileManager                   $fileManager
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function groupDelete (
			$groupSlug,
			ObjectManager $manager,
			FileManager $fileManager
	) {
		/**
		 * @var \App\Entity\Usergroup $group
		 */
		$group = $manager->getRepository( Usergroup::class )
						 ->findOneBy( [ 'slug' => $groupSlug ] );

		$this->denyAccessUnlessGranted( GroupVoter::DELETE, $group );

		$group->setLogo( NULL );
		$group->setCover( NULL );

		foreach ( $group->getMembers() as $membership ) {
			$manager->remove( $membership );
		}

		foreach ( $group->getPages() as $page ) {
			$manager->remove( $page );
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

		$manager->flush();

		$this->addFlash( 'notice', 'messages.group.group_deleted' );

		return $this->redirectToRoute( 'groups_index' );
	}
}
