<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="usergroups")
 * @ORM\Entity(repositoryClass="App\Repository\UsergroupRepository")
 */
class Usergroup {
	public const PUBLIC  = 'public';
	public const PRIVATE = 'private';

	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\Column(type="string", length=100, unique=true)
	 */
	private $slug;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $name;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	private $description;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	private $presentation;

	/**
	 * @ORM\Column(type="string", length=10)
	 */
	private $visibility;

	/**
	 * @ORM\Column(type="array")
	 */
	private $activeApps = [];

	/**
	 * @ORM\ManyToMany(targetEntity="App\Entity\Category", inversedBy="usergroups")
	 * @ORM\JoinTable(name="usergroups_categories")
	 */
	private $categories;

	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\UsergroupMembership", mappedBy="usergroup", orphanRemoval=true)
	 */
	private $members;

	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\Page", mappedBy="usergroup", orphanRemoval=true)
	 * @ORM\OrderBy({"title"="ASC"})
	 */
	private $pages;

	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\File", mappedBy="usergroup")
	 */
	private $files;

	/**
	 * @ORM\Column(type="datetime")
	 */
	private $createdAt;

	public function __construct () {
		$this->categories = new ArrayCollection();
		$this->members    = new ArrayCollection();
		$this->pages      = new ArrayCollection();
		$this->files      = new ArrayCollection();
	}

	public function getId (): ?int {
		return $this->id;
	}

	public function getName (): ?string {
		return $this->name;
	}

	public function setName ( string $name ): self {
		$this->name = $name;

		return $this;
	}

	public function getDescription (): ?string {
		return $this->description;
	}

	public function setDescription ( string $description ): self {
		$this->description = $description;

		return $this;
	}

	public function getPresentation (): ?string {
		return $this->presentation;
	}

	public function setPresentation ( string $presentation ): self {
		$this->presentation = $presentation;

		return $this;
	}

	public function getVisibility (): ?string {
		return $this->visibility;
	}

	public function setVisibility ( string $visibility ): self {
		$this->visibility = $visibility;

		return $this;
	}

	public function getActiveApps (): ?array {
		return $this->activeApps;
	}

	public function setActiveApps ( array $activeApps ): self {
		$this->activeApps = $activeApps;

		return $this;
	}

	/**
	 * @return Collection|Category[]
	 */
	public function getCategories (): Collection {
		return $this->categories;
	}

	public function addCategory ( Category $category ): self {
		if ( !$this->categories->contains( $category ) ) {
			$this->categories[] = $category;
		}

		return $this;
	}

	public function removeCategory ( Category $category ): self {
		if ( $this->categories->contains( $category ) ) {
			$this->categories->removeElement( $category );
		}

		return $this;
	}

	/**
	 * @return Collection|UsergroupMembership[]
	 */
	public function getMembers (): Collection {
		return $this->members;
	}

	public function addMember ( UsergroupMembership $member ): self {
		if ( !$this->members->contains( $member ) ) {
			$this->members[] = $member;
			$member->setGroup( $this );
		}

		return $this;
	}

	public function removeMember ( UsergroupMembership $member ): self {
		if ( $this->members->contains( $member ) ) {
			$this->members->removeElement( $member );
			// set the owning side to null (unless already changed)
			if ( $member->getGroup() === $this ) {
				$member->setGroup( NULL );
			}
		}

		return $this;
	}

	/**
	 * @return Collection|Page[]
	 */
	public function getPages (): Collection {
		return $this->pages;
	}

	public function addPage ( Page $usergroupPage ): self {
		if ( !$this->pages->contains( $usergroupPage ) ) {
			$this->pages[] = $usergroupPage;
			$usergroupPage->setUsergroup( $this );
		}

		return $this;
	}

	public function removePage ( Page $usergroupPage ): self {
		if ( $this->pages->contains( $usergroupPage ) ) {
			$this->pages->removeElement( $usergroupPage );
			// set the owning side to null (unless already changed)
			if ( $usergroupPage->getUsergroup() === $this ) {
				$usergroupPage->setUsergroup( NULL );
			}
		}

		return $this;
	}

	public function getSlug (): ?string {
		return $this->slug;
	}

	public function setSlug ( string $slug ): self {
		$this->slug = substr( $slug, 0, 100 );

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
			$file->setUsergroup( $this );
		}

		return $this;
	}

	public function removeFile ( File $file ): self {
		if ( $this->files->contains( $file ) ) {
			$this->files->removeElement( $file );
			// set the owning side to null (unless already changed)
			if ( $file->getUsergroup() === $this ) {
				$file->setUsergroup( NULL );
			}
		}

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
