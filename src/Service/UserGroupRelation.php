<?php
/**
 * User: Maxime Cousinou
 * Date: 2019-05-21
 * Time: 13:48
 */

namespace App\Service;

use App\Entity\User;
use App\Entity\Usergroup;
use App\Entity\UsergroupMembership;
use Doctrine\Common\Persistence\ObjectManager;

class UserGroupRelation {
	private $manager;

	public function __construct (
			ObjectManager $manager
	) {
		$this->manager = $manager;
	}

	public function isAdmin ( ?User $user, Usergroup $group ) {
		if ( !$user ) {
			return FALSE;
		}

		if ( $user->isAdmin() ) {
			return TRUE;
		}

		$membdership = $this->manager->getRepository( UsergroupMembership::class )
									 ->getMembership( $user, $group );

		return $membdership->getRole() === UsergroupMembership::ROLE_ADMIN;
	}

	public function isMember ( ?User $user, Usergroup $group ) {
		return $this->manager->getRepository( UsergroupMembership::class )
							 ->isMember( $user, $group );
	}

	public function isBanned ( ?User $user, Usergroup $group ) {
		return $this->manager->getRepository( UsergroupMembership::class )
							 ->isBanned( $user, $group );
	}

	public function isPending ( ?User $user, Usergroup $group ) {
		return $this->manager->getRepository( UsergroupMembership::class )
							 ->isPending( $user, $group );
	}
}
