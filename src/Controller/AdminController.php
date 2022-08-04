<?php

namespace App\Controller;

use App\Form\AdminPlatformType;
use App\Form\AdminHomeType;
use App\Form\AdminGroupsType;
use App\Form\AdminMenusType;
use App\Entity\AppLink;
use App\Entity\AppLinkGroup;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


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
	 * @param \App\Service\AppTextManager                                $appTextManager
	 * @param \Symfony\Component\Routing\Generator\UrlGeneratorInterface $router

	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function adminHomeEdit (
		Request $request,
		EntityManagerInterface $manager,
		\App\Service\FileManager $fileManager,
		\App\Service\AppTextManager $appTextManager,
		UrlGeneratorInterface $router
	) {
		$texts = $appTextManager->getTabText('home');


		$homeForm          = $this->createForm( AdminHomeType::class, $texts);
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


			foreach ( $homeForm->getData() as $key => $value ) {
				$appTextManager->changeText('home', $key, $value);
			}

			return $this->redirectToRoute( 'administration_home' );
		}

		return $this->render( 'pages/user/admin-edit.html.twig', [
			'tab' => 'home',
			'form' => $homeForm->createView(),
			'upload'     => $router->generate( 'admin_file_upload', [ 'tab' => 'home' ] ),
		] );
	}

	/**
	 * @Route("/administration/groups", name="administration_groups")
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function adminGroupsEdit (
		Request $request,
		EntityManagerInterface $manager,
		\App\Service\FileManager $fileManager
	) {
		$groupForm          = $this->createForm( AdminGroupsType::class);
		$groupForm->handleRequest( $request );

		if ( $groupForm->isSubmitted() && $groupForm->isValid() ) {

			// Entete
			$uploadFile = $groupForm->get( 'frontgroupfile' )->getData();

			if ( !empty( $uploadFile ) ) {
				/**
				 * @var \App\Service\AppFileManager $appFileManager
				 */
				$appFileManager = $fileManager->getManager( 'appfiles' );
				$newFile = $appFileManager->changeWithUploadedFile( $uploadFile, 'frontgroup');
				$manager->persist( $newFile );
				$manager->flush();

				// Put the new id in admin config file
				$appFileManager->setAppImageId('groups', 'frontgroup', $newFile->getId());

			}

			return $this->redirectToRoute( 'administration_groups' );
		}
		return $this->render( 'pages/user/admin-edit.html.twig', [
			'tab' => 'groups',
			'form' => $groupForm->createView(),
		] );
	}

	/**
	 * @Route("/administration/menus", name="administration_menus")
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function adminMenusEdit (
		Request $request,
		EntityManagerInterface $manager,
		\App\Service\AppTextManager $appTextManager
	) {
		$menusTexts = $appTextManager->getTabText('menus');
		$appLinkGroup = new AppLinkGroup();
		$linkTemp;
		foreach ($menusTexts['liens'] as $lien) {
			if(!is_null($lien['nom']) & !is_null($lien['lien'])){
				$linkTemp = new AppLink();
				$linkTemp->setNom($lien['nom']);
				$linkTemp->setLien($lien['lien']);
				$appLinkGroup->getLiens()->add($linkTemp);
			}
		}

		$menuForm          = $this->createForm( AdminMenusType::class, $appLinkGroup);
		$menuForm->handleRequest( $request );

		if ( $menuForm->isSubmitted() && $menuForm->isValid() ) {
			$appTextManager->changeLiens('menus', $menuForm->get('liens')->getData());
			return $this->redirectToRoute( 'administration_menus' );
		}

		return $this->render( 'pages/user/admin-edit.html.twig', [
			'tab' => 'menus',
			'form' => $menuForm->createView(),
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
