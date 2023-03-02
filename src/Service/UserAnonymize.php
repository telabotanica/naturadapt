<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserAnonymize {

	private $manager;

	private $translator;

	private $fileManager;

	public function __construct (
		EntityManagerInterface $manager,
		TranslatorInterface $translator,
		FileManager $fileManager
	) {
		$this->manager = $manager;
		$this->translator = $translator;
		$this->fileManager = $fileManager;
	}

	public function anonymize ( User $user ) {
		$avatar = $user->getAvatar();
		if($avatar) {
			$this->fileManager->deleteFile($avatar);
			$this->manager->remove($avatar);
		}
		$user->setAvatar(null);

		$userId = $user->getId();
		$user->setEmail(
			sprintf('deleted-%d@nospollinisateurs.fr',$userId)
		);
		$user->setName(
			$this->translator->trans('database_data.user.user_deleted_name', [
				'%1$d' => $userId,
			] )
		);
		$user->setDisplayName(
			$this->translator->trans( 'database_data.user.user_deleted_name', [
				'%1$d' => $userId,
			] )
		);

		$userGroupMemberships = $user->getUsergroupMemberships();
		foreach ($userGroupMemberships as $userGroupMembership) {
			$user->removeUsergroupMembership($userGroupMembership);
		}

		$user->setStatus(User::STATUS_DISABLED);
		$user->setZipCode(null);
		$user->setCity(null);
		$user->setCountry(null);
		$user->setPresentation(null);
		$user->setBio(null);
		$user->setProfileVisibility(null);
		$user->setInscriptionType(null);
		$user->setFavoriteEnvironment(null);
		$user->setLocale(null);
		$user->setTimezone(null);
		$user->setSeenAt(null);
		$user->setResetToken(null);
		$user->setLatitude(null);
		$user->setLongitude(null);
		$user->setSite(null);
		$user->setEmailNew(null);
		$user->setEmailToken(null);
	}
}
