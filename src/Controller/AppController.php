<?php

namespace App\Controller;

use App\Entity\Usergroup;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController {
	/**
	 * @Route("/", name="homepage")
	 */
	public function index () {
		$groups = $this->getDoctrine()
					   ->getRepository( Usergroup::class )
					   ->findAll();

		return $this->render( 'pages/front.html.twig', [
				'groups' => $groups,
		] );
	}

	public function header () {
		/**
		 * @var $user \App\Entity\User
		 */
		$user = $this->getUser();

		return $this->render( 'layout/header.html.twig', [
				'user' => $user,
		] );
	}
}
