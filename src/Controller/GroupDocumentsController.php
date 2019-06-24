<?php

namespace App\Controller;

use App\Entity\Document;
use App\Entity\File;
use App\Entity\LogEvent;
use App\Entity\Usergroup;
use App\Form\DocumentType;
use App\Security\GroupDocumentVoter;
use App\Security\GroupVoter;
use App\Service\FileManager;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class GroupDocumentsController extends AbstractController {
	/**************************************************
	 * DOCUMENTS
	 **************************************************/

	/**
	 * @Route("/groups/{groupSlug}/documents", name="group_documents_index")
	 * @param                                            $groupSlug
	 * @param \Symfony\Component\HttpFoundation\Request  $request
	 * @param \Doctrine\Common\Persistence\ObjectManager $manager
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function documentsIndex (
			$groupSlug,
			Request $request,
			ObjectManager $manager
	) {
		/**
		 * @var \App\Entity\Usergroup $group
		 */
		$group = $manager->getRepository( Usergroup::class )
						 ->findOneBy( [ 'slug' => $groupSlug ] );

		if ( !$group ) {
			throw $this->createNotFoundException( 'The group does not exist' );
		}

		$this->denyAccessUnlessGranted( GroupVoter::READ, $group );

		$page     = $request->query->get( 'page', 0 );
		$per_page = 10;

		$filters = $request->query->get( 'form', [] );

		$form = $this->createFormBuilder()
					 ->setMethod( 'get' )
					 ->add( 'filetype', ChoiceType::class, [
							 'required' => FALSE,
							 'expanded' => TRUE,
							 'multiple' => TRUE,
							 'choices'  => [],
					 ] )
					 ->add( 'query', SearchType::class, [
							 'required' => FALSE,
					 ] )
					 ->add( 'submit', SubmitType::class )
					 ->getForm();

		$form->handleRequest( $request );

		return $this->render( 'pages/document/documents-index.html.twig', [
				'group' => $group,
				'form'  => $form->createView(),
		] );
	}

	/**************************************************
	 * DOCUMENT
	 **************************************************/

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
		/**
		 * @var $group \App\Entity\Usergroup
		 */
		$group = $manager->getRepository( Usergroup::class )
						 ->findOneBy( [ 'slug' => $groupSlug ] );

		$this->denyAccessUnlessGranted( GroupDocumentVoter::CREATE, $group );

		/**
		 * @var \App\Entity\User $user
		 */
		$user = $this->getUser();

		/**************************************************
		 * DOCUMENT
		 **************************************************/

		$document = new Document();
		$form     = $this->createForm( DocumentType::class, $document );
		$form->handleRequest( $request );

		if ( $form->isSubmitted() && $form->isValid() ) {
			$document->setUser( $user );
			$document->setUsergroup( $group );
			$document->setCreatedAt( new \DateTime() );

			$manager->persist( $document );

			// File
			$uploadFile = $form->get( 'filefile' )->getData();

			if ( !empty( $uploadFile ) ) {
				/**
				 * @var \App\Service\UsergroupFileManager $groupFileManager
				 */
				$groupFileManager = $fileManager->getManager( File::USERGROUP_FILES );
				$file             = $groupFileManager->createFromUploadedFile( $uploadFile, $user, $group );

				$manager->persist( $file );

				$document->setFile( $file );

				if ( empty( $document->getTitle() ) ) {
					$document->setTitle( pathinfo( $file->getName(), PATHINFO_FILENAME ) );
				}
			}

			$manager->flush();

			// Log Event

			$log = new LogEvent();
			$log->setType( LogEvent::DOCUMENT_CREATE );
			$log->setUser( $this->getUser() );
			$log->setUsergroup( $group );
			$log->setCreatedAt( new \DateTime() );
			$log->setData( [ 'document' => $document->getId(), 'title' => $document->getTitle() ] );
			$manager->persist( $log );
			$manager->flush();

			// --

			$this->addFlash( 'notice', 'messages.document.document_created' );

			return $this->redirectToRoute( 'group_documents_index', [ 'groupSlug' => $group->getSlug() ] );
		}

		return $this->render( 'pages/document/document-create.html.twig', [
				'group' => $group,
				'form'  => $form->createView(),
		] );
	}

	/**
	 * @Route("/groups/{groupSlug}/documents/{documentId}/get", name="group_document_get")
	 * @param                                            $groupSlug
	 * @param                                            $documentId
	 * @param \Doctrine\Common\Persistence\ObjectManager $manager
	 * @param \App\Service\FileManager                   $fileManager
	 *
	 * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
	 */
	public function documentGet (
			$groupSlug,
			$documentId,
			ObjectManager $manager,
			FileManager $fileManager ) {
		/**
		 * @var  \App\Entity\Usergroup $group
		 */
		$group = $manager->getRepository( Usergroup::class )
						 ->findOneBy( [ 'slug' => $groupSlug ] );

		if ( !$group ) {
			throw $this->createNotFoundException( 'The group does not exist' );
		}

		/**
		 * @var \App\Entity\Page $page
		 */
		$document = $manager->getRepository( Document::class )
							->findOneBy( [ 'id' => $documentId ] );

		if ( !$document ) {
			throw $this->createNotFoundException( 'The document does not exist' );
		}

		$this->denyAccessUnlessGranted( GroupDocumentVoter::READ, $document );

		return $fileManager->getFile( $document->getFile() );
	}

	/**
	 * @Route("/groups/{groupSlug}/documents/{documentId}/delete", name="group_document_delete")
	 * @param                                            $groupSlug
	 * @param                                            $documentId
	 * @param \Symfony\Component\HttpFoundation\Request  $request
	 * @param \Doctrine\Common\Persistence\ObjectManager $manager
	 * @param \App\Service\FileManager                   $fileManager
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function documentDelete (
			$groupSlug,
			$documentId,
			Request $request,
			ObjectManager $manager,
			FileManager $fileManager ) {
		/**
		 * @var \App\Entity\Document $document
		 */
		$document = $manager->getRepository( Document::class )
							->findOneBy( [ 'id' => $documentId ] );

		if ( !$document ) {
			throw $this->createNotFoundException( 'The document does not exist' );
		}

		$this->denyAccessUnlessGranted( GroupDocumentVoter::DELETE, $document );

		// Delete confirmation form

		$form = $this->createFormBuilder()
					 ->add( 'submit', SubmitType::class )
					 ->getForm();

		$form->handleRequest( $request );

		if ( $form->isSubmitted() && $form->isValid() ) {
			if ( !empty( $document->getFile() ) ) {
				$fileManager->deleteFile( $document->getFile() );
				$manager->remove( $document->getFile() );
			}

			// Log Event

			$log = new LogEvent();
			$log->setType( LogEvent::DOCUMENT_DELETE );
			$log->setUser( $this->getUser() );
			$log->setUsergroup( $document->getUsergroup() );
			$log->setCreatedAt( new \DateTime() );
			$log->setData( [ 'document' => $document->getId(), 'title' => $document->getTitle() ] );
			$manager->persist( $log );

			// --

			$manager->remove( $document );

			$manager->flush();

			$this->addFlash( 'notice', 'messages.document.document_deleted' );

			return $this->redirectToRoute( 'group_index', [ 'groupSlug' => $groupSlug ] );
		}

		return $this->render( 'pages/confirm.html.twig', [
				'form' => $form->createView(),
		] );
	}
}
