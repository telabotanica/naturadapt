<?php

namespace App\Controller;

use App\Entity\File;
use App\Entity\Upload;
use App\Entity\Usergroup;
use App\Form\UploadType;
use App\Security\GroupFileVoter;
use App\Service\FileManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Throwable;

class AdminFileController extends AbstractController {
	/**
	 * @Route("/admin/file/upload/{tab}", name="admin_file_upload")
	 * @param                                                            $tab
	 * @param \Symfony\Component\HttpFoundation\Request                  $request
	 * @param \Doctrine\ORM\EntityManagerInterface                       $manager
	 * @param \App\Service\FileManager                                   $fileManager
	 * @param \Symfony\Component\Routing\Generator\UrlGeneratorInterface $router
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function uploadAdminFile (
			string $tab,
			Request $request,
            EntityManagerInterface $manager,
			FileManager $fileManager,
			UrlGeneratorInterface $router
	) {

		/**************************************************
		 * UPLOAD
		 **************************************************/

		$upload = new Upload();
		$form   = $this->createForm( UploadType::class, $upload );
		$form->handleRequest( $request );

		if ( $form->isSubmitted() ) {
			$uploadFile = $upload->getFile();

			/**
			 * @var \App\Service\AppFileManager $appFileManager
			 */
			$appFileManager = $fileManager->getManager( File::APP_FILES );
			$file = $appFileManager->changeWithUploadedFile( $uploadFile, $tab);
			$manager->persist( $file );
			$manager->flush();

			return new JsonResponse( [ 'url' => $router->generate( 'admin_file_get', [ 'fileId' => $file->getId() ] ) ] );
		}

		try {
			return new Response( $this->get( 'twig' )
									  ->createTemplate( '{{form(form)}}' )
									  ->render( [ 'form' => $form->createView() ] )
			);
		} catch ( Throwable $e ) {
		}

		return new Response( '' );
	}

	/**
	 * @Route("/admin/file/get/{fileId}", name="admin_file_get")
	 * @param                                            $fileId
	 * @param \Doctrine\ORM\EntityManagerInterface       $manager
	 * @param \App\Service\FileManager                   $fileManager
	 *
	 * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
	 */
	public function getFile (
			$fileId,
            EntityManagerInterface $manager,
			FileManager $fileManager
	) {
		/**
		 * @var  \App\Entity\File $file
		 */
		$file = $manager->getRepository( File::class )
						->findOneBy( [ 'id' => $fileId ] );

		if ( !$file ) {
			throw $this->createNotFoundException( 'The file does not exist' );
		}

		return $fileManager->getFile( $file );
	}
}
