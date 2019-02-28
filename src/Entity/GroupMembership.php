<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="groups_memberships")
 * @ORM\Entity(repositoryClass="App\Repository\GroupMembershipRepository")
 */
class GroupMembership {
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="groupMemberships")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $user;

	/**
	 * @ORM\Column(type="datetime")
	 */
	private $joinedAt;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $role;

	/**
	 * @ORM\Column(type="json", nullable=true)
	 */
	private $notificationsSettings = [];

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Group", inversedBy="members")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $grp;

	public function getId (): ?int {
		return $this->id;
	}

	public function getUser (): ?User {
		return $this->user;
	}

	public function setUser ( ?User $user ): self {
		$this->user = $user;

		return $this;
	}

	public function getJoinedAt (): ?\DateTimeInterface {
		return $this->joinedAt;
	}

	public function setJoinedAt ( \DateTimeInterface $joinedAt ): self {
		$this->joinedAt = $joinedAt;

		return $this;
	}

	public function getRole (): ?string {
		return $this->role;
	}

	public function setRole ( ?string $role ): self {
		$this->role = $role;

		return $this;
	}

	public function getNotificationsSettings (): ?array {
		return $this->notificationsSettings;
	}

	public function setNotificationsSettings ( ?array $notificationsSettings ): self {
		$this->notificationsSettings = $notificationsSettings;

		return $this;
	}

	public function getGroup (): ?Group {
		return $this->grp;
	}

	public function setGroup ( ?Group $group ): self {
		$this->grp = $group;

		return $this;
	}
}
