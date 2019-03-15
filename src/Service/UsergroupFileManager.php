<?php
/**
 * User: Maxime Cousinou
 * Date: 2019-03-14
 * Time: 15:07
 */

namespace App\Service;

use App\Entity\File;
use App\Entity\Usergroup;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class UsergroupFileManager {
	private $manager;

	/**
	 * @var \Gaufrette\Filesystem $filesystem
	 */
	private $filesystem;

	public function __construct ( ObjectManager $manager, ContainerInterface $container ) {
		$this->manager    = $manager;
		$this->filesystem = $container->get( 'gaufrette.usergroupfiles_filesystem' );
	}

	public function moveUploadedFile ( UploadedFile $file, Usergroup $group ) {
		$filename  = pathinfo( $file->getClientOriginalName(), PATHINFO_FILENAME );
		$extension = $file->guessExtension();

		try {
			$n = 1;
			do {
				$basename = $filename . ( $n > 1 ? '-' . $n : '' ) . '.' . $extension;
				$fullname = 'group-' . $group->getId() . '/' . $basename;
				$n++;
			} while ( $this->filesystem->getAdapter()->exists( $fullname ) );

			$this->filesystem->write( $fullname, file_get_contents( $file->getRealPath() ) );

			return $basename;
		} catch ( FileException $e ) {
			return FALSE;
		}
	}

	public function getUsergroupFile ( File $file ) {
		$filename = $file->getName();
		$filepath = $file->getPath();
		$filetype = $file->getType();

		$fileStream = sprintf( 'gaufrette://usergroupfiles/%s', 'group-' . $file->getUsergroup()->getId() . '/' . $filepath );

		$response = new BinaryFileResponse( $fileStream );
		$response->headers->set( 'Content-Type', $filetype );
		$response->setContentDisposition(
				ResponseHeaderBag::DISPOSITION_INLINE,
				$filename
		);

		return $response;
	}
}
