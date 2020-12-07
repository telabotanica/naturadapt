<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Usergroup;
use App\Entity\UsergroupMembership;
use Doctrine\ORM\EntityManagerInterface;

class UserGroupRelation {

	private $manager;

	private $community;

	public function __construct (
		EntityManagerInterface $manager,
		Community $community
	) {
		$this->manager = $manager;
		$this->community = $community;
	}

	public function isAdmin ( ?User $user, Usergroup $group ) {
		if ( empty( $user ) || !( $user instanceof User ) ) {
			return FALSE;
		}

		$membership = $this->manager->getRepository( UsergroupMembership::class )
									->getMembership( $user, $group );

		return !empty( $membership ) && ( $membership->getRole() === UsergroupMembership::ROLE_ADMIN );
	}

	public function isCommunityAdmin ( ?User $user ) {
		$communityGroup = $this->community->getGroup();
		if ( !$communityGroup ) {
			return FALSE;
		}

		return $this->isAdmin( $user, $communityGroup );
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

	public function getGroupsUserCanAdmin ( ?User $user, array $groups ) {
		if ( empty( $user ) || !( $user instanceof User ) ) {
			return FALSE;
		}

		if ( $this->isCommunityAdmin( $user ) ) {
			return $groups;
		}

		$groupsUserCanAdmin = [];
		foreach ( $groups as $group ) {
			if ( $this->isAdmin( $user, $group ) ) {
				$groupsUserCanAdmin[] = $group;
			}
		}

		return $groupsUserCanAdmin;
	}
}
