<?php

namespace App\Controller;

use App\Entity\Group;
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
		$user = $this->getUser ();

		$groups = $this->getDoctrine ()
					   ->getRepository ( Group::class )
					   ->findAll (1);

		return $this->render ( 'app/front.html.twig', [
				'user'   => $user,
				'groups' => $groups,
		] );
	}
}
