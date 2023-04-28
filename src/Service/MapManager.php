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

		$countUserPerRegion = $usersRepository->countUsersByRegion(False);       
		$countAdaptativeApproachPerRegion = $usersRepository->countUsersByRegion(True);       
		$countUserByCountry = $usersRepository->countUsersByCountry(False);
		$countAdaptativeApproachByCountry = $usersRepository->countUsersByCountry(True);

		return [
			'level1' => [
				'all' => $countUserByCountry,
				'adaptative_approach' => $countAdaptativeApproachByCountry
			],
			'level3' => [
				'all' => $countUserPerRegion,
				'adaptative_approach' => $countAdaptativeApproachPerRegion
			]
		];
	}
}
