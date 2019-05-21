<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="usergroups_memberships",uniqueConstraints={@ORM\UniqueConstraint(name="user_usergroup", columns={"user_id", "usergroup_id"})},indexes={@ORM\Index(name="status", columns={"status"})})
 * @ORM\Entity(repositoryClass="App\Repository\UsergroupMembershipRepository")
 */
class UsergroupMembership {
	const ROLE_ADMIN = 'admin';
	const ROLE_USER  = NULL;

	const STATUS_PENDING = 'pending';
	const STATUS_MEMBER  = 'member';
	const STATUS_BANNED  = 'banned';

	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="usergroupMemberships")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $user;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Usergroup", inversedBy="members")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $usergroup;

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
	 * @ORM\Column(type="string", length=32, nullable=true)
	 */
	private $status;

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

	public function getUsergroup (): ?Usergroup {
		return $this->usergroup;
	}

	public function setUsergroup ( ?Usergroup $usergroup ): self {
		$this->usergroup = $usergroup;

		return $this;
	}

	public function getStatus (): ?string {
		return $this->status;
	}

	public function setStatus ( ?string $status ): self {
		$this->status = $status;

		return $this;
	}
}
