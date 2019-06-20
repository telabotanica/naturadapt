<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="articles",indexes={@ORM\Index(name="slug", columns={"slug"})})
 * @ORM\Entity(repositoryClass="App\Repository\ArticlesRepository")
 */
class Article {
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Usergroup", inversedBy="articles")
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
	 * @ORM\OneToOne(targetEntity="App\Entity\File", cascade={"persist", "remove"})
	 */
	private $cover;

	public function getId (): ?int {
		return $this->id;
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
		$this->title = $title;

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

	public function getCover (): ?File {
		return $this->cover;
	}

	public function setCover ( ?File $cover ): self {
		$this->cover = $cover;

		return $this;
	}
}
