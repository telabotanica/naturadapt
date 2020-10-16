<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class HashGenerator {

	/**
	 * @var \Doctrine\ORM\EntityManagerInterface
	 */
	private $manager;

	/**
	 * @var \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface
	 */
	private $passwordEncoder;

	/**
	 * HashGenerator constructor.
	 *
	 * @param \Doctrine\ORM\EntityManagerInterface                                  $manager
	 * @param \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface $passwordEncoder
	 */
	public function __construct (
        EntityManagerInterface $manager,
        UserPasswordEncoderInterface $passwordEncoder
	) {
		$this->manager         = $manager;
		$this->passwordEncoder = $passwordEncoder;
	}

	/**
	 * @param \App\Entity\User $user
	 *
	 * @return string
	 */
	public function generateUserHash ( User $user ) {
		return $user->getId() . '|' . hash( 'sha256', $user->getId() . $user->getPassword() );
	}

	/**
	 * @param string $hash
	 *
	 * @return \App\Entity\User[]|bool|object[]
	 */
	public function getUserFromHash ( string $hash ) {
		$u = explode( '|', $hash );

		$user = $this->manager->getRepository( User::class )->findOneBy( [ 'id' => $u[ 0 ] ] );

		if ( $user && $this->generateUserHash( $user ) === $hash ) {
			return $user;
		}

		return FALSE;
	}
}
