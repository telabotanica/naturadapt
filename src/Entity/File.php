<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="files")
 * @ORM\Entity(repositoryClass="App\Repository\FileRepository")
 */
class File {
	public const USER_FILES      = 'userfiles';
	public const USERGROUP_FILES = 'usergroupfiles';
	public const APP_FILES = 'appFiles';

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
	 * @ORM\ManyToOne(targetEntity="App\Entity\Usergroup", inversedBy="files")
	 */
	private $usergroup;

	/**
	 * @ORM\Column(type="string", length=32)
	 */
	private $filesystem;

	/**
	 * @ORM\Column(type="string", length=100)
	 */
	private $name;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $path;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $type;

	/**
	 * @ORM\Column(type="integer", nullable=true)
	 */
	private $size;

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

	public function getName (): ?string {
		return $this->name;
	}

	public function setName ( string $name ): self {
		$this->name = $name;

		return $this;
	}

	public function getPath (): ?string {
		return $this->path;
	}

	public function setPath ( string $path ): self {
		$this->path = $path;

		return $this;
	}

	public function getType (): ?string {
		return $this->type;
	}

	public function setType ( ?string $type ): self {
		$this->type = substr( $type, 0, 255 );

		return $this;
	}

	public function getSize (): ?int {
		return $this->size;
	}

	public function setSize ( ?int $size ): self {
		$this->size = $size;

		return $this;
	}

	public function getFilesystem (): ?string {
		return $this->filesystem;
	}

	public function setFilesystem ( string $filesystem ): self {
		$this->filesystem = $filesystem;

		return $this;
	}
}
