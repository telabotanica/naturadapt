<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController {
	/**
	 * @Route("/", name="homepage")
	 */
	public function index () {
		return $this->render( 'pages/front.html.twig' );
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
}
