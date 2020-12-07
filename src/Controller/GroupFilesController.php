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

class GroupFilesController extends AbstractController {
	/**
	 * @Route("/file/upload/{groupId}", name="file_upload")
	 * @param                                                            $groupId
	 * @param \Symfony\Component\HttpFoundation\Request                  $request
	 * @param \Doctrine\ORM\EntityManagerInterface                       $manager
	 * @param \App\Service\FileManager                                   $fileManager
	 * @param \Symfony\Component\Routing\Generator\UrlGeneratorInterface $router
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function uploadFile (
			$groupId,
			Request $request,
            EntityManagerInterface $manager,
			FileManager $fileManager,
			UrlGeneratorInterface $router
	) {
		$user = $this->getUser();

		/**
		 * @var $group \App\Entity\Usergroup
		 */
		$group = $manager->getRepository( Usergroup::class )
						 ->findOneBy( [ 'id' => $groupId ] );

		if ( !$group ) {
			throw $this->createNotFoundException( 'The group does not exist' );
		}

		$this->denyAccessUnlessGranted( GroupFileVoter::CREATE, $group );

		/**************************************************
		 * UPLOAD
		 **************************************************/

		$upload = new Upload();
		$form   = $this->createForm( UploadType::class, $upload );
		$form->handleRequest( $request );

		if ( $form->isSubmitted() ) {
			$uploadFile = $upload->getFile();

			/**
			 * @var \App\Service\UsergroupFileManager $usergroupFileManager
			 */
			$usergroupFileManager = $fileManager->getManager( File::USERGROUP_FILES );
			$file                 = $usergroupFileManager->createFromUploadedFile( $uploadFile, $user, $group );

			$manager->persist( $file );
			$manager->flush();

			return new JsonResponse( [ 'url' => $router->generate( 'file_get', [ 'fileId' => $file->getId() ] ) ] );
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
	 * @Route("/file/get/{fileId}", name="file_get")
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

		$this->denyAccessUnlessGranted( GroupFileVoter::READ, $file );

		return $fileManager->getFile( $file );
	}
}
