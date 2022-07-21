<?php

namespace App\Controller;

use App\Form\AdminPlatformType;
use App\Form\AdminHomeType;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;


class AdminController extends AbstractController {
	/**************************************************
	 * EVENTS
	 **************************************************/

	/**
	 * @Route("/administration/platform", name="administration_platform")
	 * @param \Symfony\Component\HttpFoundation\Request                               $request
	 * @param \Doctrine\ORM\EntityManagerInterface                       $manager,
	 * @param \App\Service\FileManager                                   $fileManager
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function adminPlatformEdit (
		Request $request,
		EntityManagerInterface $manager,
		\App\Service\FileManager $fileManager
	) {
		$platformForm          = $this->createForm( AdminPlatformType::class);
		$platformForm->handleRequest( $request );

		if ( $platformForm->isSubmitted() && $platformForm->isValid() ) {

			// Logo
			$uploadFile = $platformForm->get( 'logofile' )->getData();

			if ( !empty( $uploadFile ) ) {
				/**
				 * @var \App\Service\AppFileManager $appFileManager
				 */
				$appFileManager = $fileManager->getManager( 'appfiles' );
				$newLogoFile = $appFileManager->changeWithUploadedFile( $uploadFile, 'logo');
				$manager->persist( $newLogoFile );
				$manager->flush();

				// Put the new logo id in admin config file
				$appFileManager->setAppImageId('platform', 'logo', $newLogoFile->getId());

			}

			return $this->redirectToRoute( 'administration_platform' );
		}

		return $this->render( 'pages/user/admin-edit.html.twig', [
			'tab' => 'platform',
			'form' => $platformForm->createView(),
		] );
	}


	/**
	 * @Route("/administration/home", name="administration_home")
	 * @param \Symfony\Component\HttpFoundation\Request                               $request
	 * @param \Doctrine\ORM\EntityManagerInterface                       $manager,
	 * @param \App\Service\FileManager                                   $fileManager
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function adminHomeEdit (
		Request $request,
		EntityManagerInterface $manager,
		\App\Service\FileManager $fileManager
	) {

		$homeForm          = $this->createForm( AdminHomeType::class);
		$homeForm->handleRequest( $request );

		if ( $homeForm->isSubmitted() && $homeForm->isValid() ) {

			// Front
			$uploadFile = $homeForm->get( 'frontfile' )->getData();

			if ( !empty( $uploadFile ) ) {
				/**
				 * @var \App\Service\AppFileManager $appFileManager
				 */
				$appFileManager = $fileManager->getManager( 'appfiles' );
				$newFrontFile = $appFileManager->changeWithUploadedFile( $uploadFile, 'front');
				$manager->persist( $newFrontFile );
				$manager->flush();

				// Put the new front id in admin config file
				$appFileManager->setAppImageId('home', 'front', $newFrontFile->getId());
			}

			return $this->redirectToRoute( 'administration_home' );
		}

		return $this->render( 'pages/user/admin-edit.html.twig', [
			'tab' => 'home',
			'form' => $homeForm->createView(),
		] );
	}

	/**
	 * @Route("/administration/groups", name="administration_groups")
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function adminGroupsEdit (
	) {
		return $this->render( 'pages/user/admin-edit.html.twig', [
			'tab' => 'groups',
		] );
	}

	/**
	 * @Route("/administration/menus", name="administration_menus")
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function adminMenusEdit (
	) {
		return $this->render( 'pages/user/admin-edit.html.twig', [
			'tab' => 'menus',
		] );
	}

	/**
	 * @Route("/administration/administrators", name="administration_administrators")
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function adminAdministratorsEdit (
	) {
		return $this->render( 'pages/user/admin-edit.html.twig', [
			'tab' => 'admin',
		] );
	}
}
