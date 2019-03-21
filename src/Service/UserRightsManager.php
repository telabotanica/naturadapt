<?php
/**
 * User: Maxime Cousinou
 * Date: 2019-03-21
 * Time: 10:58
 */

namespace App\Service;

use App\Entity\User;
use App\Entity\Usergroup;
use App\Entity\UsergroupMembership;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Container\ContainerInterface;

class UserRightsManager {
	/**
	 * @var \Doctrine\Common\Persistence\ObjectManager
	 */
	private $manager;

	public function __construct ( ObjectManager $manager, ContainerInterface $container ) {
		$this->manager = $manager;
	}

	public function canRegister ( ?User $user ) {
		return empty( $user ) || empty( $user->getId() );
	}

	public function canCreateGroup ( ?User $user ) {
		return !empty( $user );
	}

	public function canReadGroup ( ?User $user, Usergroup $group ) {
		if ( !empty( $user ) && $user->isAdmin() ) {
			return TRUE;
		}

		if ( $group->getVisibility() === 'public' ) {
			return TRUE;
		}

		if ( empty( $user ) ) {
			return FALSE;
		}

		$membership = $this->manager->getRepository( UsergroupMembership::class )->isMember( $user, $group );

		return !empty( $membership );
	}

	public function canEditGroup ( ?User $user, Usergroup $group ) {
		if ( !empty( $user ) && $user->isAdmin() ) {
			return TRUE;
		}

		if ( empty( $user ) ) {
			return FALSE;
		}

		/**
		 * @var UsergroupMembership $membership
		 */
		$membership = $this->manager->getRepository( UsergroupMembership::class )->isMember( $user, $group );

		return !empty( $membership ) && ( $membership->getRole() === 'ROLE_ADMIN' );
	}

	public function canJoinGroup ( ?User $user, Usergroup $group ) {
		$membership = $this->manager->getRepository( UsergroupMembership::class )->isMember( $user, $group );

		return empty( $membership );
	}
}
