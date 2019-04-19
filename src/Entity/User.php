<?php

namespace App\Entity;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface {
	public const STATUS_DISABLED = 0;
	public const STATUS_ACTIVE   = 1;
	public const STATUS_PENDING  = 2;

	public const ROLE_USER  = 'ROLE_USER';
	public const ROLE_ADMIN = 'ROLE_ADMIN';

	public const TYPE_PRIVATE       = 'private';
	public const TYPE_PROFESSIONNAL = 'professional';

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
	 * @ORM\Column(type="smallint")
	 */
	private $status;

	/**
	 * @ORM\Column(type="string", length=100, nullable=true)
	 */
	private $name;

	/**
	 * @ORM\Column(type="string", length=100, nullable=true)
	 */
	private $displayName;

	/**
	 * @ORM\OneToOne(targetEntity="App\Entity\File", cascade={"persist", "remove"})
	 */
	private $avatar;

	/**
	 * @ORM\Column(type="string", length=10, nullable=true)
	 */
	private $zipcode;

	/**
	 * @ORM\Column(type="string", length=100, nullable=true)
	 */
	private $city;

	/**
	 * @ORM\Column(type="string", length=100, nullable=true)
	 */
	private $country;

	/**
	 * @ORM\Column(type="text", length=64, nullable=true)
	 */
	private $presentation;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	private $bio;

	/**
	 * @ORM\Column(type="string", length=100, nullable=true)
	 */
	private $profileVisibility;

	/**
	 * @ORM\Column(type="string", length=20, nullable=true)
	 */
	private $inscriptionType;

	/**
	 * @ORM\Column(type="string", length=100, nullable=true)
	 */
	private $site;

	/**
	 * @ORM\ManyToMany(targetEntity="App\Entity\Skill")
	 * @ORM\JoinTable(name="users_skills")
	 */
	private $skills;

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
		$this->skills               = new ArrayCollection();
	}

	public function getId (): ?int {
		return $this->id;
	}

	/**
	 * A visual identifier that represents this user.
	 *
	 * @see UserInterface
	 */
	public function getUsername (): string {
		return (string) $this->getDisplayName();
	}

	/****************************************
	 * FIELDS
	 ****************************************/

	public function getDisplayName (): ?string {
		if ( !empty( $this->displayName ) ) {
			return $this->displayName;
		}

		if ( !empty( $this->getName() ) ) {
			return $this->getName();
		}

		return mb_convert_case( explode( '@', $this->getEmail() )[ 0 ], MB_CASE_TITLE );
	}

	public function setDisplayName ( ?string $displayName ): self {
		$this->displayName = trim( $displayName );

		return $this;
	}

	public function getName (): ?string {
		return $this->name;
	}

	public function setName ( string $name ): self {
		$this->name = mb_convert_case( trim( $name ), MB_CASE_TITLE );

		return $this;
	}

	public function getEmail (): ?string {
		return $this->email;
	}

	public function setEmail ( string $email ): self {
		$this->email = mb_convert_case( trim( $email ), MB_CASE_LOWER );

		return $this;
	}

	public function isAdmin (): ?bool {
		return in_array( User::ROLE_ADMIN, $this->getRoles() );
	}

	/**
	 * @see UserInterface
	 */
	public function getRoles (): array {
		$roles = $this->roles;
		// guarantee every user at least has ROLE_USER
		$roles[] = User::ROLE_USER;

		return array_unique( $roles );
	}

	public function setRoles ( array $roles ): self {
		$this->roles = array_unique( $roles );

		return $this;
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

	public function getPresentation (): ?string {
		return $this->presentation;
	}

	public function setPresentation ( ?string $presentation ): self {
		$this->presentation = trim( $presentation );

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

	public function getCreatedAt (): ?DateTimeInterface {
		return $this->createdAt;
	}

	public function setCreatedAt ( DateTimeInterface $createdAt ): self {
		$this->createdAt = $createdAt;

		return $this;
	}

	public function getSeenAt (): ?DateTimeInterface {
		return $this->seenAt;
	}

	public function setSeenAt ( ?DateTimeInterface $seenAt ): self {
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

	public function getStatus (): ?int {
		return $this->status;
	}

	public function setStatus ( int $status ): self {
		$this->status = $status;

		return $this;
	}

	public function getCity (): ?string {
		return $this->city;
	}

	public function setCity ( ?string $city ): self {
		$this->city = mb_convert_case( trim( $city ), MB_CASE_TITLE );

		return $this;
	}

	public function getZipcode (): ?string {
		return $this->zipcode;
	}

	public function setZipcode ( ?string $zipcode ): self {
		$this->zipcode = trim( $zipcode );

		return $this;
	}

	public function getCountry (): ?string {
		return $this->country;
	}

	public function setCountry ( ?string $country ): self {
		$this->country = mb_convert_case( trim( $country ), MB_CASE_UPPER );

		return $this;
	}

	public function getBio (): ?string {
		return $this->bio;
	}

	public function setBio ( ?string $bio ): self {
		$this->bio = trim( $bio );

		return $this;
	}

	public function getInscriptionType (): ?string {
		return $this->inscriptionType;
	}

	public function setInscriptionType ( ?string $inscriptionType ): self {
		$this->inscriptionType = $inscriptionType;

		return $this;
	}

	public function getSite (): ?string {
		return $this->site;
	}

	public function setSite ( ?string $site ): self {
		$this->site = trim( $site );

		return $this;
	}

	/**
	 * @return Collection|Skill[]
	 */
	public function getSkills (): Collection {
		return $this->skills;
	}

	public function addSkill ( Skill $skill ): self {
		if ( !$this->skills->contains( $skill ) ) {
			$this->skills[] = $skill;
		}

		return $this;
	}

	public function removeSkill ( Skill $skill ): self {
		if ( $this->skills->contains( $skill ) ) {
			$this->skills->removeElement( $skill );
		}

		return $this;
	}

	public function getAvatar (): ?File {
		return $this->avatar;
	}

	public function setAvatar ( ?File $avatar ): self {
		$this->avatar = $avatar;

		return $this;
	}
}
