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


	// Get the number of users by region and country
    public function getUsersByGeoSubdivision() {
		$manager = $this->manager;

		/**
		 * @var \App\Repository\UserRepository $usersRepository
		 */
		$usersRepository = $manager->getRepository( User::class );

		$countByRegion = $usersRepository->countUsersByRegion();
		$countByCountry = $usersRepository->countUsersByCountry();

		return [
			'level1' => $countByCountry,
			'level3' => $countByRegion,
		];
	}
}
