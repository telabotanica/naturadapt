<?php

namespace App\Security;

use App\Entity\Discussion;
use App\Entity\User;
use App\Entity\Usergroup;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class GroupDiscussionVoter extends Voter {
	const CREATE      = 'group:discussion:create';
	const READ        = 'group:discussion:read';
	const PARTICIPATE = 'group:discussion:participate';
	const EDIT        = 'group:discussion:edit';
	const DELETE      = 'group:discussion:delete';

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

		if ( in_array( $attribute, [ self::READ, self::EDIT, self::DELETE ] ) && ( $subject instanceof Discussion ) ) {
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
			case self::PARTICIPATE:
				/**
				 * @var \App\Entity\Usergroup $group
				 */
				$group = $subject;

				return $this->security->isGranted( GroupVoter::PARTICIPATE, $group );

			case self::READ:
				/**
				 * @var \App\Entity\Discussion $discussion
				 */
				$discussion = $subject;

				return $this->security->isGranted( GroupVoter::READ, $discussion->getUsergroup() );

			case self::EDIT:
				/**
				 * @var \App\Entity\Discussion $discussion
				 */
				$discussion = $subject;

				return $this->security->isGranted( GroupVoter::EDIT, $discussion->getUsergroup() );

			case self::DELETE:
				/**
				 * @var \App\Entity\Discussion $discussion
				 */
				$discussion = $subject;

				return $this->security->isGranted( GroupVoter::DELETE, $discussion->getUsergroup() );
		}

		throw new \LogicException( 'This code should not be reached!' );
	}
}
