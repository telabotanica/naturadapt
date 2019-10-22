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
	 * @ORM\OrderBy({"joinedAt"="DESC"})
	 */
	private $members;

	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\Page", mappedBy="usergroup", orphanRemoval=true)
	 * @ORM\OrderBy({"title"="ASC"})
	 */
	private $pages;

	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\Document", mappedBy="usergroup")
	 */
	private $documents;

	/**
	 * @ORM\OneToMany(targetEntity="Article", mappedBy="usergroup", orphanRemoval=true)
	 * @ORM\OrderBy({"createdAt"="DESC"})
	 */
	private $articles;

	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\File", mappedBy="usergroup")
	 */
	private $files;

	/**
	 * @ORM\Column(type="datetime")
	 */
	private $createdAt;

	/**
	 * @ORM\OneToOne(targetEntity="App\Entity\File", cascade={"persist", "remove"})
	 */
	private $logo;

	/**
	 * @ORM\OneToOne(targetEntity="App\Entity\File", cascade={"persist", "remove"})
	 */
	private $cover;

	/**
	 * @ORM\OneToMany(targetEntity="App\Entity\LogEvent", mappedBy="usergroup")
	 * @ORM\OrderBy({"createdAt"="DESC"})
	 */
	private $logEvents;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\DocumentFolder", mappedBy="usergroup", orphanRemoval=true)
     */
    private $documentFolders;

	public function __construct () {
               		$this->categories = new ArrayCollection();
               		$this->members    = new ArrayCollection();
               		$this->pages      = new ArrayCollection();
               		$this->files      = new ArrayCollection();
               		$this->documents  = new ArrayCollection();
               		$this->articles   = new ArrayCollection();
               		$this->logEvents  = new ArrayCollection();
                 $this->documentFolders = new ArrayCollection();
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

	public function setPresentation ( ?string $presentation ): self {
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
	 * @param string $status
	 *
	 * @return Collection|UsergroupMembership[]
	 */
	public function getMembers ( $status = UsergroupMembership::STATUS_MEMBER ): Collection {
               		return $this->members->filter( function ( UsergroupMembership $membership ) use ( $status ) {
               			return $membership->getStatus() === $status;
               		} );
               	}

	/**
	 * @param string $role
	 *
	 * @return Collection|UsergroupMembership[]
	 */
	public function getMembersByRole ( $role = UsergroupMembership::ROLE_USER ): Collection {
               		return $this->members->filter( function ( UsergroupMembership $membership ) use ( $role ) {
               			return $membership->getRole() === $role;
               		} );
               	}

	public function addMember ( UsergroupMembership $member ): self {
               		if ( !$this->members->contains( $member ) ) {
               			$this->members[] = $member;
               			$member->setUsergroup( $this );
               		}
               
               		return $this;
               	}

	public function removeMember ( UsergroupMembership $member ): self {
               		if ( $this->members->contains( $member ) ) {
               			$this->members->removeElement( $member );
               			// set the owning side to null (unless already changed)
               			if ( $member->getUsergroup() === $this ) {
               				$member->setUsergroup( NULL );
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

	public function getLogo (): ?File {
               		return $this->logo;
               	}

	public function setLogo ( ?File $logo ): self {
               		$this->logo = $logo;
               
               		return $this;
               	}

	public function getCover (): ?File {
               		return $this->cover;
               	}

	public function setCover ( ?File $cover ): self {
               		$this->cover = $cover;
               
               		return $this;
               	}

	/**
	 * @return Collection|Document[]
	 */
	public function getDocuments (): Collection {
               		return $this->documents;
               	}

	public function addDocument ( Document $document ): self {
               		if ( !$this->documents->contains( $document ) ) {
               			$this->documents[] = $document;
               			$document->setUsergroup( $this );
               		}
               
               		return $this;
               	}

	public function removeDocument ( Document $document ): self {
               		if ( $this->documents->contains( $document ) ) {
               			$this->documents->removeElement( $document );
               			// set the owning side to null (unless already changed)
               			if ( $document->getUsergroup() === $this ) {
               				$document->setUsergroup( NULL );
               			}
               		}
               
               		return $this;
               	}

	/**
	 * @return Collection|Article[]
	 */
	public function getArticles (): Collection {
               		return $this->articles;
               	}

	public function addArticle ( Article $article ): self {
               		if ( !$this->articles->contains( $article ) ) {
               			$this->articles[] = $article;
               			$article->setUsergroup( $this );
               		}
               
               		return $this;
               	}

	public function removeArticle ( Article $article ): self {
               		if ( $this->articles->contains( $article ) ) {
               			$this->articles->removeElement( $article );
               			// set the owning side to null (unless already changed)
               			if ( $article->getUsergroup() === $this ) {
               				$article->setUsergroup( NULL );
               			}
               		}
               
               		return $this;
               	}

	/**
	 * @return Collection|LogEvent[]
	 */
	public function getLogEvents (): Collection {
               		return $this->logEvents;
               	}

	public function addLogEvent ( LogEvent $logEvent ): self {
               		if ( !$this->logEvents->contains( $logEvent ) ) {
               			$this->logEvents[] = $logEvent;
               			$logEvent->setUsergroup( $this );
               		}
               
               		return $this;
               	}

	public function removeLogEvent ( LogEvent $logEvent ): self {
               		if ( $this->logEvents->contains( $logEvent ) ) {
               			$this->logEvents->removeElement( $logEvent );
               			// set the owning side to null (unless already changed)
               			if ( $logEvent->getUsergroup() === $this ) {
               				$logEvent->setUsergroup( NULL );
               			}
               		}
               
               		return $this;
               	}

    /**
     * @return Collection|DocumentFolder[]
     */
    public function getDocumentFolders(): Collection
    {
        return $this->documentFolders;
    }

    public function addDocumentFolder(DocumentFolder $documentFolder): self
    {
        if (!$this->documentFolders->contains($documentFolder)) {
            $this->documentFolders[] = $documentFolder;
            $documentFolder->setUsergroup($this);
        }

        return $this;
    }

    public function removeDocumentFolder(DocumentFolder $documentFolder): self
    {
        if ($this->documentFolders->contains($documentFolder)) {
            $this->documentFolders->removeElement($documentFolder);
            // set the owning side to null (unless already changed)
            if ($documentFolder->getUsergroup() === $this) {
                $documentFolder->setUsergroup(null);
            }
        }

        return $this;
    }
}
