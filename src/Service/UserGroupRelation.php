<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Usergroup;
use App\Entity\UsergroupMembership;
use Doctrine\ORM\EntityManagerInterface;

class UserGroupRelation {
	private $manager;

	public function __construct (
        EntityManagerInterface $manager
	) {
		$this->manager = $manager;
	}

	public function isAdmin ( ?User $user, Usergroup $group ) {
		if ( empty( $user ) || !( $user instanceof User ) ) {
			return FALSE;
		}

		$membership = $this->manager->getRepository( UsergroupMembership::class )
									->getMembership( $user, $group );

		return !empty( $membership ) && ( $membership->getRole() === UsergroupMembership::ROLE_ADMIN );
	}

	public function isMember ( ?User $user, Usergroup $group ) {
		return $this->manager->getRepository( UsergroupMembership::class )
							 ->isMember( $user, $group );
	}

	public function isSubscribed ( ?User $user, Usergroup $group ) {
		return $this->manager->getRepository( UsergroupMembership::class )
							 ->isSubscribed( $user, $group );
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
