<?php

namespace App\Service;

use App\Entity\File;
use App\Entity\User;
use App\Entity\Usergroup;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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

	public function getFileSystem () {
		return $this->filesystem;
	}

	/**
	 * Writes file in filesystem
	 *
	 * @param string                $file
	 * @param \App\Entity\Usergroup $group
	 * @param                       $content
	 *
	 * @return bool|string
	 */
	public function writeFile ( string $file, Usergroup $group, $content ) {
		$filename  = pathinfo( $file, PATHINFO_FILENAME );
		$extension = pathinfo( $file, PATHINFO_EXTENSION );

		try {
			$n = 1;
			do {
				$basename = $filename . ( $n > 1 ? '-' . $n : '' ) . '.' . $extension;
				$fullname = 'group-' . $group->getId() . '/' . $basename;
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
	 * @param \App\Entity\Usergroup                               $group
	 *
	 * @return bool|string
	 */
	public function moveUploadedFile ( UploadedFile $file, Usergroup $group ) {
		return $this->writeFile( $file->getClientOriginalName(), $group, file_get_contents( $file->getRealPath() ) );
	}

	/**
	 * Writes uploaded file in file system and return File object
	 *
	 * @param \Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile
	 * @param \App\Entity\User                                    $user
	 * @param \App\Entity\Usergroup                               $group
	 *
	 * @return \App\Entity\File
	 */
	public function createFromUploadedFile ( UploadedFile $uploadedFile, User $user, Usergroup $group ) {
		$filename = $this->moveUploadedFile( $uploadedFile, $group );

		$file = new File();
		$file->setFilesystem( File::USERGROUP_FILES );
		$file->setUser( $user );
		$file->setUsergroup( $group );
		$file->setName( $uploadedFile->getClientOriginalName() );
		$file->setPath( $filename );
		$file->setType( $uploadedFile->getMimeType() );
		$file->setSize( $uploadedFile->getSize() );

		return $file;
	}
}
