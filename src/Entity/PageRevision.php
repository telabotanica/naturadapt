<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="pages_revisions")
 * @ORM\Entity(repositoryClass="App\Repository\PageRevisionRepository")
 */
class PageRevision {
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Page", inversedBy="pageRevisions")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $page;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\User")
	 */
	private $user;

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

	public function getPage (): ?Page {
		return $this->page;
	}

	public function setPage ( ?Page $page ): self {
		$this->page = $page;

		return $this;
	}

	public function getUser (): ?User {
		return $this->user;
	}

	public function setUser ( ?User $user ): self {
		$this->user = $user;

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
}
