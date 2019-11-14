<?php
/**
 * User: Maxime Cousinou
 * Date: 14/11/2019
 * Time: 16:39
 */

namespace App\Service;

class FileMimeManager {
	public const DOCUMENTS = 'docs';
	public const PDF       = 'pdf';
	public const IMAGES    = 'images';

	public static function getMimes ( $documentType = '' ) {
		switch ( $documentType ) {
			case self::DOCUMENTS:
				return [
						'application/msword',
						'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
						'application/vnd.ms-excel',
						'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
						'application/vnd.oasis.opendocument.text',
						'application/vnd.oasis.opendocument.spreadsheet',
				];
				break;

			case self::PDF:
				return [
						'application/pdf',
						'application/x-pdf',
				];
				break;

			case self::IMAGES:
				return [
						'image/gif',
						'image/png',
						'image/jpeg',
				];
				break;

			default:
				return [];
		}
	}
}
