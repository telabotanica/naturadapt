<?php

namespace App\Controller;

use App\Entity\Discussion;
use App\Entity\DiscussionMessage;
use App\Entity\LogEvent;
use App\Entity\User;
use App\Entity\Usergroup;
use App\Entity\UsergroupMembership;
use App\Form\DiscussionMessageType;
use App\Form\DiscussionType;
use App\Security\GroupDiscussionVoter;
use App\Security\GroupVoter;
use App\Service\DiscussionSender;
use DateTime;
use Doctrine\Common\Persistence\ObjectManager;
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
	 * @param \Doctrine\Common\Persistence\ObjectManager $manager
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function groupDiscussionsIndex (
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
	 * @param \Doctrine\Common\Persistence\ObjectManager                 $manager
	 * @param \Symfony\Component\Routing\Generator\UrlGeneratorInterface $router
	 * @param \App\Service\DiscussionSender                              $sender
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function groupDiscussionNew (
			$groupSlug,
			Request $request,
			ObjectManager $manager,
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

		$this->denyAccessUnlessGranted( GroupDiscussionVoter::CREATE, $group );

		$discussion = new Discussion();
		$form       = $this->createForm( DiscussionType::class, $discussion );

		$form->handleRequest( $request );

		if ( $form->isSubmitted() && $form->isValid() ) {
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
	 * @param \Doctrine\Common\Persistence\ObjectManager                 $manager
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
			ObjectManager $manager,
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
				$discussionMessage->setDiscussion( $discussion );
				$discussionMessage->getDiscussion()->setActiveAt( new DateTime() );
				$discussionMessage->setCreatedAt( new DateTime() );
				$discussionMessage->setAuthor( $this->getUser() );
				$discussionMessage->setBody( $form->get( 'body' )->getData() );
				$manager->persist( $discussionMessage );
				$manager->flush();

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
	 * @param \Doctrine\Common\Persistence\ObjectManager $manager
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function groupDiscussionDelete (
			$groupSlug,
			$discussionUuid,
			Request $request,
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
	 * @Route("/groups/{groupSlug}/message/{messageId}/hide", name="group_message_hide")
	 * @param                                            $groupSlug
	 * @param                                            $messageId
	 * @param \Doctrine\Common\Persistence\ObjectManager $manager
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function groupMessageHide (
			$groupSlug,
			$messageId,
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
	 * @param \Doctrine\Common\Persistence\ObjectManager $manager
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function groupMessageShow (
			$groupSlug,
			$messageId,
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
	 * @param \Doctrine\Common\Persistence\ObjectManager $manager
	 * @param \App\Service\DiscussionSender              $sender
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function webserviceInbound (
			$key,
			Request $request,
			ObjectManager $manager,
			DiscussionSender $sender
	) {
		if ( $key !== $this->getParameter( 'postmark' )[ 'inbound_key' ] ) {
			return new JsonResponse( [ 'status' => 'Invalid API key' ] );
		}

		$data = json_decode( $request->getContent(), TRUE );

		$slug      = explode( '+', explode( '@', $data[ 'OriginalRecipient' ] )[ 0 ] )[ 0 ];
		$hash      = $data[ 'MailboxHash' ];
		$userEmail = $data[ 'From' ];
		$subject   = $data[ 'Subject' ];

		$emailBody = !empty( $data[ 'TextBody' ] ) ? $data[ 'TextBody' ] : strip_tags( $data[ 'HtmlBody' ] );
		$fragments = ( new EmailParser() )->parse( $emailBody )->getFragments();
		$body      = nl2br( current( $fragments )->getContent() );

		/**
		 * @var \App\Entity\User $user
		 */
		$user = $manager->getRepository( User::class )
						->findOneBy( [ 'email' => $userEmail ] );

		if ( !$user ) {
			return new JsonResponse( [ 'status' => 'The user does not exist' ] );
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

		if ( $discussion ) {
			$discussionMessage = new DiscussionMessage();
			$discussionMessage->setDiscussion( $discussion );
			$discussionMessage->getDiscussion()->setActiveAt( new DateTime() );
			$discussionMessage->setCreatedAt( new DateTime() );
			$discussionMessage->setAuthor( $user );
			$discussionMessage->setBody( $body );
			$manager->persist( $discussionMessage );
			$manager->flush();

			// Send Notifications

			$sender->sendDiscussionMessage( $discussionMessage );

			// Log Event

			$log = new LogEvent();
			$log->setType( LogEvent::DISCUSSION_PARTICIPATE );
			$log->setUser( $user );
			$log->setUsergroup( $group );
			$log->setCreatedAt( new DateTime() );
			$log->setData( [ 'discussion' => $discussion->getId(), 'message' => $discussionMessage->getId(), 'title' => $discussion->getTitle() ] );
			$manager->persist( $log );
			$manager->flush();

			return new JsonResponse( [ 'status' => 'message created' ] );
		}
		else {
			$discussion = new Discussion();
			$discussion->setUuid( Uuid::uuid4() );
			$discussion->setTitle( $subject );
			$discussion->setAuthor( $user );
			$discussion->setUsergroup( $group );
			$discussion->setCreatedAt( new DateTime() );
			$manager->persist( $discussion );
			$manager->flush();

			$discussionMessage = new DiscussionMessage();
			$discussionMessage->setDiscussion( $discussion );
			$discussionMessage->getDiscussion()->setActiveAt( new DateTime() );
			$discussionMessage->setCreatedAt( new DateTime() );
			$discussionMessage->setAuthor( $user );
			$discussionMessage->setBody( $body );
			$manager->persist( $discussionMessage );

			// Send Notifications

			$sender->sendDiscussionMessage( $discussionMessage, TRUE );

			// Log Event

			$log = new LogEvent();
			$log->setType( LogEvent::DISCUSSION_CREATE );
			$log->setUser( $user );
			$log->setUsergroup( $group );
			$log->setCreatedAt( new DateTime() );
			$log->setData( [ 'discussion' => $discussion->getId(), 'title' => $discussion->getTitle() ] );
			$manager->persist( $log );
			$manager->flush();

			// --

			return new JsonResponse( [ 'status' => 'discussion created' ] );
		}
	}
}
