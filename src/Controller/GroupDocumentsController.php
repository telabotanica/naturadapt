<?php

namespace App\Controller;

use App\Entity\Document;
use App\Entity\DocumentFolder;
use App\Entity\File;
use App\Entity\LogEvent;
use App\Entity\Usergroup;
use App\Form\DocumentType;
use App\Security\GroupDocumentVoter;
use App\Security\GroupVoter;
use App\Service\FileManager;
use App\Service\FileMimeManager;
use App\Service\SlugGenerator;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class GroupDocumentsController extends AbstractController {
	/**
	 * @param \App\Entity\Document $document
	 * @param                      $filters
	 *
	 * @return bool
	 */
	private function match ( Document $document, $filters ) {
		// Keywords

		$matchKeywords = FALSE;

		if ( !empty( $filters[ 'keywords' ] ) ) {
			$title = SlugGenerator::slugify( $document->getTitle() . '-' . $document->getFile()->getName() );

			foreach ( $filters[ 'keywords' ] as $keyword ) {
				$matchKeywords = $matchKeywords || ( strpos( $title, $keyword ) !== FALSE );
			}
		}
		else {
			$matchKeywords = TRUE;
		}

		if ( !$matchKeywords ) {
			return FALSE;
		}

		// Filetype

		if ( !empty( $filters[ 'filetype' ] ) ) {
			$matchFiletype = FALSE;

			foreach ( $filters[ 'filetype' ] as $type ) {
				$matchFiletype = $matchFiletype || ( in_array( $document->getFile()->getType(), FileMimeManager::getMimes( $type ) ) );
			}
		}
		else {
			$matchFiletype = TRUE;
		}

		if ( !$matchFiletype ) {
			return FALSE;
		}

		//

		return $matchFiletype && $matchKeywords;
	}

	/**************************************************
	 * DOCUMENTS
	 **************************************************/

	/**
	 * @Route("/groups/{groupSlug}/documents", name="group_documents_index")
	 * @param                                            $groupSlug
	 * @param \Symfony\Component\HttpFoundation\Request  $request
	 * @param \Doctrine\ORM\EntityManagerInterface       $manager
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function documentsIndex (
			$groupSlug,
			Request $request,
            EntityManagerInterface $manager
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

		// Filters

		$filters = $request->query->get( 'form', [] );
		unset( $filters[ 'submit' ] );

		if ( !empty( $filters[ 'query' ] ) ) {
			$filters[ 'keywords' ] = explode( '-', SlugGenerator::slugify( $filters[ 'query' ] ) );
			unset( $filters[ 'query' ] );
		}

		$form = $this->createFormBuilder( NULL, [ 'csrf_protection' => FALSE ] )
					 ->setMethod( 'get' )
					 ->add( 'filetype', ChoiceType::class, [
							 'required' => FALSE,
							 'expanded' => TRUE,
							 'multiple' => TRUE,
							 'choices'  => [
									 'pages.document.list.types.' . FileMimeManager::DOCUMENTS => FileMimeManager::DOCUMENTS,
									 'pages.document.list.types.' . FileMimeManager::PDF       => FileMimeManager::PDF,
									 'pages.document.list.types.' . FileMimeManager::IMAGES    => FileMimeManager::IMAGES,
									 'pages.document.list.types.' . FileMimeManager::ARCHIVES  => FileMimeManager::ARCHIVES,
							 ],
					 ] )
					 ->add( 'query', SearchType::class, [
							 'required' => FALSE,
					 ] )
					 ->add( 'submit', SubmitType::class )
					 ->getForm();

		$form->handleRequest( $request );

		// Folders

		$folders = $group->getDocumentFolders();

		foreach ( $folders as $folder ) {
			$count = 0;
			foreach ( $folder->getDocuments() as $document ) {
				if ( !$this->match( $document, $filters ) ) {
					$folder->removeDocument( $document );
				}
				else {
					$count++;
				}
			}

			if ( empty( $count ) ) {
				$folders->removeElement( $folder );
			}
		}

		// Documents

		$documents = $manager->getRepository( Document::class )->findRootDocuments( $group );

		$documents = array_filter( $documents, function ( $document ) use ( $filters ) {
			return $this->match( $document, $filters );
		} );

		return $this->render( 'pages/document/documents-index.html.twig', [
				'group'     => $group,
				'folders'   => $folders,
				'documents' => $documents,
				'form'      => $form->createView(),
		] );
	}

	/**************************************************
	 * DOCUMENT
	 **************************************************/

	/**
	 * @Route("/groups/{groupSlug}/documents/new", name="group_document_new")
	 * @param                                            $groupSlug
	 * @param \Symfony\Component\HttpFoundation\Request  $request
	 * @param \Doctrine\ORM\EntityManagerInterface       $manager
	 * @param \App\Service\FileManager                   $fileManager
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @throws \Exception
	 */
	public function documentNew (
			$groupSlug,
			Request $request,
            EntityManagerInterface $manager,
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

		$existingFolders = $manager->getRepository( DocumentFolder::class )
								   ->findForGroup( $group );

		$document = new Document();
		$form     = $this->createForm( DocumentType::class, $document, [ 'folders' => $existingFolders ] );
		$form->handleRequest( $request );

		if ( $form->isSubmitted() && $form->isValid() ) {
			$document->setUser( $user );
			$document->setUsergroup( $group );
			$document->setCreatedAt( new DateTime() );

			$folderTitle = trim( $form->get( 'folderTitle' )->getData() );

			if ( !empty( $folderTitle ) ) {
				$folder = $manager->getRepository( DocumentFolder::class )
								  ->findOneBy( [ 'usergroup' => $group, 'title' => $folderTitle ] );

				if ( !$folder ) {
					$folder = new DocumentFolder();
					$folder->setUsergroup( $group );
					$folder->setTitle( $folderTitle );

					$manager->persist( $folder );
				}

				$document->setFolder( $folder );
			}
			else {
				$document->setFolder( NULL );
			}

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
			$log->setCreatedAt( new DateTime() );
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
	 * @Route("/groups/{groupSlug}/documents/{documentId}/edit", name="group_document_edit")
	 * @param                                            $groupSlug
	 * @param                                            $documentId
	 * @param \Symfony\Component\HttpFoundation\Request  $request
	 * @param \Doctrine\ORM\EntityManagerInterface       $manager
	 * @param \App\Service\FileManager                   $fileManager
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @throws \Exception
	 */
	public function documentEdit (
			$groupSlug,
			$documentId,
			Request $request,
            EntityManagerInterface $manager,
			FileManager $fileManager
	) {
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

		$this->denyAccessUnlessGranted( GroupDocumentVoter::EDIT, $document );

		/**
		 * @var \App\Entity\User $user
		 */
		$user = $this->getUser();

		/**************************************************
		 * DOCUMENT
		 **************************************************/

		$existingFolders = $manager->getRepository( DocumentFolder::class )
								   ->findForGroup( $group );

		$form = $this->createForm( DocumentType::class, $document, [ 'folders' => $existingFolders ] );
		$form->handleRequest( $request );

		if ( $form->isSubmitted() && $form->isValid() ) {
			$folderTitle    = trim( $form->get( 'folderTitle' )->getData() );
			$previousFolder = $document->getFolder();

			// Set Folder

			if ( !empty( $folderTitle ) ) {
				if ( ( empty( $previousFolder ) || ( $previousFolder->getTitle() !== $folderTitle ) ) ) {
					$folder = $manager->getRepository( DocumentFolder::class )
									  ->findOneBy( [ 'usergroup' => $group, 'title' => $folderTitle ] );

					if ( !$folder ) {
						$folder = new DocumentFolder();
						$folder->setUsergroup( $group );
						$folder->setTitle( $folderTitle );

						$manager->persist( $folder );
					}

					$document->setFolder( $folder );
				}
			}
			else {
				$document->setFolder( NULL );
			}

			// File
			$uploadFile = $form->get( 'filefile' )->getData();

			if ( FALSE && !empty( $uploadFile ) ) {
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

			// Clean previous Folder

			if ( !empty( $previousFolder ) && ( $previousFolder->getTitle() !== $folderTitle ) ) {
				$documents = $manager->getRepository( Document::class )
									 ->findBy( [ 'folder' => $previousFolder ] );

				if ( empty( $documents ) ) {
					$manager->remove( $previousFolder );
				}
			}

			// Log Event

			$log = new LogEvent();
			$log->setType( LogEvent::DOCUMENT_EDIT );
			$log->setUser( $this->getUser() );
			$log->setUsergroup( $group );
			$log->setCreatedAt( new DateTime() );
			$log->setData( [ 'document' => $document->getId(), 'title' => $document->getTitle() ] );
			$manager->persist( $log );
			$manager->flush();

			// --

			$this->addFlash( 'notice', 'messages.document.document_updated' );

			return $this->redirectToRoute( 'group_documents_index', [ 'groupSlug' => $group->getSlug() ] );
		}

		return $this->render( 'pages/document/document-edit.html.twig', [
				'group'    => $group,
				'form'     => $form->createView(),
				'document' => $document,
		] );
	}

	/**
	 * @Route("/groups/{groupSlug}/documents/{documentId}/get", name="group_document_get")
	 * @param                                            $groupSlug
	 * @param                                            $documentId
	 * @param \Doctrine\ORM\EntityManagerInterface       $manager
	 * @param \App\Service\FileManager                   $fileManager
	 *
	 * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
	 */
	public function documentGet (
			$groupSlug,
			$documentId,
            EntityManagerInterface $manager,
			FileManager $fileManager
	) {
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
	 * @param \Doctrine\ORM\EntityManagerInterface       $manager
	 * @param \App\Service\FileManager                   $fileManager
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @throws \Exception
	 */
	public function documentDelete (
			$groupSlug,
			$documentId,
			Request $request,
            EntityManagerInterface $manager,
			FileManager $fileManager
	) {
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
			$log->setCreatedAt( new DateTime() );
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
