<?php

namespace App\Controller;

use App\Entity\File;
use App\Entity\LogEvent;
use App\Entity\Usergroup;
use App\Entity\UsergroupMembership;
use App\Form\UsergroupType;
use App\Security\GroupVoter;
use App\Security\UserVoter;
use App\Service\Community;
use App\Service\EmailSender;
use App\Service\FileManager;
use App\Service\SlugGenerator;
use App\Service\UserGroupRelation;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
		$groupsManager = $manager->getRepository( Usergroup::class );

		$groups = $groupsManager->getGroupsWithMembers( $community->getGroup(), true );
		$groupsToActivate = $groupsManager->getGroupsWithMembers( false, false );

		return $this->render( 'pages/group/groups-index.html.twig', [
				'groups' => $groups,
				'groupsToActivate' => $groupsToActivate,
		] );
	}

	/**************************************************
	 * GROUP
	 **************************************************/

	/**
	 * @Route("/groups/new", name="group_new")
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param \Doctrine\ORM\EntityManagerInterface      $manager
	 * @param \App\Service\FileManager                  $fileManager
	 * @param \App\Service\SlugGenerator                $slugGenerator
	 * @param \App\Service\Community                    $community
	 * @param \App\Service\UserGroupRelation            $userGroupRelation
	 * @param \App\Service\EmailSender                  $mailer
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function groupNew (
			Request $request,
			EntityManagerInterface $manager,
			FileManager $fileManager,
			SlugGenerator $slugGenerator,
			Community $community,
			UserGroupRelation $userGroupRelation,
			EmailSender $mailer
	) {
		$this->denyAccessUnlessGranted( GroupVoter::CREATE );

		/**
		 * @var \App\Entity\User $user
		 */
		$user = $this->getUser();

		$doActivate = $userGroupRelation->isCommunityAdmin( $user );

		/**
		 * @var \App\Entity\Usergroup $group
		 */
		$group = new Usergroup();

		$form = $this->createForm( UsergroupType::class, $group );

		$form->handleRequest( $request );

		if ( $form->isSubmitted() && $form->isValid() ) {
			$group->setSlug( $slugGenerator->generateSlug( $group->getName(), Usergroup::class, 'slug' ) );
			$group->setCreatedAt( new DateTime() );
			$group->setIsActive( $doActivate );

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

			if ( $doActivate ) {
				$this->redirectToRoute( 'group_activate', [ 'groupSlug' => $group->getSlug(), 'doActivate' => TRUE ] );
			} else {

				$communityAdmins = $community->getGroup()->getMembersByRole( UsergroupMembership::ROLE_ADMIN );
				$multiple = count( $communityAdmins ) > 1;

				foreach ( $communityAdmins as $communityAdminMembership ) {
					$communityAdmin = $communityAdminMembership->getUser();

					$message = $this->renderView(
						'emails/usergroup-activation.html.twig',
						[
							'admin'     => $communityAdmin,
							'user'      => $user,
							'usergroup' => $group,
							'url'       => $this->generateUrl( 'group_index', [ 'groupSlug' => $group->getSlug() ], UrlGeneratorInterface::ABSOLUTE_URL ),
							'multiple'  => $multiple,
						]
					);

					$mailer->send(
						[ $this->getParameter( 'plateform' )[ 'from' ] => $this->getParameter( 'plateform' )[ 'name' ] ],
						$communityAdmin->getEmail(),
						$mailer->getSubjectFromTitle( $message ),
						$message
					);
				}
			}

			return $this->redirectToRoute( 'groups_index' );
		}

		return $this->render( 'pages/group/group-create.html.twig', [
				'group' => $group,
				'form'  => $form->createView(),
		] );
	}

	/**
	 * @Route("/groups/activate/{groupSlug}/action/{doActivate}", name="group_activate")
	 *
	 * @param                                      $groupSlug
	 * @param                                      $doActivate
	 * @param \Doctrine\ORM\EntityManagerInterface $manager
	 * @param \App\Service\UserGroupRelation       $userGroupRelation
	 * @param \App\Service\EmailSender             $mailer

	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 * @throws \Exception
	 */
	public function groupActivate (
		$groupSlug,
		$doActivate,
		EntityManagerInterface $manager,
		UserGroupRelation $userGroupRelation,
		EmailSender $mailer
	) {
		if (!$this->isGranted(UserVoter::LOGGED)) {
			return $this->redirectToRoute('user_login');
		}

		if ( !$userGroupRelation->isCommunityAdmin( $this->getUser() ) ) {
			throw new AccessDeniedHttpException( 'Your are not allowed to activate groups' );
		}

		$group = $manager->getRepository( Usergroup::class )
			->findOneBy( [ 'slug' => $groupSlug ] );

		if ( !$group ) {
			throw $this->createNotFoundException( 'The group does not exist' );
		}

		if ( $group->getIsActive() ) {
			$this->addFlash('error', 'messages.group.already_active');

			return $this->redirectToRoute( 'group_index', [ 'groupSlug' => $groupSlug ] );
		}

		if ( !$doActivate ) {

			return $this->redirectToRoute( 'group_delete', [ 'groupSlug' => $groupSlug ] );
		} else {
			$group->setIsActive( true );
			$manager->flush();
		}

		$admins = $group->getMembersByRole( UsergroupMembership::ROLE_ADMIN );
		foreach ( $admins as $adminMembership ) {
			$admin = $adminMembership->getUser();

			$message = $this->renderView(
				'emails/usergroup-activation_answer.html.twig',
				[
					'admin'       => $admin,
					'usergroup'   => $group,
					'isActivated' => $doActivate,
					'url'         => $this->generateUrl( 'group_index', [ 'groupSlug' => $groupSlug ], UrlGeneratorInterface::ABSOLUTE_URL ),
				]
			);

			$mailer->send(
				[ $this->getParameter( 'plateform' )[ 'from' ] => $this->getParameter( 'plateform' )[ 'name' ] ],
				$admin->getEmail(),
				$mailer->getSubjectFromTitle( $message ),
				$message
			);
		}

		// Log Event

		$log = new LogEvent();
		$log->setType( LogEvent::GROUP_ACTIVATE );
		$log->setUser( $this->getUser() );
		$log->setUsergroup( $group );
		$log->setCreatedAt( new DateTime() );
		$log->setData( [ 'name' => $group->getName() ] );
		$manager->persist( $log );
		$manager->flush();

		$this->addFlash( 'notice', 'messages.group.group_activated' );

		return $this->redirectToRoute( 'groups_index' );
	}

	/**
	 * @Route("/groups/{groupSlug}/edit", name="group_edit")
	 *
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
	 *
	 * @param                                            $groupSlug
	 * @param \Doctrine\ORM\EntityManagerInterface       $manager
	 * @param \App\Service\UserGroupRelation             $userGroupRelation
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function groupIndex (
			$groupSlug,
			EntityManagerInterface $manager,
			UserGroupRelation $userGroupRelation
	) {
		/**
		 * @var $group \App\Entity\Usergroup
		 */
		$group = $manager->getRepository( Usergroup::class )
						 ->findOneBy( [ 'slug' => $groupSlug ] );

		/**
		 * @var $user \App\Entity\User
		 */
		$user = $this->getUser();

		if ( !$group ) {
			throw $this->createNotFoundException( 'The group does not exist' );
		}

		if ( !$group->getIsActive() ) {
			$this->denyAccessUnlessGranted(GroupVoter::ADMIN, $group );
		}

		// Viewing rights is tested in the template

		return $this->render( 'pages/group/group-index.html.twig', [ 'group' => $group ] );
	}

	/**
	 * @Route("/groups/{groupSlug}/delete", name="group_delete")
	 *
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
