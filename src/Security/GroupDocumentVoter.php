<?php

namespace App\Security;

use App\Entity\Document;
use App\Entity\User;
use App\Entity\Usergroup;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class GroupDocumentVoter extends Voter {
	const CREATE = 'group:document:create';
	const READ   = 'group:document:read';
	const EDIT   = 'group:document:edit';
	const DELETE = 'group:document:delete';

	/**
	 * @var \Doctrine\ORM\EntityManagerInterface
	 */
	private $manager;

	/**
	 * @var \Symfony\Component\Security\Core\Security
	 */
	private $security;

	public function __construct ( EntityManagerInterface $manager, Security $security ) {
		$this->manager  = $manager;
		$this->security = $security;
	}

	protected function supports ( $attribute, $subject ) {
		if ( in_array( $attribute, [ self::CREATE ] ) && ( $subject instanceof Usergroup ) ) {
			return TRUE;
		}

		if ( in_array( $attribute, [ self::READ, self::EDIT, self::DELETE ] ) && ( $subject instanceof Document ) ) {
			return TRUE;
		}

		return FALSE;
	}

	protected function voteOnAttribute ( $attribute, $subject, TokenInterface $token ) {
		$user = $token->getUser();

		if ( ( $user instanceof User ) && $user->isAdmin() ) {
			return TRUE;
		}

		switch ( $attribute ) {
			case self::CREATE:
				/**
				 * @var \App\Entity\Usergroup $group
				 */
				$group = $subject;

				return $this->security->isGranted( GroupVoter::PARTICIPATE, $group );

			case self::READ:
				/**
				 * @var \App\Entity\Document $document
				 */
				$document = $subject;

				return $this->security->isGranted( GroupVoter::READ, $document->getUsergroup() );

			case self::EDIT:
				/**
				 * @var \App\Entity\Document $document
				 */
				$document = $subject;

				return $this->security->isGranted( GroupVoter::PARTICIPATE, $document->getUsergroup() );

			case self::DELETE:
				/**
				 * @var \App\Entity\Document $document
				 */
				$document = $subject;

				if ( $user === $document->getUser() ) {
					return $this->security->isGranted( GroupVoter::PARTICIPATE, $document->getUsergroup() );
				}

				return $this->security->isGranted( GroupVoter::DELETE, $document->getUsergroup() );
		}

		throw new \LogicException( 'This code should not be reached!' );
	}
}
