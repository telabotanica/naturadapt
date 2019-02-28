<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="groups")
 * @ORM\Entity(repositoryClass="App\Repository\GroupRepository")
 */
class Group {
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;

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
	 * @ORM\ManyToMany(targetEntity="App\Entity\Category", inversedBy="groups")
	 * @ORM\joinTable(name="groups_categories")
	 */
	private $categories;

	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\GroupMembership", mappedBy="grp", orphanRemoval=true)
	 */
	private $members;

	public function __construct () {
		$this->categories = new ArrayCollection();
		$this->members    = new ArrayCollection();
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
		if ( !$this->categories->contains ( $category ) ) {
			$this->categories[] = $category;
		}

		return $this;
	}

	public function removeCategory ( Category $category ): self {
		if ( $this->categories->contains ( $category ) ) {
			$this->categories->removeElement ( $category );
		}

		return $this;
	}

	/**
	 * @return Collection|GroupMembership[]
	 */
	public function getMembers (): Collection {
		return $this->members;
	}

	public function addMember ( GroupMembership $member ): self {
		if ( !$this->members->contains ( $member ) ) {
			$this->members[] = $member;
			$member->setGroup ( $this );
		}

		return $this;
	}

	public function removeMember ( GroupMembership $member ): self {
		if ( $this->members->contains ( $member ) ) {
			$this->members->removeElement ( $member );
			// set the owning side to null (unless already changed)
			if ( $member->getGroup () === $this ) {
				$member->setGroup ( NULL );
			}
		}

		return $this;
	}
}
