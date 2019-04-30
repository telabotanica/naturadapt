<?php
/**
 * User: Maxime Cousinou
 * Date: 2019-04-19
 * Time: 10:36
 */

namespace App\Service;

use App\Entity\File;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class FileManager {
	private $managers = [];
	/**
	 * @var CacheManager $cacheManager
	 */
	private $cacheManager;

	public function __construct (
			UserFileManager $userFileManager,
			UsergroupFileManager $usergroupFileManager,
			CacheManager $cacheManager
	) {
		$this->managers[ File::USER_FILES ]      = $userFileManager;
		$this->managers[ File::USERGROUP_FILES ] = $usergroupFileManager;

		$this->cacheManager = $cacheManager;
	}

	public function getFile ( File $file ) {
		$response = new BinaryFileResponse( sprintf( 'gaufrette://%s', $file->getFilesystem() . '/' . $file->getPath() ) );
		$response->headers->set( 'Content-Type', $file->getType() );
		$response->setContentDisposition(
				ResponseHeaderBag::DISPOSITION_INLINE,
				$file->getName()
		);

		return $response;
	}

	public function getResized ( File $file ) {
		$image = $this->cacheManager->resolve( $file->getPath(), 'avatar' );

		return new RedirectResponse( $image );
	}

	public function getManager ( string $filesytem ) {
		return $this->managers[ $filesytem ];
	}
}
