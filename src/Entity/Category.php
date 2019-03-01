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
	 * @ORM\ManyToMany(targetEntity="App\Entity\Usergroup", mappedBy="categories")
	 */
	private $usergroups;

	public function __construct () {
		$this->usergroups = new ArrayCollection();
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
	 * @return Collection|Usergroup[]
	 */
	public function getUsergroups (): Collection {
		return $this->usergroups;
	}

	public function addUsergroup ( Usergroup $usergroup ): self {
		if ( !$this->usergroups->contains ( $usergroup ) ) {
			$this->usergroups[] = $usergroup;
			$usergroup->addCategory ( $this );
		}

		return $this;
	}

	public function removeUsergroup ( Usergroup $usergroup ): self {
		if ( $this->usergroups->contains ( $usergroup ) ) {
			$this->usergroups->removeElement ( $usergroup );
			$usergroup->removeCategory ( $this );
		}

		return $this;
	}
}
