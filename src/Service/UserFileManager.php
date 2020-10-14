<?php

namespace App\Service;

use App\Entity\File;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UserFileManager {
	private $manager;

	/**
	 * @var \Gaufrette\Filesystem $filesystem
	 */
	private $filesystem;

	public function __construct ( EntityManagerInterface $manager, ContainerInterface $container ) {
		$this->manager    = $manager;
		$this->filesystem = $container->get( 'gaufrette.userfiles_filesystem' );
	}

	public function getFileSystem () {
		return $this->filesystem;
	}

	/**
	 * Writes file in filesystem
	 *
	 * @param string                $file
	 * @param \App\Entity\User      $user
	 * @param                       $content
	 *
	 * @return bool|string
	 */
	public function writeFile ( string $file, User $user, $content ) {
		$filename  = pathinfo( $file, PATHINFO_FILENAME );
		$extension = pathinfo( $file, PATHINFO_EXTENSION );

		try {
			$n = 1;
			do {
				$basename = $filename . ( $n > 1 ? '-' . $n : '' ) . '.' . $extension;
				$fullname = 'user-' . $user->getId() . '/' . $basename;
				$n++;
			} while ( $this->filesystem->getAdapter()->exists( $fullname ) );

			$this->filesystem->write( $fullname, $content );

			return $fullname;
		} catch ( FileException $e ) {
			return FALSE;
		}
	}

	/**
	 * Writes uploaded file in file system
	 *
	 * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
	 * @param \App\Entity\User                                    $user
	 *
	 * @return bool|string
	 */
	public function moveUploadedFile ( UploadedFile $file, User $user ) {
		return $this->writeFile( $file->getClientOriginalName(), $user, file_get_contents( $file->getRealPath() ) );
	}

	/**
	 * Writes uploaded file in file system and return File object
	 *
	 * @param \Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile
	 * @param \App\Entity\User                                    $user
	 *
	 * @return \App\Entity\File
	 */
	public function createFromUploadedFile ( UploadedFile $uploadedFile, User $user ) {
		$filename = $this->moveUploadedFile( $uploadedFile, $user );

		$file = new File();
		$file->setFilesystem( File::USER_FILES );
		$file->setUser( $user );
		$file->setName( $uploadedFile->getClientOriginalName() );
		$file->setPath( $filename );
		$file->setType( $uploadedFile->getMimeType() );
		$file->setSize( $uploadedFile->getSize() );

		return $file;
	}
}
