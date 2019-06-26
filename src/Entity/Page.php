<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
	 * @ORM\Column(type="text", nullable=true)
	 */
	private $body = '';

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

	/**
	 * @ORM\OneToOne(targetEntity="App\Entity\File", cascade={"persist", "remove"})
	 */
	private $cover;

	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\PageRevision", mappedBy="page", orphanRemoval=true)
	 */
	private $pageRevisions;

	public function __construct () {
		$this->pageRevisions = new ArrayCollection();
	}

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
		$this->title = substr( $title, 0, 100 );

		return $this;
	}

	public function getBody (): ?string {
		return $this->body;
	}

	public function setBody ( ?string $body ): self {
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

	public function getEditionRestricted (): ?bool {
		return !empty( $this->editionRestricted );
	}

	public function setEditionRestricted ( ?bool $editionRestricted ): self {
		$this->editionRestricted = $editionRestricted;

		return $this;
	}

	public function getCover (): ?File {
		return $this->cover;
	}

	public function setCover ( ?File $cover ): self {
		$this->cover = $cover;

		return $this;
	}

	/**
	 * @return Collection|PageRevision[]
	 */
	public function getPageRevisions (): Collection {
		return $this->pageRevisions;
	}

	public function addPageRevision ( PageRevision $pageRevision ): self {
		if ( !$this->pageRevisions->contains( $pageRevision ) ) {
			$this->pageRevisions[] = $pageRevision;
			$pageRevision->setPage( $this );
		}

		return $this;
	}

	public function removePageRevision ( PageRevision $pageRevision ): self {
		if ( $this->pageRevisions->contains( $pageRevision ) ) {
			$this->pageRevisions->removeElement( $pageRevision );
			// set the owning side to null (unless already changed)
			if ( $pageRevision->getPage() === $this ) {
				$pageRevision->setPage( NULL );
			}
		}

		return $this;
	}
}
