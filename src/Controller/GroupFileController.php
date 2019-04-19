<?php

namespace App\Controller;

use App\Entity\File;
use App\Entity\Upload;
use App\Entity\Usergroup;
use App\Form\UploadType;
use App\Service\FileManager;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class GroupFileController extends AbstractController {
	/**
	 * @Route("/groups/{groupSlug}/files/new", name="group_file_upload")
	 * @param                                            $groupSlug
	 * @param \Symfony\Component\HttpFoundation\Request  $request
	 * @param \Doctrine\Common\Persistence\ObjectManager $manager
	 * @param \App\Service\FileManager                   $fileManager
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function uploadFile (
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
			$manager->flush();
		}

		return $this->render( 'pages/group/group-upload-file.html.twig', [
				'group' => $group,
				'form'  => $form->createView(),
		] );
	}

	/**
	 * @Route("/groups/{groupSlug}/files/{fileSlug}", name="group_file_get")
	 * @param                                            $groupSlug
	 * @param                                            $fileSlug
	 * @param \Doctrine\Common\Persistence\ObjectManager $manager
	 * @param \App\Service\FileManager                   $fileManager
	 *
	 * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
	 */
	public function getFile (
			$groupSlug,
			$fileSlug,
			ObjectManager $manager,
			FileManager $fileManager ) {
		/**
		 * @var $group \App\Entity\Usergroup
		 */
		$group = $manager->getRepository( Usergroup::class )
						 ->findOneBy( [ 'slug' => $groupSlug ] );

		/**
		 * @var $file \App\Entity\File
		 */
		$file = $manager->getRepository( File::class )
						->findOneBy( [ 'usergroup' => $group, 'name' => $fileSlug ] );

		return $fileManager->getFile( $file );
	}
}
