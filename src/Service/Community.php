<?php

namespace App\Service;

use App\Entity\Usergroup;
use Doctrine\ORM\EntityManagerInterface;

class Community {
	/**
	 * @var \App\Entity\Usergroup|bool|object|null
	 */
	private $group = FALSE;

	/**
	 * Community constructor.
	 *
	 * @param                                            $slug
	 * @param \Doctrine\ORM\EntityManagerInterface       $manager
	 */
	public function __construct (
			$slug,
            EntityManagerInterface $manager
	) {
		if ( !empty( $slug ) ) {
			$this->group = $manager->getRepository( Usergroup::class )
								   ->findOneBy( [ 'slug' => $slug ] );
		}
	}

	public function getGroup () {
		return $this->group;
	}
}
