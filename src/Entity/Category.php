<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="categories")
 * @ORM\Entity(repositoryClass="App\Repository\CategoryRepository")
 */
class Category {
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
	 * @ORM\ManyToMany(targetEntity="App\Entity\Group", mappedBy="categories")
	 */
	private $groups;

	public function __construct () {
		$this->groups = new ArrayCollection();
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

	public function setDescription ( ?string $description ): self {
		$this->description = $description;

		return $this;
	}

	/**
	 * @return Collection|Group[]
	 */
	public function getGroups (): Collection {
		return $this->groups;
	}

	public function addGroup ( Group $group ): self {
		if ( !$this->groups->contains ( $group ) ) {
			$this->groups[] = $group;
			$group->addCategory ( $this );
		}

		return $this;
	}

	public function removeGroup ( Group $group ): self {
		if ( $this->groups->contains ( $group ) ) {
			$this->groups->removeElement ( $group );
			$group->removeCategory ( $this );
		}

		return $this;
	}
}
