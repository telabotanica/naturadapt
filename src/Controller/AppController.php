<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\FileManager;
use App\Entity\Usergroup;

// Import the BinaryFileResponse
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AppController extends AbstractController {
	/**
	 * @Route("/", name="homepage")
	 *
	 * @param \Doctrine\ORM\EntityManagerInterface       $manager
	 * @param \App\Service\AppTextManager            $appTextManager
	 */
	public function index (
		EntityManagerInterface $manager,
		\App\Service\AppTextManager $appTextManager
	) {

		$homeTexts = $appTextManager->getTabText('home');

       // Obtenez l'objet token actuel
	   $token = $this->get('security.token_storage')->getToken();
	   
	   // TODO: Code à enlever une fois que les utilisateurs auront mis à jour leurs profils
		// Si l'utilisateur est connecté, récupérez l'objet User
		if ($token && $token->getUser()) {
			$user = $token->getUser();

			if (!$user->getHasBeenNotifiedOfNewAdaptativeApproach()) {
				// Add a flash message to notify the user
				$this->addFlash('warning', 'Une nouvelle fonctionnalité est disponible. <br> Veuillez remplir le nouveau champs "Démarche adaptative" sur votre profil.');
				$user->setHasBeenNotifiedOfNewAdaptativeApproach(true);
				$manager->flush();
			}
		}

		return $this->render( 'pages/front.html.twig', [
			'adminText' => $homeTexts
		] );
	}

	/**
	 * @Route("/dump")
	 */
	public function dump () {
		$output = ';-)';

		/**
		 * @var \App\Entity\User $user
		 */
		$user = $this->getUser();

		if ( $user && $user->isAdmin() ) {
			ob_start();
			phpinfo();
			$output = ob_get_clean();
		}

		return new Response( $output );
	}

	/**
	 * @Route("/app/{tab}/{image_type}", name="app_image")
	 *
	 * @param                                            $tab
	 * @param                                            $image_type
	 * @param \Doctrine\ORM\EntityManagerInterface       $manager
	 * @param \App\Service\FileManager                   $fileManager
	 *
	 * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Symfony\Component\HttpFoundation\Response
	 */
	public function appImage (
		$tab,
		$image_type,
		EntityManagerInterface $manager,
		FileManager $fileManager
	) {

		/**
		 * @var \App\Service\AppFileManager $appFileManager
		 */
		$appFileManager = $fileManager->getManager( 'appfiles' );
		$fileId = $appFileManager->getAppImageId($tab, $image_type);

		if ( !empty( $fileId ) ) {
			$file = $appFileManager->getFileById( $fileId );
			return $fileManager->getFile( $file );
		} else {
			return new BinaryFileResponse($appFileManager->getDefaultFile($tab, $image_type));
		}

		throw $this->createNotFoundException( 'There is no '. $image_type . ' image.' );
	}

	/**
	 * @param                                            $resources
	 * @param \Doctrine\ORM\EntityManagerInterface       $manager
	 * @param \App\Service\AppTextManager            $appTextManager
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function appHeaderMenus (
		$resources,
		EntityManagerInterface $manager,
		\App\Service\AppTextManager $appTextManager
	) {

		$liens = $appTextManager->getTabSectionText('menus', 'navbarLiens');

		return $this->render( 'layout/header-menus.html.twig', [
				'liens' => $liens['liens'],
				'resources' => $resources
		] );
	}

	/**
	 * @param                                            $position
	 * @param \Doctrine\ORM\EntityManagerInterface       $manager
	 * @param \App\Service\AppTextManager            $appTextManager
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function appFooterMenus (
		$position,
		EntityManagerInterface $manager,
		\App\Service\AppTextManager $appTextManager
	) {

		switch ($position) {
			case 'first':
				$liens = $appTextManager->getTabSectionText('menus', 'footbarFirstLiens');
				break;
			case 'second':
				$liens = $appTextManager->getTabSectionText('menus', 'footbarSecondLiens');
				break;
			case 'third':
				$liens = $appTextManager->getTabSectionText('menus', 'footbarThirdLiens');
				break;
		}
		return $this->render( 'layout/footer-menus.html.twig', [
			'title' => $liens['title'],
			'liens' => $liens['liens']
		] );
	} 

	/**
	 * @param \Doctrine\ORM\EntityManagerInterface       $manager
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function headerUserOptions (
		EntityManagerInterface $manager
	) {

		$communauteGroup = $manager->getRepository( Usergroup::class )
			->findOneBy( [ 'slug' => 'communaute' ] );

		return $this->render( 'layout/header-user-options.html.twig', [
			'communauteGroup' => $communauteGroup
		] );
	}

}
