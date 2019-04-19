<?php
/**
 * User: Maxime Cousinou
 * Date: 2019-04-19
 * Time: 10:36
 */

namespace App\Service;

use App\Entity\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class FileManager {
	private $managers = [];

	public function __construct (
			UserFileManager $userFileManager,
			UsergroupFileManager $usergroupFileManager
	) {
		$this->managers[ File::USER_FILES ]      = $userFileManager;
		$this->managers[ File::USERGROUP_FILES ] = $usergroupFileManager;
	}

	public function getFile ( File $file ) {
		$response = new BinaryFileResponse( sprintf( 'gaufrette://%s', $file->getPath() ) );
		$response->headers->set( 'Content-Type', $file->getType() );
		$response->setContentDisposition(
				ResponseHeaderBag::DISPOSITION_INLINE,
				$file->getName()
		);

		return $response;
	}

	public function getManager ( string $filesytem ) {
		return $this->managers[ $filesytem ];
	}
}
