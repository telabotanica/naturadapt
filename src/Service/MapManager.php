<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class MapManager
{

    private $manager;
	private $formFactory;

	public function __construct ( EntityManagerInterface $manager) {
		$this->manager     = $manager;
	}

    public function getUsersByRegion () {
		$manager = $this->manager;

		/**
		 * @var \App\Repository\UserRepository $usersRepository
		 */
		$usersRepository = $manager->getRepository( User::class );

		$countByRegion = $usersRepository->countUsersByRegion();

		return [
				'countByRegion' => $countByRegion,
		];
	}
}
