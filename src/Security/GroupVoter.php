<?php
/**
 * User: Maxime Cousinou
 * Date: 2019-03-29
 * Time: 12:43
 */

namespace App\Security;

use App\Entity\User;
use App\Entity\Usergroup;
use App\Entity\UsergroupMembership;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class GroupVoter extends Voter {
	const READ   = 'read';
	const CREATE = 'create';
	const EDIT   = 'edit';
	const JOIN   = 'join';

	/**
	 * @var \Doctrine\Common\Persistence\ObjectManager
	 */
	private $manager;

	public function __construct ( ObjectManager $manager ) {
		$this->manager = $manager;
	}

	protected function supports ( $attribute, $subject ) {
		if ( in_array( $attribute, [ self::CREATE ] ) ) {
			return TRUE;
		}

		if ( !in_array( $attribute, [ self::READ, self::EDIT, self::JOIN ] ) ) {
			return FALSE;
		}

		if ( !$subject instanceof Usergroup ) {
			return FALSE;
		}

		return TRUE;
	}

	protected function voteOnAttribute ( $attribute, $subject, TokenInterface $token ) {
		$user = $token->getUser();

		if ( ( $user instanceof User ) && $user->isAdmin() ) {
			return TRUE;
		}

		/**
		 * @var Usergroup $group
		 */
		$group = $subject;

		switch ( $attribute ) {
			case self::CREATE:
				return ( $user instanceof User );

			case self::READ:
				if ( $group->getVisibility() === Usergroup::PUBLIC ) {
					return TRUE;
				}

				if ( !$user instanceof User ) {
					return FALSE;
				}

				$membership = $this->manager->getRepository( UsergroupMembership::class )->isMember( $user, $group );

				return !empty( $membership );

			case self::EDIT:
				if ( !$user instanceof User ) {
					return FALSE;
				}

				$membership = $this->manager->getRepository( UsergroupMembership::class )->isMember( $user, $group );

				return !empty( $membership );

			case self::JOIN:
				if ( !$user instanceof User ) {
					return FALSE;
				}

				$membership = $this->manager->getRepository( UsergroupMembership::class )->isMember( $user, $group );

				if ( !empty( $membership ) ) {
					return FALSE;
				}

				if ( $group->getVisibility() === Usergroup::PUBLIC ) {
					return TRUE;
				}

				return FALSE;
		}

		throw new \LogicException( 'This code should not be reached!' );
	}
}
