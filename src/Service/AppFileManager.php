<?php

namespace App\Service;

use App\Entity\File;
use App\Entity\Usergroup;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;


class AppFileManager {
	private $manager;

	/**
	 * @var \Gaufrette\Filesystem $filesystem
	 */
	private $filesystem;

	public function __construct ( EntityManagerInterface $manager, ContainerInterface $container, string $logoPath ) {
		$this->manager    = $manager;
		$this->filesystem = $container->get( 'gaufrette.appfiles_filesystem' );
		$this->logoPath = $logoPath;
	}

	public function getFileSystem () {
		return $this->filesystem;
	}

	/**
	 * Writes file in filesystem
	 *
	 * @param string                $file
	 * @param                       $content
	 *
	 * @return bool|string
	*/
	public function writeFile ( string $file, $content ) {
		$filename  = pathinfo( $file, PATHINFO_FILENAME );
		$extension = pathinfo( $file, PATHINFO_EXTENSION );

		try {
			$this->filesystem->delete('logo.png');
			$this->filesystem->write( 'logo.png', $content );

			return 'logo.png';
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
	public function moveUploadedFile ( UploadedFile $file ) {
		$this->writeFile( $file->getClientOriginalName(), file_get_contents( $file->getRealPath() ) );
	}

	/**
	 * Writes uploaded file in file system and return File object
	 *
	 * @param \Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile
	 *
	 * @return \App\Entity\File
	 */
	public function changeWithUploadedFile ( UploadedFile $uploadedFile ) {
		$this->moveUploadedFile( $uploadedFile );
	}
}
