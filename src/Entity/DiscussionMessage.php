<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DiscussionMessageRepository")
 */
class DiscussionMessage {
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Discussion", inversedBy="messages")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $discussion;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\User")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $author;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	private $body;

	/**
	 * @ORM\Column(type="datetime")
	 */
	private $createdAt;

	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 */
	private $masked;

	/**
	 * @ORM\ManyToMany(targetEntity="App\Entity\File")
	 */
	private $files;

	public function __construct () {
		$this->files = new ArrayCollection();
	}

	public function getId (): ?int {
		return $this->id;
	}

	public function getDiscussion (): ?Discussion {
		return $this->discussion;
	}

	public function setDiscussion ( ?Discussion $discussion ): self {
		$this->discussion = $discussion;

		return $this;
	}

	public function getAuthor (): ?User {
		return $this->author;
	}

	public function setAuthor ( ?User $author ): self {
		$this->author = $author;

		return $this;
	}

	public function getBody (): ?string {
		return $this->body;
	}

	public function setBody ( ?string $body ): self {
		$this->body = trim( $body );

		return $this;
	}

	public function getCreatedAt (): ?\DateTimeInterface {
		return $this->createdAt;
	}

	public function setCreatedAt ( \DateTimeInterface $createdAt ): self {
		$this->createdAt = $createdAt;

		return $this;
	}

	public function getMasked (): ?bool {
		return $this->masked;
	}

	public function setMasked ( ?bool $masked ): self {
		$this->masked = $masked;

		return $this;
	}

	/**
	 * @return Collection|File[]
	 */
	public function getFiles (): Collection {
		return $this->files;
	}

	public function addFile ( File $file ): self {
		if ( !$this->files->contains( $file ) ) {
			$this->files[] = $file;
		}

		return $this;
	}

	public function removeFile ( File $file ): self {
		if ( $this->files->contains( $file ) ) {
			$this->files->removeElement( $file );
		}

		return $this;
	}
}
