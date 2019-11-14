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
	public const ARCHIVES  = 'archives';

	public static function getMimes ( $documentType = '' ) {
		// https://developer.mozilla.org/fr/docs/Web/HTTP/Basics_of_HTTP/MIME_types/Complete_list_of_MIME_types

		switch ( $documentType ) {
			case self::DOCUMENTS:
				return [
					// TXT
					'text/plain',
					// DOC
					'application/msword',
					// DOCX
					'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
					// ODT
					'application/vnd.oasis.opendocument.text',
					// XLS
					'application/vnd.ms-excel',
					// XLSX
					'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
					// CSV
					'text/csv',
					// ODS
					'application/vnd.oasis.opendocument.spreadsheet',
					// PPT
					'application/vnd.ms-powerpoint',
					// PPTX
					'application/vnd.openxmlformats-officedocument.presentationml.presentation',
					// ODP
					'application/vnd.oasis.opendocument.presentation',
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
						'image/svg+xml',
				];
				break;

			case self::ARCHIVES:
				return [
						'application/zip',
						'application/x-7z-compressed',
						'application/x-tar',
				];
				break;

			default:
				return [];
		}
	}
}
