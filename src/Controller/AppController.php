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
		/**
		 * @var $user \App\Entity\User
		 */
		$user = $this->getUser();

		$groups = $this->getDoctrine()
					   ->getRepository( Usergroup::class )
					   ->findAll();

		return $this->render( 'pages/front.html.twig', [
				'user'   => $user,
				'groups' => $groups,
		] );
	}
}
