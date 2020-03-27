<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DiscussionRepository")
 */
class Discussion {
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\Column(type="string", length=100)
	 */
	private $uuid;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Usergroup", inversedBy="discussions")
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
	private $title;

	/**
	 * @ORM\Column(type="datetime")
	 */
	private $createdAt;

	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\DiscussionMessage", mappedBy="discussion", orphanRemoval=true)
	 * @ORM\OrderBy({"createdAt"="ASC"})
	 */
	private $messages;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	private $activeAt;

	public function __construct () {
		$this->messages = new ArrayCollection();
	}

	public function getId (): ?int {
		return $this->id;
	}

	public function getUuid (): ?string {
		return $this->uuid;
	}

	public function setUuid ( string $uuid ): self {
		$this->uuid = $uuid;

		return $this;
	}

	public function getUsergroup (): ?Usergroup {
		return $this->usergroup;
	}

	public function setUsergroup ( ?Usergroup $usergroup ): self {
		$this->usergroup = $usergroup;

		return $this;
	}

	public function getAuthor (): ?User {
		return $this->author;
	}

	public function setAuthor ( ?User $author ): self {
		$this->author = $author;

		return $this;
	}

	public function getTitle (): ?string {
		return $this->title;
	}

	public function setTitle ( string $title ): self {
		$this->title = trim( $title );

		return $this;
	}

	public function getCreatedAt (): ?\DateTimeInterface {
		return $this->createdAt;
	}

	public function setCreatedAt ( \DateTimeInterface $createdAt ): self {
		$this->createdAt = $createdAt;

		return $this;
	}

	/**
	 * @return Collection|DiscussionMessage[]
	 */
	public function getMessages (): Collection {
		return $this->messages;
	}

	public function addMessage ( DiscussionMessage $message ): self {
		if ( !$this->messages->contains( $message ) ) {
			$this->messages[] = $message;
			$message->setDiscussion( $this );
		}

		return $this;
	}

	public function removeMessage ( DiscussionMessage $message ): self {
		if ( $this->messages->contains( $message ) ) {
			$this->messages->removeElement( $message );
			// set the owning side to null (unless already changed)
			if ( $message->getDiscussion() === $this ) {
				$message->setDiscussion( NULL );
			}
		}

		return $this;
	}

	public function getActiveAt (): ?\DateTimeInterface {
		return $this->activeAt;
	}

	public function setActiveAt ( ?\DateTimeInterface $activeAt ): self {
		$this->activeAt = $activeAt;

		return $this;
	}
}
