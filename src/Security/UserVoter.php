<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserVoter extends Voter {
	const LOGGED   = 'user:logged';
	const REGISTER = 'user:register';

	protected function supports ( $attribute, $subject ) {
		if ( in_array( $attribute, [ self::LOGGED, self::REGISTER ] ) ) {
			return TRUE;
		}

		return FALSE;
	}

	protected function voteOnAttribute ( $attribute, $subject, TokenInterface $token ) {
		$user = $token->getUser();

		switch ( $attribute ) {
			case self::LOGGED:
				return ( $user instanceof User );

			case self::REGISTER:
				return ( !$user instanceof User );
		}

		throw new \LogicException( 'This code should not be reached!' );
	}
}
