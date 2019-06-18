<?php

namespace App\Controller;

use App\Entity\Document;
use App\Entity\File;
use App\Entity\Upload;
use App\Entity\Usergroup;
use App\Form\UploadType;
use App\Security\GroupVoter;
use App\Service\FileManager;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GroupDocumentsController extends AbstractController {
	/**
	 * @Route("/groups/{groupSlug}/documents", name="group_documents_index")
	 */
	public function documentsIndex () {
		return new Response( "#TODO" );
	}

	/**
	 * @Route("/groups/{groupSlug}/documents/new", name="group_document_new")
	 * @param                                            $groupSlug
	 * @param \Symfony\Component\HttpFoundation\Request  $request
	 * @param \Doctrine\Common\Persistence\ObjectManager $manager
	 * @param \App\Service\FileManager                   $fileManager
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function documentNew (
			$groupSlug,
			Request $request,
			ObjectManager $manager,
			FileManager $fileManager
	) {
		$user = $this->getUser();

		/**
		 * @var $group \App\Entity\Usergroup
		 */
		$group = $manager->getRepository( Usergroup::class )
						 ->findOneBy( [ 'slug' => $groupSlug ] );

		$this->denyAccessUnlessGranted( GroupVoter::EDIT, $group );

		/**************************************************
		 * UPLOAD
		 **************************************************/

		$upload = new Upload();
		$form   = $this->createForm( UploadType::class, $upload );
		$form->handleRequest( $request );

		if ( $form->isSubmitted() && $form->isValid() ) {
			$uploadFile = $upload->getFile();

			/**
			 * @var \App\Service\UsergroupFileManager $usergroupFileManager
			 */
			$usergroupFileManager = $fileManager->getManager( File::USERGROUP_FILES );
			$file                 = $usergroupFileManager->createFromUploadedFile( $uploadFile, $user, $group );

			$manager->persist( $file );

			$document = new Document();
			$document->setUser( $user );
			$document->setUsergroup( $group );
			$document->setFile( $file );

			$manager->flush();
		}

		return $this->render( 'pages/group/group-upload-file.html.twig', [
				'group' => $group,
				'form'  => $form->createView(),
		] );
	}

	/**
	 * @Route("/groups/{groupSlug}/documents/{documentSlug}", name="group_document_get")
	 * @param                                            $groupSlug
	 * @param                                            $documentSlug
	 * @param \Doctrine\Common\Persistence\ObjectManager $manager
	 * @param \App\Service\FileManager                   $fileManager
	 *
	 * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
	 */
	public function getFile (
			$groupSlug,
			$documentSlug,
			ObjectManager $manager,
			FileManager $fileManager ) {
		/**
		 * @var  \App\Entity\Usergroup $group
		 */
		$group = $manager->getRepository( Usergroup::class )
						 ->findOneBy( [ 'slug' => $groupSlug ] );

		$this->denyAccessUnlessGranted( GroupVoter::READ, $group );

		/**
		 * @var  \App\Entity\Document $document
		 */
		$document = $manager->getRepository( Document::class )
							->findOneBy( [ 'usergroup' => $group, 'slug' => $documentSlug ] );

		return $fileManager->getFile( $document->getFile() );
	}
}
