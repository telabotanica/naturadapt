<?php
/**
 * User: Maxime Cousinou
 * Date: 2019-03-14
 * Time: 15:07
 */

namespace App\Service;

use App\Entity\File;
use App\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UserFileManager {
	private $manager;

	/**
	 * @var \Gaufrette\Filesystem $filesystem
	 */
	private $filesystem;

	public function __construct ( ObjectManager $manager, ContainerInterface $container ) {
		$this->manager    = $manager;
		$this->filesystem = $container->get( 'gaufrette.userfiles_filesystem' );
	}

	public function getFileSystem () {
		return $this->filesystem;
	}

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

	public function moveUploadedFile ( UploadedFile $file, User $user ) {
		$filename  = pathinfo( $file->getClientOriginalName(), PATHINFO_FILENAME );
		$extension = $file->guessExtension();

		try {
			$n = 1;
			do {
				$basename = $filename . ( $n > 1 ? '-' . $n : '' ) . '.' . $extension;
				$fullname = 'user-' . $user->getId() . '/' . $basename;
				$n++;
			} while ( $this->filesystem->getAdapter()->exists( $fullname ) );

			$this->filesystem->write( $fullname, file_get_contents( $file->getRealPath() ) );

			return $fullname;
		} catch ( FileException $e ) {
			return FALSE;
		}
	}
}
