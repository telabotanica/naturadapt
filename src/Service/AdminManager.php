<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserRepository;


class AdminManager {
	private $manager;
	private $formFactory;

	public function __construct ( EntityManagerInterface $manager) {
		$this->manager     = $manager;
	}

	public function getAdminMembers () {
		$manager = $this->manager;

		/**
		 * @var \App\Repository\UserRepository $usersRepository
		 */
		$usersRepository = $manager->getRepository( User::class );

		$members = $usersRepository->searchAdmins();

		return [
				'members' => $members,
		];
	}
}
