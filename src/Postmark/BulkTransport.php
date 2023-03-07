<?php

namespace App\Postmark;

use Postmark\Transport;
use Swift_Mime_MimePart;
use Swift_Mime_SimpleMessage;

class BulkTransport extends Transport {
	/**
	 * @param array $messages
	 *
	 * @return bool|int
	 */
	public function sendMultiple ( array $messages ) {
		if ( empty( $this->serverToken ) ) {
			return TRUE;
		}

		$client = $this->getHttpClient();

		if ( $evt = $this->_eventDispatcher->createSendEvent( $this, $messages[ 0 ] ) ) {
			$this->_eventDispatcher->dispatchEvent( $evt, 'beforeSendPerformed' );
			if ( $evt->bubbleCancelled() ) {
				return 0;
			}
		}

		$v = $this->version;
		$o = $this->os;

		$total          = count( $messages );
		$sendSuccessful = TRUE;
		$loop           = 0;
		$messagesPool   = [];

		foreach ( $messages as $message ) {
			$loop++;

			$messagesPool[] = $this->getMessagePayload( $message );

			if ( ( ( $loop % 500 ) === 0 ) || ( $loop >= $total ) ) {
				$response       = $client->request( 'POST', 'https://api.postmarkapp.com/email/batch', [
						'headers'     => [
								'X-Postmark-Server-Token' => $this->serverToken,
								'Content-Type'            => 'application/json',
								'User-Agent'              => "swiftmailer-postmark (PHP Version: $v, OS: $o)",
						],
						'json'        => $messagesPool,
						'http_errors' => FALSE,
				] );
				$sendSuccessful = $sendSuccessful && ( $response->getStatusCode() == 200 );

				$messagesPool = [];
			}
		}

		if ( $evt && $sendSuccessful ) {
			$evt->setResult( \Swift_Events_SendEvent::RESULT_SUCCESS );
			$this->_eventDispatcher->dispatchEvent( $evt, 'sendPerformed' );
		}

		return $sendSuccessful
				? $total
				: 0;
	}

	/**************************************************
	 * COPIES OF private METHODS
	 **************************************************/

	/**
	 * Get the number of recipients for a message
	 *
	 * @param Swift_Mime_SimpleMessage $message
	 *
	 * @return int
	 */
	protected function getRecipientCount ( Swift_Mime_SimpleMessage $message ) {
		return count( array_merge(
						(array)$message->getTo(),
						(array)$message->getCc(),
						(array)$message->getBcc() )
		);
	}

	/**
	 * Convert email dictionary with emails and names
	 * to array of emails with names.
	 *
	 * @param array $emails
	 *
	 * @return array
	 */
    protected function convertEmailsArray ( array $emails ) {
		$convertedEmails = array();
		foreach ( $emails as $email => $name ) {
			$convertedEmails[] = $name
					? '"' . str_replace( '"', '\\"', $name ) . "\" <{$email}>"
					: $email;
		}

		return $convertedEmails;
	}

	/**
	 * Gets MIME parts that match the message type.
	 * Excludes parts of type \Swift_Mime_Attachment as those
	 * are handled later.
	 *
	 * @param Swift_Mime_SimpleMessage $message
	 * @param string                   $mimeType
	 *
	 * @return Swift_Mime_MimePart
	 */
    protected function getMIMEPart ( Swift_Mime_SimpleMessage $message, $mimeType ) {
		foreach ( $message->getChildren() as $part ) {
			if ( strpos( $part->getContentType(), $mimeType ) === 0 && !( $part instanceof \Swift_Mime_Attachment ) ) {
				return $part;
			}
		}
	}

	/**
	 * Convert a Swift Mime Message to a Postmark Payload.
	 *
	 * @param Swift_Mime_SimpleMessage $message
	 *
	 * @return object
	 */
    protected function getMessagePayload ( Swift_Mime_SimpleMessage $message ) {
		$payload = [];

		$payload['MessageStream'] = 'broadcast';

		$this->processRecipients( $payload, $message );

		$this->processMessageParts( $payload, $message );

		if ( $message->getHeaders() ) {
			$this->processHeaders( $payload, $message );
		}

		return $payload;
	}

