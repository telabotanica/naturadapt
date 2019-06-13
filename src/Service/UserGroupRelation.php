<?php

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
