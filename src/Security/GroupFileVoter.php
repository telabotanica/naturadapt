<?php

namespace App\Security;

use App\Entity\File;
use App\Entity\User;
use App\Entity\Usergroup;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class GroupFileVoter extends Voter {
	const CREATE = 'group:file:create';
	const READ   = 'group:file:read';
	const DELETE = 'group:file:delete';

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

		if ( in_array( $attribute, [ self::READ, self::DELETE ] ) && ( $subject instanceof File ) ) {
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
				 * @var \App\Entity\File $file
				 */
				$file = $subject;

				if ( !empty( $file->getUsergroup() ) ) {
					return $this->security->isGranted( GroupVoter::READ, $file->getUsergroup() );
				}
				else {
					return TRUE;
				}

			case self::DELETE:
				/**
				 * @var \App\Entity\Page $page
				 */
				$file = $subject;

				return $this->security->isGranted( GroupVoter::ADMIN, $file->getUsergroup() );
		}

		throw new \LogicException( 'This code should not be reached!' );
	}
}
