<?php

namespace App\Twig;

use App\Entity\User;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ProfileLinkExtension extends AbstractExtension {

	private const DEFAULT_PROFILE_LINK_CLASS = 'user--name';
	private const DISABLED_LINK_CLASS = 'disabled';

	public function getFunctions () {
		return [
				new TwigFunction( 'profileLinkClasses', [ $this, 'getProfileLinkClasses' ] ),
		];
	}

	public function getProfileLinkClasses (
			string $status,
			array $defaultProfileLinkClasses = null
	) :string {
		$classes = $defaultProfileLinkClasses ?? [ self::DEFAULT_PROFILE_LINK_CLASS ];
		if ( User::STATUS_ACTIVE !== $status ) {
			$classes[] = self::DISABLED_LINK_CLASS;
		}

		return implode ( ' ', $classes );
	}
}
