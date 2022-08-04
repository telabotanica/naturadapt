<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Table(name="sites")
 * @ORM\Entity(repositoryClass="App\Repository\SiteRepository")
 */
class AppLinkGroup {

	private $liens;

	public function __construct () {
		$this->liens = new ArrayCollection();
	}

	public function getLiens(): Collection
    {
        return $this->liens;
    }
}


