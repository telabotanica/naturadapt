<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="pages",indexes={@ORM\Index(name="slug", columns={"slug"})})
 * @ORM\Entity(repositoryClass="App\Repository\PageRepository")
 */
class Page {
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Usergroup", inversedBy="pages")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $usergroup;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\User")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $author;

	/**
	 * @ORM\Column(type="string", length=100)
	 */
	private $slug;

	/**
	 * @ORM\Column(type="string", length=100)
	 */
	private $title;

	/**
	 * @ORM\Column(type="text")
	 */
	private $body;

	/**
	 * @ORM\Column(type="datetime")
	 */
	private $createdAt;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	private $editedAt;

	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 */
	private $editionRestricted;

	public function getId (): ?int {
		return $this->id;
	}

	public function getSlug (): ?string {
		return $this->slug;
	}

	public function setSlug ( string $slug ): self {
		$this->slug = substr( $slug, 0, 100 );

		return $this;
	}

	public function getTitle (): ?string {
		return $this->title;
	}

	public function setTitle ( string $title ): self {
		$this->title = substr( $title, 0, 100 );

		return $this;
	}

	public function getBody (): ?string {
		return $this->body;
	}

	public function setBody ( string $body ): self {
		$this->body = $body;

		return $this;
	}

	public function getCreatedAt (): ?\DateTimeInterface {
		return $this->createdAt;
	}

	public function setCreatedAt ( \DateTimeInterface $createdAt ): self {
		$this->createdAt = $createdAt;

		return $this;
	}

	public function getEditedAt (): ?\DateTimeInterface {
		return $this->editedAt;
	}

	public function setEditedAt ( ?\DateTimeInterface $editedAt ): self {
		$this->editedAt = $editedAt;

		return $this;
	}

	public function getAuthor (): ?User {
		return $this->author;
	}

	public function setAuthor ( ?User $author ): self {
		$this->author = $author;

		return $this;
	}

	public function getUsergroup (): ?Usergroup {
		return $this->usergroup;
	}

	public function setUsergroup ( ?Usergroup $usergroup ): self {
		$this->usergroup = $usergroup;

		return $this;
	}

	public function getEditionRestricted (): ?bool {
		return !empty( $this->editionRestricted );
	}

	public function setEditionRestricted ( ?bool $editionRestricted ): self {
		$this->editionRestricted = $editionRestricted;

		return $this;
	}
}
