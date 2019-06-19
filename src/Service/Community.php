<?php

namespace App\Service;

use App\Entity\Usergroup;
use Doctrine\Common\Persistence\ObjectManager;

class Community {
	/**
	 * @var \App\Entity\Usergroup|bool|object|null
	 */
	private $group = FALSE;

	/**
	 * Community constructor.
	 *
	 * @param                                            $slug
	 * @param \Doctrine\Common\Persistence\ObjectManager $manager
	 */
	public function __construct (
			$slug,
			ObjectManager $manager
	) {
		if ( !empty( $slug ) ) {
			$this->group = $manager->getRepository( Usergroup::class )
								   ->findOneBy( [ 'slug' => $slug ] );
		}
	}

	public function getGroup () {
		return $this->group;
	}

	public function getName () {
		if ( $this->group ) {
			return $this->group->getName();
		}

		return 'NaturAdapt';
	}
}
