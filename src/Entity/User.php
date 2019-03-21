<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface {
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\Column(type="string", length=180, unique=true)
	 */
	private $email;

	/**
	 * @ORM\Column(type="json")
	 */
	private $roles = [];

	/**
	 * @var string The hashed password
	 * @ORM\Column(type="string")
	 */
	private $password;

	/**
	 * @ORM\Column(type="string", length=100)
	 */
	private $name;

	/**
	 * @ORM\Column(type="string", length=100, nullable=true)
	 */
	private $location;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	private $presentation;

	/**
	 * @ORM\Column(type="string", length=100, nullable=true)
	 */
	private $avatar;

	/**
	 * @ORM\Column(type="string", length=100, nullable=true)
	 */
	private $profileVisibility;

	/**
	 * @ORM\Column(type="string", length=100, nullable=true)
	 */
	private $locale;

	/**
	 * @ORM\Column(type="string", length=100, nullable=true)
	 */
	private $timezone;

	/**
	 * @ORM\Column(type="datetime")
	 */
	private $createdAt;

	/**
	 * @ORM\Column(type="datetime",nullable=true)
	 */
	private $seenAt;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $resetToken;

	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\UsergroupMembership", mappedBy="user", orphanRemoval=true)
	 */
	private $usergroupMemberships;

	public function __construct () {
		$this->usergroupMemberships = new ArrayCollection();
	}

	public function getId (): ?int {
		return $this->id;
	}

	public function getEmail (): ?string {
		return $this->email;
	}

	public function setEmail ( string $email ): self {
		$this->email = $email;

		return $this;
	}

	/**
	 * A visual identifier that represents this user.
	 *
	 * @see UserInterface
	 */
	public function getUsername (): string {
		return (string) $this->email;
	}

	/**
	 * @see UserInterface
	 */
	public function getRoles (): array {
		$roles = $this->roles;
		// guarantee every user at least has ROLE_USER
		$roles[] = 'ROLE_USER';

		return array_unique( $roles );
	}

	public function setRoles ( array $roles ): self {
		$this->roles = array_unique( $roles );

		return $this;
	}

	public function isAdmin (): ?bool {
		return in_array( 'ROLE_ADMIN', $this->getRoles() );
	}

	/**
	 * @see UserInterface
	 */
	public function getPassword (): string {
		return (string) $this->password;
	}

	public function setPassword ( string $password ): self {
		$this->password = $password;

		return $this;
	}

	/**
	 * @see UserInterface
	 */
	public function getSalt () {
		// not needed when using the "bcrypt" algorithm in security.yaml
	}

	/**
	 * @see UserInterface
	 */
	public function eraseCredentials () {
		// If you store any temporary, sensitive data on the user, clear it here
		// $this->plainPassword = null;
	}

	public function getName (): ?string {
		return $this->name;
	}

	public function setName ( string $name ): self {
		$this->name = $name;

		return $this;
	}

	public function getLocation (): ?string {
		return $this->location;
	}

	public function setLocation ( ?string $location ): self {
		$this->location = $location;

		return $this;
	}

	public function getPresentation (): ?string {
		return $this->presentation;
	}

	public function setPresentation ( ?string $presentation ): self {
		$this->presentation = $presentation;

		return $this;
	}

	public function getAvatar (): ?string {
		return $this->avatar;
	}

	public function setAvatar ( ?string $avatar ): self {
		$this->avatar = $avatar;

		return $this;
	}

	public function getProfileVisibility (): ?string {
		return $this->profileVisibility;
	}

	public function setProfileVisibility ( ?string $profileVisibility ): self {
		$this->profileVisibility = $profileVisibility;

		return $this;
	}

	public function getLocale (): ?string {
		return $this->locale;
	}

	public function setLocale ( ?string $locale ): self {
		$this->locale = $locale;

		return $this;
	}

	public function getTimezone (): ?string {
		return $this->timezone;
	}

	public function setTimezone ( ?string $timezone ): self {
		$this->timezone = $timezone;

		return $this;
	}

	public function getCreatedAt (): ?\DateTimeInterface {
		return $this->createdAt;
	}

	public function setCreatedAt ( \DateTimeInterface $createdAt ): self {
		$this->createdAt = $createdAt;

		return $this;
	}

	public function getSeenAt (): ?\DateTimeInterface {
		return $this->seenAt;
	}

	public function setSeenAt ( ?\DateTimeInterface $seenAt ): self {
		$this->seenAt = $seenAt;

		return $this;
	}

	public function getResetToken (): ?string {
		return $this->resetToken;
	}

	public function setResetToken ( ?string $resetToken ): self {
		$this->resetToken = $resetToken;

		return $this;
	}

	/**
	 * @return Collection|UsergroupMembership[]
	 */
	public function getUsergroupMemberships (): Collection {
		return $this->usergroupMemberships;
	}

	public function addUsergroupMembership ( UsergroupMembership $usergroupMembership ): self {
		if ( !$this->usergroupMemberships->contains( $usergroupMembership ) ) {
			$this->usergroupMemberships[] = $usergroupMembership;
			$usergroupMembership->setUser( $this );
		}

		return $this;
	}

	public function removeUsergroupMembership ( UsergroupMembership $usergroupMembership ): self {
		if ( $this->usergroupMemberships->contains( $usergroupMembership ) ) {
			$this->usergroupMemberships->removeElement( $usergroupMembership );
			// set the owning side to null (unless already changed)
			if ( $usergroupMembership->getUser() === $this ) {
				$usergroupMembership->setUser( NULL );
			}
		}

		return $this;
	}
}
