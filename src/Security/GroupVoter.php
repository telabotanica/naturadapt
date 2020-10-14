<?php

namespace App\Security;

use App\Entity\User;
use App\Entity\Usergroup;
use App\Entity\UsergroupMembership;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class GroupVoter extends Voter {
	const CREATE      = 'group:create';
	const READ        = 'group:read';
	const JOIN        = 'group:join';
	const PARTICIPATE = 'group:participate';
	const EDIT        = 'group:edit';
	const ADMIN       = 'group:admin';
	const DELETE      = 'group:delete';

	/**
	 * @var \Doctrine\ORM\EntityManagerInterface
	 */
	private $manager;

	public function __construct ( EntityManagerInterface $manager ) {
		$this->manager = $manager;
	}

	protected function supports ( $attribute, $subject ) {
		if ( in_array( $attribute, [ self::CREATE ] ) ) {
			return TRUE;
		}

		if ( in_array( $attribute, [ self::READ, self::JOIN, self::PARTICIPATE, self::EDIT, self::ADMIN, self::DELETE ] ) && ( $subject instanceof Usergroup ) ) {
			return TRUE;
		}

		return FALSE;
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

				return $this->manager->getRepository( UsergroupMembership::class )->isMember( $user, $group );

			case self::JOIN:
				if ( !$user instanceof User ) {
					return FALSE;
				}

				$membership = $this->manager->getRepository( UsergroupMembership::class )->getMembership( $user, $group );

				if ( !empty( $membership ) ) {
					return FALSE;
				}

				if ( $group->getVisibility() === Usergroup::PUBLIC ) {
					return TRUE;
				}

				return FALSE;

			case self::PARTICIPATE:
				if ( !$user instanceof User ) {
					return FALSE;
				}

				return $this->manager->getRepository( UsergroupMembership::class )->isMember( $user, $group );

			case self::EDIT:
			case self::ADMIN:
			case self::DELETE:
				if ( !$user instanceof User ) {
					return FALSE;
				}

				$membership = $this->manager->getRepository( UsergroupMembership::class )
											->getMembership( $user, $group );

				return !empty( $membership ) && ( $membership->getRole() === UsergroupMembership::ROLE_ADMIN );
		}

		throw new \LogicException( 'This code should not be reached!' );
	}
}
