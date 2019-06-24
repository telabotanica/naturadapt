<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="log_events",indexes={@ORM\Index(name="type", columns={"type"})})
 * @ORM\Entity(repositoryClass="App\Repository\LogEventRepository")
 */
class LogEvent {
	public const USER_REGISTER = 'user:register';
	public const USER_JOIN     = 'user:join';
	public const USER_LEAVE    = 'user:leave';
	public const USER_ADMIN    = 'user:admin';

	public const GROUP_CREATE = 'group:create';
	public const GROUP_EDIT   = 'group:edit';
	public const GROUP_DELETE = 'group:delete';

	public const PAGE_CREATE = 'page:create';
	public const PAGE_EDIT   = 'page:edit';
	public const PAGE_DELETE = 'page:delete';

	public const ARTICLE_CREATE = 'article:create';
	public const ARTICLE_EDIT   = 'article:edit';
	public const ARTICLE_DELETE = 'article:delete';

	public const DOCUMENT_CREATE = 'document:create';
	public const DOCUMENT_EDIT   = 'document:edit';
	public const DOCUMENT_DELETE = 'document:delete';

	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $type;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\User")
	 */
	private $user;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Usergroup", inversedBy="logEvents")
	 */
	private $usergroup;

	/**
	 * @ORM\Column(type="json", nullable=true)
	 */
	private $data = [];

	/**
	 * @ORM\Column(type="datetime")
	 */
	private $createdAt;

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

	public function getUsergroup (): ?Usergroup {
		return $this->usergroup;
	}

	public function setUsergroup ( ?Usergroup $usergroup ): self {
		$this->usergroup = $usergroup;

		return $this;
	}

	public function getData (): ?array {
		return $this->data;
	}

	public function setData ( ?array $data ): self {
		$this->data = $data;

		return $this;
	}

	public function getCreatedAt (): ?\DateTimeInterface {
		return $this->createdAt;
	}

	public function setCreatedAt ( \DateTimeInterface $createdAt ): self {
		$this->createdAt = $createdAt;

		return $this;
	}

	public function getType (): ?string {
		return $this->type;
	}

	public function setType ( string $type ): self {
		$this->type = $type;

		return $this;
	}
}
