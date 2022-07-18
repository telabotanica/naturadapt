<?php

namespace App\Controller;

use App\Form\AdminPlatformType;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController {
	/**************************************************
	 * EVENTS
	 **************************************************/

	/**
	 * @Route("/administration/platform", name="administration_platform")
	 * @param \Symfony\Component\HttpFoundation\Request                               $request
	 * @param \App\Service\FileManager                                   $fileManager


	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function adminPlatformEdit (
		Request $request,
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
				$appFileManager = $fileManager->getManager( 'appFiles' );
				$appFileManager->changeWithUploadedFile( $uploadFile );
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
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function adminHomeEdit (
	) {
		return $this->render( 'pages/user/admin-edit.html.twig', [
			'tab' => 'home',
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
