<?php
/**
 * User: Maxime Cousinou
 * Date: 2019-03-29
 * Time: 12:43
 */

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserVoter extends Voter {
	const REGISTER = 'register';

	protected function supports ( $attribute, $subject ) {
		if ( !in_array( $attribute, [ self::REGISTER ] ) ) {
			return FALSE;
		}

		return TRUE;
	}

	protected function voteOnAttribute ( $attribute, $subject, TokenInterface $token ) {
		$user = $token->getUser();

		switch ( $attribute ) {
			case self::REGISTER:
				return ( !$user instanceof User );
		}

		throw new \LogicException( 'This code should not be reached!' );
	}
}
