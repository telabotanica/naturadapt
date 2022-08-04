<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="sites")
 * @ORM\Entity(repositoryClass="App\Repository\SiteRepository")
 */
class AppLink {

	private $nom;
	private $lien;

	public function __construct ( ) {
	}

	public function getNom (): ?string {
		return $this->nom;
	}

	public function setNom ( string $nom ): self {
		$this->nom = $nom;

		return $this;
	}

	public function getLien (): ?string {
		return $this->lien;
	}

	public function setLien ( string $lien ): self {
		$this->lien = $lien;

		return $this;
	}
}
