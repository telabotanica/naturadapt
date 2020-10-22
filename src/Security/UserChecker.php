<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface {
	public function checkPreAuth ( UserInterface $user ) {
		if ( !$user instanceof User ) {
			return;
		}

		if ( $user->getStatus() === User::STATUS_DISABLED ) {
			throw new CustomUserMessageAuthenticationException( 'messages.user.inactive' );
		}

		if ( $user->getStatus() === User::STATUS_PENDING ) {
			throw new CustomUserMessageAuthenticationException( 'messages.user.pending' );
		}
	}

	public function checkPostAuth ( UserInterface $user ) {
	}
}
