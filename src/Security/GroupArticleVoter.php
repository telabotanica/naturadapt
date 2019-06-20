<?php

namespace App\Security;

use App\Entity\Article;
use App\Entity\User;
use App\Entity\Usergroup;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class GroupArticleVoter extends Voter {
	const CREATE = 'group:article:create';
	const READ   = 'group:article:read';
	const EDIT   = 'group:article:edit';
	const DELETE = 'group:article:delete';

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

		if ( in_array( $attribute, [ self::READ, self::EDIT, self::DELETE ] ) && ( $subject instanceof Article ) ) {
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
				 * @var \App\Entity\Article $article
				 */
				$article = $subject;

				return $this->security->isGranted( GroupVoter::READ, $article->getUsergroup() );

			case self::EDIT:
				/**
				 * @var \App\Entity\Article $article
				 */
				$article = $subject;

				if ( $user === $article->getAuthor() ) {
					return $this->security->isGranted( GroupVoter::PARTICIPATE, $article->getUsergroup() );
				}

				return $this->security->isGranted( GroupVoter::ADMIN, $article->getUsergroup() );

			case self::DELETE:
				/**
				 * @var \App\Entity\Article $article
				 */
				$article = $subject;

				if ( $user === $article->getAuthor() ) {
					return $this->security->isGranted( GroupVoter::PARTICIPATE, $article->getUsergroup() );
				}

				return $this->security->isGranted( GroupVoter::DELETE, $article->getUsergroup() );
		}

		throw new \LogicException( 'This code should not be reached!' );
	}
}
