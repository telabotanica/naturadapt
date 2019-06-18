<?php

namespace App\Security;

use App\Entity\Page;
use App\Entity\User;
use App\Entity\Usergroup;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class GroupPageVoter extends Voter {
	const CREATE = 'group:page:create';
	const READ   = 'group:page:read';
	const EDIT   = 'group:page:edit';
	const DELETE = 'group:page:delete';

	/**
	 * @var \Doctrine\Common\Persistence\ObjectManager
	 */
	private $manager;

	/**
	 * @var \Symfony\Component\Security\Core\Security
	 */
	private $security;

	public function __construct ( ObjectManager $manager, Security $security ) {
		$this->manager  = $manager;
		$this->security = $security;
	}

	protected function supports ( $attribute, $subject ) {
		if ( in_array( $attribute, [ self::CREATE ] ) && ( $subject instanceof Usergroup ) ) {
			return TRUE;
		}

		if ( in_array( $attribute, [ self::READ, self::EDIT, self::DELETE ] ) && ( $subject instanceof Page ) ) {
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
				 * @var \App\Entity\Page $page
				 */
				$page = $subject;

				return $this->security->isGranted( GroupVoter::READ, $page->getUsergroup() );

			case self::EDIT:
				/**
				 * @var \App\Entity\Page $page
				 */
				$page = $subject;

				if ( $page->getEditionRestricted() ) {
					if ( $user === $page->getAuthor() ) {
						return $this->security->isGranted( GroupVoter::PARTICIPATE, $page->getUsergroup() );
					}

					return $this->security->isGranted( GroupVoter::EDIT, $page->getUsergroup() );
				}

				return $this->security->isGranted( GroupVoter::PARTICIPATE, $page->getUsergroup() );

			case self::DELETE:
				/**
				 * @var \App\Entity\Page $page
				 */
				$page = $subject;

				if ( $user === $page->getAuthor() ) {
					return $this->security->isGranted( GroupVoter::PARTICIPATE, $page->getUsergroup() );
				}

				return $this->security->isGranted( GroupVoter::DELETE, $page->getUsergroup() );
		}

		throw new \LogicException( 'This code should not be reached!' );
	}
}
