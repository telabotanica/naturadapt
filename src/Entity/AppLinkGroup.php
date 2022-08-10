<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class AppLinkGroup
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;
    private $navbarLiensTitle;
    private $footbarFirstLiensTitle;
    private $footbarSecondLiensTitle;
    private $footbarThirdLiensTitle;

    private $navbarLiens;
    private $footbarFirstLiens;
    private $footbarSecondLiens;
    private $footbarThirdLiens;

    public function __construct()
    {
        $this->navbarLiens = new ArrayCollection();
        $this->footbarFirstLiens = new ArrayCollection();
        $this->footbarSecondLiens = new ArrayCollection();
        $this->footbarThirdLiens = new ArrayCollection();
    }

    public function getNavbarLiensTitle(): string
    {
        return $this->navbarLiensTitle;
    }

    public function getFootbarFirstLiensTitle(): string
    {
        return $this->footbarFirstLiensTitle;
    }

    public function getFootbarSecondLiensTitle(): string
    {
        return $this->footbarSecondLiensTitle;
    }

    public function getFootbarThirdLiensTitle(): string
    {
        return $this->footbarThirdLiensTitle;
    }

    public function setNavbarLiensTitle($navbarLiensTitle)
    {
        $this->navbarLiensTitle = $navbarLiensTitle;
    }

    public function setFootbarFirstLiensTitle($footbarFirstLiensTitle)
    {
        $this->footbarFirstLiensTitle = $footbarFirstLiensTitle;
    }

    public function setFootbarSecondLiensTitle($footbarSecondLiensTitle)
    {
        $this->footbarSecondLiensTitle = $footbarSecondLiensTitle;
    }

    public function setFootbarThirdLiensTitle($footbarThirdLiensTitle)
    {
        $this->footbarThirdLiensTitle = $footbarThirdLiensTitle;
    }

    public function getNavbarLiens(): Collection
    {
        return $this->navbarLiens;
    }

    public function getFootbarFirstLiens(): Collection
    {
        return $this->footbarFirstLiens;
    }

    public function getFootbarSecondLiens(): Collection
    {
        return $this->footbarSecondLiens;
    }

    public function getFootbarThirdLiens(): Collection
    {
        return $this->footbarThirdLiens;
    }
}
