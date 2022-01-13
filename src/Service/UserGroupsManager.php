<?php

namespace App\Service;

use App\Service\Community;
use App\Entity\Usergroup;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;


class UserGroupsManager {
	/**
	 * @var array
	 */
	private $groups;
	/**
	 * @var array
	 */
	private $groupsToActivate;

	/**
	 * Community constructor.
	 *
	 * @param                                            $community
	 * @param \Doctrine\ORM\EntityManagerInterface       $manager
	 */
	public function __construct (
		Community $community,
		EntityManagerInterface $manager
	) {
		$groupsManager = $manager->getRepository( Usergroup::class );
		$this->groups = $groupsManager->getGroupsWithMembers( $community->getGroup(), true );
		$this->groupsToActivate = $groupsManager->getGroupsWithMembers( false, false );
	}

	public function getGroups (): array {
		return $this->groups;
	}
	public function setGroups (array $groups) {
		$this->groups = $groups;
	}

	public function getGroupsToActivate (): array {
		return $this->groupsToActivate;
	}
	public function setGroupsToActivate (array $groupsToActivate) {
		$this->groupsToActivate = $groupsToActivate;
	}

	public function getGroupsFromType(string $groupType): array{
		if($groupType=='groups_elements'){
			$groups = $this->groups;
		} else if ($groupType=='groups_to_activate_elements'){
			$groups = $this->groupsToActivate;
		} else {
			$groups = [];
		}
		return $groups;
	}

	public function getGroupFilteredByIds(array $idsList, string $groupType): array{
		if($groupType=='groups_elements'){
			$groups = $this->groups;
		} else if ($groupType=='groups_to_activate_elements'){
			$groups = $this->groupsToActivate;
		} else {
			$groups = [];
		}
		$result = [];
		foreach($this->groups as $group){
			if (in_array($group->getId(), $idsList)){
				array_push($result, $group);
			}
		}
		return $result;
	}
}
