<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\FileManager;

// Import the BinaryFileResponse
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AppController extends AbstractController {
	/**
	 * @Route("/", name="homepage")
	 */
	public function index () {
		return $this->render( 'pages/front.html.twig' );
	}

	/**
	 * @Route("/dump")
	 */
	public function dump () {
		$output = ';-)';

		/**
		 * @var \App\Entity\User $user
		 */
		$user = $this->getUser();

		if ( $user && $user->isAdmin() ) {
			ob_start();
			phpinfo();
			$output = ob_get_clean();
		}

		return new Response( $output );
	}

	/**
	 * @Route("/app/{tab}/{image_type}", name="app_image")
	 *
	 * @param                                            $tab
	 * @param                                            $image_type
	 * @param \Doctrine\ORM\EntityManagerInterface       $manager
	 * @param \App\Service\FileManager                   $fileManager
	 *
	 * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Symfony\Component\HttpFoundation\Response
	 */
	public function appImage (
		$tab,
		$image_type,
		EntityManagerInterface $manager,
		FileManager $fileManager
	) {

		/**
		 * @var \App\Service\AppFileManager $appFileManager
		 */
		$appFileManager = $fileManager->getManager( 'appfiles' );
		$fileId = $appFileManager->getAppImageId($tab, $image_type);

		if ( !empty( $fileId ) ) {
			$file = $appFileManager->getFileById( $fileId );
			return $fileManager->getFile( $file );
		} else {
			return new BinaryFileResponse($appFileManager->getDefaultFile($tab, $image_type));
		}

		throw $this->createNotFoundException( 'There is no '. $image_type . ' image.' );
	}
}
