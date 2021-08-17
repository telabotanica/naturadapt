<?php

namespace App\Service;

use App\Entity\DiscussionMessage;
use App\Postmark\BulkTransport;
use Swift_Message;
use Throwable;
use Twig\Environment;

class DiscussionSender {
	/**
	 * @var \App\Postmark\BulkTransport
	 */
	private $transport;

	private $params;

	private $twig;

	/**
	 * BulkSender constructor.
	 *
	 * @param \App\Postmark\BulkTransport $transport
	 * @param                             $params
	 * @param \Twig\Environment           $twig
	 */
	public function __construct ( BulkTransport $transport, $params, Environment $twig ) {
		$this->transport = $transport;
		$this->params    = $params;
		$this->twig      = $twig;
	}

	/**
	 * @param \App\Entity\DiscussionMessage $discussionMessage
	 * @param bool                          $first
	 *
	 * @return bool|int
	 */
	public function sendDiscussionMessage ( DiscussionMessage $discussionMessage, $first = FALSE ) {
		$subject = ( $first ? '' : 'Re: ' ) . $discussionMessage->getDiscussion()->getTitle();
		$from    = 'noreply@' . $this->params[ 'list_domain' ];

		$to       = $discussionMessage->getDiscussion()->getUsergroup()->getMembers();
		$messages = [];

		/**
		 * @var \App\Entity\UsergroupMembership $membership
		 */
		foreach ( $to as $membership ) {
			if ( $membership->shouldReceiveDiscussionsEmails() ) {
				$user = $membership->getUser();

				try {
					$body = $this->twig->render( $first ? 'emails/discussion-new.html.twig' : 'emails/discussion-message.html.twig', [
							'user'    => $user,
							'message' => $discussionMessage,
					] );
				} catch ( Throwable $e ) {
					$body = '';
				}

				$message = ( new Swift_Message( $subject ) )
					->setFrom( $from )
					->setTo( $user->getEmail() )
					->setReplyTo( $discussionMessage->getDiscussion()->getUsergroup()->getSlug() . '+' . $discussionMessage->getDiscussion()->getUuid() . '@' . $this->params[ 'list_domain' ] )
					->setBody( $body, 'text/html' );

				// set headers to disable auto responders
				$headers = $message->getHeaders();
				$headers->addTextHeader('Auto-submitted', 'auto-generated'); // RFC 3834
				$headers->addTextHeader('List-Id', $discussionMessage->getDiscussion()->getId()); // RFC 2919
				$headers->addTextHeader('Precedence', 'list'); // MS Outlook
				$headers->addTextHeader('X-Auto-Response-Suppress', 'All'); // MS Outlook

				$messages[] = $message;
			}
		}

		return $this->transport->sendMultiple( $messages );
	}
}
