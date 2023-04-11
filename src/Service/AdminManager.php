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

	/**
	 * get all admin of the communaute group
	 * @return array
	 */
	public function getCommuniteAdminMembers () {
		$manager = $this->manager;

		/**
		 * @var \App\Repository\UserRepository $usersRepository
		 */
		$usersRepository = $manager->getRepository( User::class );

		$members = $usersRepository->searchCommunauteAdmins();

		return [
				'members' => $members,
		];
	}
}
