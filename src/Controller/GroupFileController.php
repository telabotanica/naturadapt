<?php

namespace App\Controller;

use App\Entity\File;
use App\Entity\Upload;
use App\Entity\Usergroup;
use App\Form\UploadType;
use App\Service\UsergroupFileManager;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class GroupFileController extends AbstractController {
	/**
	 * @Route("/groups/{groupSlug}/files/new", name="group_file_upload")
	 */
	public function uploadFile ( $groupSlug, Request $request, ObjectManager $manager, UsergroupFileManager $fileManager ) {
		/**
		 * @var $user \App\Entity\User
		 */
		$user = $this->getUser();

		/**
		 * @var $group \App\Entity\Usergroup
		 */
		$group = $this->getDoctrine()
					  ->getRepository( Usergroup::class )
					  ->findOneBy( [ 'slug' => $groupSlug ] );

		/**************************************************
		 * UPLOAD
		 **************************************************/

		$upload = new Upload();
		$form   = $this->createForm( UploadType::class, $upload );
		$form->handleRequest( $request );

		if ( $form->isSubmitted() && $form->isValid() ) {
			$uploadFile = $upload->getFile();

			$filename = $fileManager->moveUploadedFile( $uploadFile, $group );

			$file = new File();
			$file->setUser( $user );
			$file->setUsergroup( $group );
			$file->setName( $uploadFile->getClientOriginalName() );
			$file->setPath( $filename );
			$file->setType( $uploadFile->getMimeType() );
			$file->setSize( $uploadFile->getSize() );

			$manager->persist( $file );
			$manager->flush();
		}

		return $this->render( 'pages/group/group-upload-file.html.twig', [
				'group' => $group,
				'form'  => $form->createView(),
		] );
	}

	/**
	 * @Route("/groups/{groupSlug}/files/{fileSlug}", name="group_file_get")
	 */
	public function getFile ( $groupSlug, $fileSlug, UsergroupFileManager $fileManager ) {
		/**
		 * @var $user \App\Entity\User
		 */
		$user = $this->getUser();

		/**
		 * @var $group \App\Entity\Usergroup
		 */
		$group = $this->getDoctrine()
					  ->getRepository( Usergroup::class )
					  ->findOneBy( [ 'slug' => $groupSlug ] );

		/**
		 * @var $file \App\Entity\File
		 */
		$file = $this->getDoctrine()
					 ->getRepository( File::class )
					 ->findOneBy( [ 'usergroup' => $group, 'name' => $fileSlug ] );

		return $fileManager->getUsergroupFile( $file );
	}
}
