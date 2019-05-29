<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController {
	/**
	 * @Route("/", name="homepage")
	 */
	public function index () {
		if ( $this->isGranted( User::ROLE_USER ) ) {
			return $this->redirectToRoute( 'user_dashboard', [], 302 );
		}

		return $this->forward( 'App\Controller\GroupController::groupsIndex', [] );
	}
}
