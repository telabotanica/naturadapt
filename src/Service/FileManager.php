<?php

namespace App\Service;

use App\Entity\File;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
			AppFileManager $appFileManager,
			CacheManager $cacheManager
	) {
		$this->managers[ File::USER_FILES ]      = $userFileManager;
		$this->managers[ File::USERGROUP_FILES ] = $usergroupFileManager;
		$this->managers[ File::APP_FILES ] = $appFileManager;

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

	public function deleteFile ( File $file ) {
		/**
		 * @var \Gaufrette\FilesystemInterface $fs
		 */
		$fs = $this->getManager( $file->getFilesystem() )
				   ->getFileSystem();

		if ( $fs->has( $file->getPath() ) ) {
			return $fs->delete( $file->getPath() );
		}

		return TRUE;
	}

	public function getResized ( File $file ) {
		$image = $this->cacheManager->resolve( $file->getPath(), 'avatar' );

		return new RedirectResponse( $image );
	}

	public function getManager ( string $filesytem ) {
		return $this->managers[ $filesytem ];
	}

	/**************************************************
	 * UPLOAD TOOLS
	 **************************************************/

	/**
	 * @param     $bytes
	 * @param int $precision
	 *
	 * @return string
	 */
	public function formatSize ( $bytes, $precision = 2 ) {
		$units = array( 'B', 'Ko', 'Mo', 'Go', 'To' );

		$bytes = max( $bytes, 0 );
		$pow   = floor( ( $bytes ? log( $bytes ) : 0 ) / log( 1024 ) );
		$pow   = min( $pow, count( $units ) - 1 );

		$bytes /= pow( 1024, $pow );

		return round( $bytes, $precision ) . ' ' . $units[ $pow ];
	}

	/**
	 *
	 * @param $size
	 *
	 * @return float|mixed
	 */
	public function fileUploadMaxSize ( $size ) {
		$targetSize    = $this->parseSize( $size );
		$serverMaxSize = $this->serverFileUploadMaxSize();

		if ( !empty( $serverMaxSize ) ) {
			return min( $targetSize, $serverMaxSize );
		}

		return $targetSize;
	}

	private $maxSize = -1;

	/**
	 * Get max upload size from server.
	 * from Drupal https://api.drupal.org/api/drupal/includes%21file.inc/function/file_upload_max_size/7.x
	 *
	 * @return float|int
	 */
	private function serverFileUploadMaxSize () {
		if ( $this->maxSize < 0 ) {
			// Start with post_max_size.
			$post_max_size = $this->parseSize( ini_get( 'post_max_size' ) );
			if ( $post_max_size > 0 ) {
				$this->maxSize = $post_max_size;
			}

			// If upload_max_size is less, then reduce. Except if upload_max_size is
			// zero, which indicates no limit.
			$upload_max = $this->parseSize( ini_get( 'upload_max_filesize' ) );
			if ( $upload_max > 0 && $upload_max < $this->maxSize ) {
				$this->maxSize = $upload_max;
			}
		}

		return $this->maxSize;
	}

	/**
	 * @param $size
	 *
	 * @return false|float
	 */
	private function parseSize ( $size ) {
		$unit = preg_replace( '/[^bkmgtpezy]/i', '', $size ); // Remove the non-unit characters from the size.
		$size = preg_replace( '/[^0-9.]/', '', $size ); // Remove the non-numeric characters from the size.
		if ( $unit ) {
			// Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
			return round( $size * pow( 1024, stripos( 'bkmgtpezy', $unit[ 0 ] ) ) );
		}
		else {
			return round( $size );
		}
	}
}
