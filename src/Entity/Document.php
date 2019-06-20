<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DocumentRepository")
 */
class Document {
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\User")
	 */
	private $user;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Usergroup", inversedBy="documents")
	 */
	private $usergroup;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\File")
	 */
	private $file;

	/**
	 * @ORM\Column(type="string", length=100, nullable=true)
	 */
	private $slug;

	/**
	 * @ORM\Column(type="string", length=100, nullable=true)
	 */
	private $title;

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

	public function getFile (): ?File {
		return $this->file;
	}

	public function setFile ( ?File $file ): self {
		$this->file = $file;

		return $this;
	}

	public function getSlug (): ?string {
		return $this->slug;
	}

	public function setSlug ( ?string $slug ): self {
		$this->slug = $slug;

		return $this;
	}

	public function getTitle (): ?string {
		return $this->title;
	}

	public function setTitle ( ?string $title ): self {
		$this->title = $title;

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
