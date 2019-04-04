<?php
/**
 * User: Maxime Cousinou
 * Date: 2019-03-29
 * Time: 10:28
 */

namespace App\Service;

use Postmark\Transport;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class EmailSender {
	/**
	 * @var \Swift_Mailer
	 */
	private $mailer;

	public function __construct ( \Swift_Mailer $mailer, ParameterBagInterface $params ) {
		$transport    = new Transport( $params->get( 'postmark' )[ 'server_token' ] );
		$this->mailer = new \Swift_Mailer( $transport );
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
