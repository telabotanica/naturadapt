<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Contracts\Translation\TranslatorInterface;

class SoftDelete {

    private $translator;

	public function __construct (
            TranslatorInterface $translator
	) {
        $this->translator = $translator;
	}

	public function setUserDeleted (
	        User $user
    ) {
        $user->setAvatar(null);

        $userId = $user->getId();
        $user->setEmail(
            sprintf('deleted-%d@naturadapt.com',$userId)
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

        $user->setStatus(User::STATUS_DISABLED);
        $user->setZipCode(null);
        $user->setCity(null);
        $user->setCountry(null);
        $user->setPresentation(null);
        $user->setBio(null);
        $user->setProfileVisibility(null);
        $user->setInscriptionType(null);
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
