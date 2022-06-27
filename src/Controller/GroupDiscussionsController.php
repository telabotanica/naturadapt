<?php

namespace App\Controller;

use App\Entity\Discussion;
use App\Entity\DiscussionMessage;
use App\Entity\File;
use App\Entity\LogEvent;
use App\Entity\User;
use App\Entity\Usergroup;
use App\Entity\UsergroupMembership;
use App\Form\DiscussionMessageType;
use App\Form\DiscussionType;
use App\Security\GroupDiscussionVoter;
use App\Security\GroupVoter;
use App\Service\DiscussionSender;
use App\Service\FileManager;
use App\Service\HashGenerator;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use EmailReplyParser\Parser\EmailParser;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GroupDiscussionsController extends AbstractController {
	/**************************************************
	 * DISCUSSIONS
	 **************************************************/

	/**
	 * @Route("/groups/{groupSlug}/discussions", name="group_discussions_index")
	 *
	 * @param                                            $groupSlug
	 * @param \Doctrine\ORM\EntityManagerInterface       $manager
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function groupDiscussionsIndex (
			$groupSlug,
			EntityManagerInterface $manager
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

		return $this->render( 'pages/discussion/discussions-index.html.twig', [
				'group' => $group,
				'email' => $group->getSlug() . '@' . ( $this->getParameter( 'postmark' )[ 'list_domain' ] ),
		] );
	}

	/**************************************************
	 * DISCUSSION
	 **************************************************/

	/**
	 * @Route("/groups/{groupSlug}/discussions/new", name="group_discussion_new")
	 * @param                                                            $groupSlug
	 * @param \Symfony\Component\HttpFoundation\Request                  $request
	 * @param \Doctrine\ORM\EntityManagerInterface                       $manager
	 * @param \App\Service\FileManager                                   $fileManager
	 * @param \Symfony\Component\Routing\Generator\UrlGeneratorInterface $router
	 * @param \App\Service\DiscussionSender                              $sender
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function groupDiscussionNew (
			$groupSlug,
			Request $request,
			EntityManagerInterface $manager,
			FileManager $fileManager,
			UrlGeneratorInterface $router,
			DiscussionSender $sender
	) {
		/**
		 * @var \App\Entity\Usergroup $group
		 */
		$group = $manager->getRepository( Usergroup::class )
						 ->findOneBy( [ 'slug' => $groupSlug ] );

		if ( !$group ) {
			throw $this->createNotFoundException( 'The group does not exist' );
		}

		if ( !$this->isGranted( GroupDiscussionVoter::CREATE, $group ) ) {
			$this->addFlash( 'notice', 'messages.discussion.join_required' );

			return $this->redirectToRoute( 'group_discussions_index', [ 'groupSlug' => $groupSlug ] );
		}

		$this->denyAccessUnlessGranted( GroupDiscussionVoter::CREATE, $group );

		$discussion = new Discussion();
		$form       = $this->createForm( DiscussionType::class, $discussion );

		$form->handleRequest( $request );

		if ( $form->isSubmitted() && $form->isValid() ) {
			if ( empty( $form->get( 'body' )->getData() ) ) {
				$this->addFlash( 'warning', 'messages.discussion.message_error' );
			}
			else {
				$discussion->setUuid( Uuid::uuid4() );
				$discussion->setAuthor( $this->getUser() );
				$discussion->setUsergroup( $group );
				$discussion->setCreatedAt( new DateTime() );
				$manager->persist( $discussion );
				$manager->flush();

				$discussionMessage = new DiscussionMessage();
				$discussionMessage->setDiscussion( $discussion );
				$discussionMessage->getDiscussion()->setActiveAt( new DateTime() );
				$discussionMessage->setCreatedAt( new DateTime() );
				$discussionMessage->setAuthor( $this->getUser() );
				$discussionMessage->setBody( $form->get( 'body' )->getData() );
				$manager->persist( $discussionMessage );
				$manager->flush();

				// File
				$uploadFile = $form->get( 'attachment' )->getData();

				if ( !empty( $uploadFile ) ) {
					/**
					 * @var \App\Service\UsergroupFileManager $groupFileManager
					 */
					$groupFileManager = $fileManager->getManager( File::USERGROUP_FILES );
					$file             = $groupFileManager->createFromUploadedFile( $uploadFile, $this->getUser(), $group );
					$manager->persist( $file );
					$manager->flush();

					$discussionMessage->addFile( $file );
					$manager->flush();
				}

				// Send Notifications

				$sender->sendDiscussionMessage( $discussionMessage, TRUE );

				// Log Event

				$log = new LogEvent();
				$log->setType( LogEvent::DISCUSSION_CREATE );
				$log->setUser( $this->getUser() );
				$log->setUsergroup( $group );
				$log->setCreatedAt( new DateTime() );
				$log->setData( [ 'discussion' => $discussion->getId(), 'title' => $discussion->getTitle() ] );
				$manager->persist( $log );
				$manager->flush();

				// --

				$this->addFlash( 'notice', 'messages.discussion.discussion_created' );

				return $this->redirectToRoute( 'group_discussions_index', [ 'groupSlug' => $group->getSlug() ] );
			}
		}

		return $this->render( 'pages/discussion/discussion-create.html.twig', [
				'group'      => $group,
				'discussion' => $discussion,
				'form'       => $form->createView(),
				'upload'     => $router->generate( 'file_upload', [ 'groupId' => $group->getId() ] ),
		] );
	}

	/**
	 * @Route("/groups/{groupSlug}/discussions/{discussionUuid}", name="group_discussion_index")
	 * @param                                                            $groupSlug
	 * @param                                                            $discussionUuid
	 * @param \Symfony\Component\HttpFoundation\Request                  $request
	 * @param \Doctrine\ORM\EntityManagerInterface                       $manager
	 * @param \App\Service\FileManager                                   $fileManager
	 * @param \Symfony\Component\Routing\Generator\UrlGeneratorInterface $router
	 * @param \App\Service\DiscussionSender                              $sender
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @throws \Exception
	 */
	public function groupDiscussionIndex (
			$groupSlug,
			$discussionUuid,
			Request $request,
			EntityManagerInterface $manager,
			FileManager $fileManager,
			UrlGeneratorInterface $router,
			DiscussionSender $sender
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
		 * @var \App\Entity\Discussion $discussion
		 */
		$discussion = $manager->getRepository( Discussion::class )
							  ->findOneBy( [ 'usergroup' => $group, 'uuid' => $discussionUuid ] );

		if ( !$discussion ) {
			throw $this->createNotFoundException( 'The discussion does not exist' );
		}

		if ( !$this->isGranted( GroupDiscussionVoter::READ, $discussion ) ) {
			return $this->redirectToRoute( 'group_index', [ 'groupSlug' => $group->getSlug() ] );
		}

		if ( $this->isGranted( GroupDiscussionVoter::PARTICIPATE, $discussion ) ) {
			$discussionMessage = new DiscussionMessage();
			$form              = $this->createForm( DiscussionMessageType::class, $discussionMessage );

			$form->handleRequest( $request );

			if ( $form->isSubmitted() && $form->isValid() ) {
				if ( empty( $form->get( 'body' )->getData() ) ) {
					$this->addFlash( 'warning', 'messages.discussion.message_error' );
				}
				else {
					$discussionMessage->setDiscussion( $discussion );
					$discussionMessage->getDiscussion()->setActiveAt( new DateTime() );
					$discussionMessage->setCreatedAt( new DateTime() );
					$discussionMessage->setAuthor( $this->getUser() );
					$discussionMessage->setBody( $form->get( 'body' )->getData() );
					$manager->persist( $discussionMessage );
					$manager->flush();

					// File
					$uploadFile = $form->get( 'attachment' )->getData();

					if ( !empty( $uploadFile ) ) {
						/**
						 * @var \App\Service\UsergroupFileManager $groupFileManager
						 */
						$groupFileManager = $fileManager->getManager( File::USERGROUP_FILES );
						$file             = $groupFileManager->createFromUploadedFile( $uploadFile, $this->getUser(), $group );
						$manager->persist( $file );
						$manager->flush();

						$discussionMessage->addFile( $file );
						$manager->flush();
					}

					// Send Notifications

					$sender->sendDiscussionMessage( $discussionMessage );

					// Log Event

					$log = new LogEvent();
					$log->setType( LogEvent::DISCUSSION_PARTICIPATE );
					$log->setUser( $this->getUser() );
					$log->setUsergroup( $group );
					$log->setCreatedAt( new DateTime() );
					$log->setData( [ 'discussion' => $discussion->getId(), 'message' => $discussionMessage->getId(), 'title' => $discussion->getTitle() ] );
					$manager->persist( $log );
					$manager->flush();

					// --

					$this->addFlash( 'notice', 'messages.discussion.message_created' );

					$discussionMessage = new DiscussionMessage();
					$form              = $this->createForm( DiscussionMessageType::class, $discussionMessage );
				}
			}
		}
		else {
			$form = FALSE;
		}

		return $this->render( 'pages/discussion/discussion-index.html.twig', [
				'group'      => $group,
				'discussion' => $discussion,
				'form'       => $form ? $form->createView() : FALSE,
				'upload'     => $router->generate( 'file_upload', [ 'groupId' => $group->getId() ] ),
		] );
	}

	/**
	 * @Route("/groups/{groupSlug}/discussions/{discussionUuid}/delete", name="group_discussion_delete")
	 * @param                                            $groupSlug
	 * @param                                            $discussionUuid
	 * @param \Symfony\Component\HttpFoundation\Request  $request
	 * @param \Doctrine\ORM\EntityManagerInterface       $manager
	 * @param \App\Service\FileManager                   $fileManager
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function groupDiscussionDelete (
			$groupSlug,
			$discussionUuid,
			Request $request,
			EntityManagerInterface $manager,
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
		 * @var \App\Entity\Discussion $discussion
		 */
		$discussion = $manager->getRepository( Discussion::class )
							  ->findOneBy( [ 'usergroup' => $group, 'uuid' => $discussionUuid ] );

		if ( !$discussion ) {
			throw $this->createNotFoundException( 'The discussion does not exist' );
		}

		$this->denyAccessUnlessGranted( GroupDiscussionVoter::DELETE, $discussion );

		// Delete confirmation form

		$form = $this->createFormBuilder()
					 ->add( 'submit', SubmitType::class )
					 ->getForm();

		$form->handleRequest( $request );

		if ( $form->isSubmitted() && $form->isValid() ) {
			foreach ( $discussion->getMessages() as $message ) {
				foreach ( $message->getFiles() as $file ) {
					$fileManager->deleteFile( $file );
					$manager->remove( $file );
				}

				$manager->remove( $message );
			}

			// Log Event

			$log = new LogEvent();
			$log->setType( LogEvent::DISCUSSION_DELETE );
			$log->setUser( $this->getUser() );
			$log->setUsergroup( $group );
			$log->setCreatedAt( new DateTime() );
			$log->setData( [ 'discussion' => $discussion->getId(), 'title' => $discussion->getTitle() ] );
			$manager->persist( $log );

			// --

			$manager->remove( $discussion );

			$manager->flush();

			$this->addFlash( 'notice', 'messages.discussion.discussion_deleted' );

			return $this->redirectToRoute( 'group_index', [ 'groupSlug' => $group->getSlug() ] );
		}

		return $this->render( 'pages/confirm.html.twig', [
				'form' => $form->createView(),
		] );
	}

	/**
	 * @Route("/groups/{groupSlug}/message/{messageId}/delete", name="group_message_delete")
	 * @param                                            $groupSlug
	 * @param                                            $messageId
	 * @param \Symfony\Component\HttpFoundation\Request  $request
	 * @param \Doctrine\ORM\EntityManagerInterface       $manager
	 * @param \App\Service\FileManager                   $fileManager
	 *
	 * @return string
	 */
	public function groupMessageDelete (
			$groupSlug,
			$messageId,
			Request $request,
			EntityManagerInterface $manager,
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
		 * @var \App\Entity\DiscussionMessage $message
		 */
		$message = $manager->getRepository( DiscussionMessage::class )
						   ->findOneBy( [ 'id' => $messageId ] );

		if ( !$message ) {
			throw $this->createNotFoundException( 'The message does not exist' );
		}

		// Delete confirmation form

		$form = $this->createFormBuilder()
					 ->add( 'submit', SubmitType::class )
					 ->getForm();

		$form->handleRequest( $request );

		if ( $form->isSubmitted() && $form->isValid() ) {
			$discussion = $message->getDiscussion();

			foreach ( $message->getFiles() as $file ) {
				$fileManager->deleteFile( $file );
				$manager->remove( $file );
			}

			$manager->remove( $message );

			$manager->flush();

			$this->addFlash( 'notice', 'messages.discussion.message_deleted' );

			return $this->redirectToRoute( 'group_discussion_index', [ 'groupSlug' => $group->getSlug(), 'discussionUuid' => $discussion->getUuid() ] );
		}

		return $this->render( 'pages/confirm.html.twig', [
				'form' => $form->createView(),
		] );
	}

	/**
	 * @Route("/groups/{groupSlug}/message/{messageId}/hide", name="group_message_hide")
	 * @param                                            $groupSlug
	 * @param                                            $messageId
	 * @param \Doctrine\ORM\EntityManagerInterface       $manager
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function groupMessageHide (
			$groupSlug,
			$messageId,
			EntityManagerInterface $manager
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
		 * @var \App\Entity\DiscussionMessage $message
		 */
		$message = $manager->getRepository( DiscussionMessage::class )
						   ->findOneBy( [ 'id' => $messageId ] );

		if ( !$message ) {
			throw $this->createNotFoundException( 'The message does not exist' );
		}

		/**
		 * @var \App\Entity\Discussion $discussion
		 */
		$discussion = $message->getDiscussion();

		$this->denyAccessUnlessGranted( GroupDiscussionVoter::EDIT, $discussion );

		$message->setMasked( TRUE );
		$manager->flush();

		$this->addFlash( 'notice', 'messages.discussion.message_hidden' );

		return $this->redirectToRoute( 'group_discussion_index', [ 'groupSlug' => $group->getSlug(), 'discussionUuid' => $discussion->getUuid() ] );
	}

	/**
	 * @Route("/groups/{groupSlug}/message/{messageId}/show", name="group_message_show")
	 * @param                                            $groupSlug
	 * @param                                            $messageId
	 * @param \Doctrine\ORM\EntityManagerInterface       $manager
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function groupMessageShow (
			$groupSlug,
			$messageId,
			EntityManagerInterface $manager
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
		 * @var \App\Entity\DiscussionMessage $message
		 */
		$message = $manager->getRepository( DiscussionMessage::class )
						   ->findOneBy( [ 'id' => $messageId ] );

		if ( !$message ) {
			throw $this->createNotFoundException( 'The message does not exist' );
		}

		/**
		 * @var \App\Entity\Discussion $discussion
		 */
		$discussion = $message->getDiscussion();

		$this->denyAccessUnlessGranted( GroupDiscussionVoter::EDIT, $discussion );

		$message->setMasked( NULL );
		$manager->flush();

		$this->addFlash( 'notice', 'messages.discussion.message_shown' );

		return $this->redirectToRoute( 'group_discussion_index', [ 'groupSlug' => $group->getSlug(), 'discussionUuid' => $discussion->getUuid() ] );
	}

	/**
	 * @Route("/ws/list/inbound/{key}", name="webservice_list_inbound")
	 * @param                                            $key
	 * @param \Symfony\Component\HttpFoundation\Request  $request
	 * @param \Doctrine\ORM\EntityManagerInterface       $manager
	 * @param \App\Service\FileManager                   $fileManager
	 * @param \App\Service\DiscussionSender              $sender
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function webserviceInbound (
			$key,
			Request $request,
			EntityManagerInterface $manager,
			FileManager $fileManager,
			DiscussionSender $sender
	) {
		if ( $key !== $this->getParameter( 'postmark' )[ 'inbound_key' ] ) {
			return new JsonResponse( [ 'status' => 'Invalid API key' ] );
		}

		$data = json_decode( $request->getContent(), TRUE );

		// Ignore automatically generated messages
		if ( !empty( $data[ 'Headers' ] ) ) {
			foreach ( $data[ 'Headers' ] as $header ) {
				if ( 'Auto-Submitted' === $header[ 'Name' ] && 'no' !== $header[ 'Value' ] ) {
					return new JsonResponse( [ 'status' => 'This is an auto-replied message, ignoring' ] );
				}
			}
		}

		$slug      = explode( '+', explode( '@', $data[ 'OriginalRecipient' ] )[ 0 ] )[ 0 ];
		$hash      = $data[ 'MailboxHash' ];
		$userEmail = $data[ 'From' ];
		$subject   = $data[ 'Subject' ];

		$emailBody = !empty( $data[ 'StrippedTextBody' ] )
				? $data[ 'StrippedTextBody' ]
				: (
					!empty( $data[ 'TextBody' ] )
						? $data[ 'TextBody' ]
						: strip_tags( $data[ 'HtmlBody' ] )
				)
		;

		// Replace nobreaking space with simple space
		$emailBody = str_replace( "\u{00a0}", ' ', $emailBody );
		$email     = ( new EmailParser() )->parse( $emailBody );
		$fragments = $email->getFragments();
		$body      = trim( current( $fragments )->getContent() );

		/**
		 * @var \App\Entity\User $user
		 */
		$user = $manager->getRepository( User::class )
						->findOneBy( [ 'email' => $userEmail ] );

		if ( !$user ) {
			return new JsonResponse( [ 'status' => 'The user does not exist' ] );
		}

		if ( $user->getStatus() !== User::STATUS_ACTIVE ) {
			return new JsonResponse( [ 'status' => 'The user is disabled' ] );
		}

		/**
		 * @var \App\Entity\Usergroup $group
		 */
		$group = $manager->getRepository( Usergroup::class )
						 ->findOneBy( [ 'slug' => $slug ] );

		if ( !$group ) {
			return new JsonResponse( [ 'status' => 'The group does not exist' ] );
		}

		if ( !$manager->getRepository( UsergroupMembership::class )->isMember( $user, $group ) ) {
			return new JsonResponse( [ 'status' => 'This user can not participate' ] );
		}

		$discussion = FALSE;

		if ( !empty( $hash ) ) {
			/**
			 * @var \App\Entity\Discussion $discussion
			 */
			$discussion = $manager->getRepository( Discussion::class )
								  ->findOneBy( [ 'uuid' => $hash ] );

			if ( !$discussion ) {
				return new JsonResponse( [ 'status' => 'The discussion does not exist' ] );
			}

			$group = $discussion->getUsergroup();
		}

		$createDiscussion = !$discussion;

		if ( $createDiscussion ) {
			$discussion = new Discussion();
			$discussion->setUuid( Uuid::uuid4() );
			$discussion->setTitle( $subject );
			$discussion->setAuthor( $user );
			$discussion->setUsergroup( $group );
			$discussion->setCreatedAt( new DateTime() );
			$manager->persist( $discussion );
			$manager->flush();
		}

		$discussionMessage = new DiscussionMessage();
		$discussionMessage->setDiscussion( $discussion );
		$discussionMessage->getDiscussion()->setActiveAt( new DateTime() );
		$discussionMessage->setCreatedAt( new DateTime() );
		$discussionMessage->setAuthor( $user );
		$discussionMessage->setBody( nl2br( $body ) );
		$manager->persist( $discussionMessage );
		$manager->flush();

		if ( !empty( $data[ 'Attachments' ] ) ) {
			foreach ( $data[ 'Attachments' ] as $attachment ) {
				/**
				 * @var \App\Service\UsergroupFileManager $groupFileManager
				 */
				$groupFileManager = $fileManager->getManager( File::USERGROUP_FILES );

				$filename = $groupFileManager->writeFile( $attachment[ 'Name' ], $group, base64_decode( chunk_split( $attachment[ 'Content' ] ) ) );

				$file = new File();
				$file->setFilesystem( File::USERGROUP_FILES );
				$file->setUser( $user );
				$file->setUsergroup( $group );
				$file->setName( $attachment[ 'Name' ] );
				$file->setPath( $filename );
				$file->setType( $attachment[ 'ContentType' ] );
				$file->setSize( $attachment[ 'ContentLength' ] );

				$manager->persist( $file );

				$discussionMessage->addFile( $file );
			}

			$manager->flush();
		}

		// Send Notifications

		$sender->sendDiscussionMessage( $discussionMessage, $createDiscussion );

		// Log Event

		$log = new LogEvent();
		$log->setType( $createDiscussion ? LogEvent::DISCUSSION_CREATE : LogEvent::DISCUSSION_PARTICIPATE );
		$log->setUser( $user );
		$log->setUsergroup( $group );
		$log->setCreatedAt( new DateTime() );
		$log->setData( [ 'discussion' => $discussion->getId(), 'message' => $discussionMessage->getId(), 'title' => $discussion->getTitle() ] );
		$manager->persist( $log );
		$manager->flush();

		return new JsonResponse( [ 'status' => $createDiscussion ? 'discussion created' : 'message created' ] );
	}

	/**************************************************
	 * SUBSCRIPTION
	 **************************************************/

	/**
	 * @Route("/groups/{groupSlug}/notifications/{status}/{redirect}/{hash}", name="group_discussions_notifications")
	 * @param                                            $groupSlug
	 * @param                                            $status
	 * @param string                                     $redirect
	 * @param string                                     $hash
	 * @param \Doctrine\ORM\EntityManagerInterface       $manager
	 * @param \App\Service\HashGenerator                 $hashGenerator
	 *
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
	 */
	public function groupNotifications (
			$groupSlug,
			$status,
			EntityManagerInterface $manager,
			HashGenerator $hashGenerator,
			$redirect = 'group',
			$hash = ''
	) {
		/**
		 * @var \App\Entity\Usergroup $group
		 */
		$group = $manager->getRepository( Usergroup::class )
						 ->findOneBy( [ 'slug' => $groupSlug ] );

		if ( !$group ) {
			throw $this->createNotFoundException( 'The group does not exist' );
		}

		if ( empty( $hash ) ) {
			$user = $this->getUser();
		}
		else {
			$user = $hashGenerator->getUserFromHash( $hash );
		}

		if ( !$user ) {
			throw $this->createNotFoundException( 'The user does not exist' );
		}

		$membership = $manager->getRepository( UsergroupMembership::class )
							  ->getMembership( $user, $group );

		if ( $membership ) {
			$settings = $membership->getNotificationsSettings();
			switch ( $status ) {
				case 'unsubscribe':
					$settings[ 'unsubscribed' ] = TRUE;
					$this->addFlash( 'notice', 'messages.discussion.notifications.unsubscribe' );
					break;

				default:
					unset( $settings[ 'unsubscribed' ] );
					$this->addFlash( 'notice', 'messages.discussion.notifications.subscribe' );
			}
			$membership->setNotificationsSettings( $settings );
			$manager->flush();
		}

		switch ( $redirect ) {
			case 'groups':
				return $this->redirectToRoute( 'user_groups' );
				break;

			default:
				return $this->redirectToRoute( 'group_index', [ 'groupSlug' => $groupSlug ] );
		}
	}
}