	/**
	 * Applies the recipients of the message into the API Payload.
	 *
	 * @param array                    $payload
	 * @param Swift_Mime_SimpleMessage $message
	 *
	 * @return object
	 */
    protected function processRecipients ( &$payload, $message ) {
		$payload[ 'From' ] = join( ',', $this->convertEmailsArray( $message->getFrom() ) );
		if ( $to = $message->getTo() ) {
			$payload[ 'To' ] = join( ',', $this->convertEmailsArray( $to ) );
		}
		$payload[ 'Subject' ] = $message->getSubject();

		if ( $cc = $message->getCc() ) {
			$payload[ 'Cc' ] = join( ',', $this->convertEmailsArray( $cc ) );
		}
		if ( $reply_to = $message->getReplyTo() ) {
			$payload[ 'ReplyTo' ] = join( ',', $this->convertEmailsArray( $reply_to ) );
		}
		if ( $bcc = $message->getBcc() ) {
			$payload[ 'Bcc' ] = join( ',', $this->convertEmailsArray( $bcc ) );
		}
	}

	/**
	 * Applies the message parts and attachments
	 * into the API Payload.
	 *
	 * @param array                    $payload
	 * @param Swift_Mime_SimpleMessage $message
	 *
	 * @return object
	 */
    protected function processMessageParts ( &$payload, $message ) {
		//Get the primary message.
		switch ( $message->getContentType() ) {
			case 'text/html':
			case 'multipart/alternative':
			case 'multipart/mixed':
				$payload[ 'HtmlBody' ] = $message->getBody();
				break;
			default:
				$payload[ 'TextBody' ] = $message->getBody();
				break;
		}

		// Provide an alternate view from the secondary parts.
		if ( $plain = $this->getMIMEPart( $message, 'text/plain' ) ) {
			$payload[ 'TextBody' ] = $plain->getBody();
		}
		if ( $html = $this->getMIMEPart( $message, 'text/html' ) ) {
			$payload[ 'HtmlBody' ] = $html->getBody();
		}
		if ( $message->getChildren() ) {
			$payload[ 'Attachments' ] = array();
			foreach ( $message->getChildren() as $attachment ) {
				if ( is_object( $attachment ) and $attachment instanceof \Swift_Mime_Attachment ) {
					$a = array(
							'Name'        => $attachment->getFilename(),
							'Content'     => base64_encode( $attachment->getBody() ),
							'ContentType' => $attachment->getContentType(),
					);
					if ( $attachment->getDisposition() != 'attachment' && $attachment->getId() != NULL ) {
						$a[ 'ContentID' ] = 'cid:' . $attachment->getId();
					}
					$payload[ 'Attachments' ][] = $a;
				}
			}
		}
	}

	/**
	 * Applies the headers into the API Payload.
	 *
	 * @param array                    $payload
	 * @param Swift_Mime_SimpleMessage $message
	 *
	 * @return object
	 */
    protected function processHeaders ( &$payload, $message ) {
		$headers = [];

		foreach ( $message->getHeaders()->getAll() as $key => $value ) {
			$fieldName = $value->getFieldName();

			$excludedHeaders = [ 'Subject', 'Content-Type', 'MIME-Version', 'Date' ];

			if ( !in_array( $fieldName, $excludedHeaders ) ) {

				if ( $value instanceof \Swift_Mime_Headers_UnstructuredHeader ||
					 $value instanceof \Swift_Mime_Headers_OpenDKIMHeader ) {
					if ( $fieldName != 'X-PM-Tag' ) {
						array_push( $headers, [
								"Name"  => $fieldName,
								"Value" => $value->getValue(),
						] );
					}
					else {
						$payload[ "Tag" ] = $value->getValue();
					}
				}
				else if ( $value instanceof \Swift_Mime_Headers_DateHeader ||
						  $value instanceof \Swift_Mime_Headers_IdentificationHeader ||
						  $value instanceof \Swift_Mime_Headers_ParameterizedHeader ||
						  $value instanceof \Swift_Mime_Headers_PathHeader ) {
					array_push( $headers, [
							"Name"  => $fieldName,
							"Value" => $value->getFieldBody(),
					] );

					if ( $value->getFieldName() == 'Message-ID' ) {
						array_push( $headers, [
								"Name"  => 'X-PM-KeepID',
								"Value" => 'true',
						] );
					}
				}
			}
		}
		$payload[ 'Headers' ] = $headers;
	}
}
