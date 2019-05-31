<?php

namespace App\Service;

class EmailSender {
	/**
	 * @var \Swift_Mailer
	 */
	private $mailer;

	public function __construct ( \Swift_Mailer $mailer ) {
		$this->mailer = $mailer;
	}

	public function send ( $from, $to, $subject, $message ) {
		$message = ( new \Swift_Message( $subject ) )
				->setFrom( $from )
				->setTo( $to )
				->setBody( $message, 'text/html' );

		return $this->mailer->send( $message );
	}

	public function getSubjectFromTitle ( $message, $default = 'Subject' ) {
		return preg_match( '/<title[^>]*>(.*?)<\/title>/ims', $message, $matches ) ? $matches[ 1 ] : $default;
	}
}
