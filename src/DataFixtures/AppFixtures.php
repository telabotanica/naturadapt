<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Group;
use App\Entity\GroupMembership;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture {
	private $passwordEncoder;

	public function __construct ( UserPasswordEncoderInterface $passwordEncoder ) {
		$this->passwordEncoder = $passwordEncoder;
	}

	public function load ( ObjectManager $manager ) {
		$faker = Faker\Factory::create ( 'fr_FR' );

		/**
		 * USERS
		 */
		$users = [];
		for ( $i = 0; $i < 20; $i++ ) {
			$user = new User();
			$user->setCreatedAt ( new \DateTime() );
			$user->setName ( $faker->firstName () . ' ' . $faker->lastName () );
			$user->setEmail ( sprintf ( 'test-%d@test.com', $i ) );
			$user->setPassword ( $this->passwordEncoder->encodePassword (
					$user,
					'test'
			) );

			$manager->persist ( $user );
			$users[] = $user;
		}

		$manager->flush ();

		/**
		 * CATEGORIES
		 */
		$categories = [];
		for ( $i = 0; $i < 10; $i++ ) {
			$category = new Category();
			$category->setName ( $faker->sentence ( 2 ) );
			$category->setDescription ( $faker->sentence ( 30 ) );

			$manager->persist ( $category );
			$categories[] = $category;
		}

		$manager->flush ();

		/**
		 * GROUPS
		 */
		$groups = [];
		for ( $i = 0; $i < 20; $i++ ) {
			$group = new Group();
			$group->setName ( mb_convert_case ( $faker->word (), MB_CASE_TITLE ) );
			$group->setDescription ( $faker->sentence ( 30 ) );
			$group->setPresentation ( '<p>' . implode ( '</p><p>', $faker->paragraphs ( 10 ) ) . '</p>' );
			$group->setVisibility ( empty( rand ( 0, 1 ) ) ? 'private' : 'public' );

			for ( $j = 0, $n = rand ( 1, 3 ); $j < $n; $j++ ) {
				$group->addCategory ( $categories[ rand ( 0, count ( $categories ) - 1 ) ] );
			}

			for ( $j = 0, $n = rand ( 3, 20 ); $j < $n; $j++ ) {
				$membership = new GroupMembership();
				$membership->setGroup ( $group );
				$membership->setUser ( $users[ rand ( 0, count ( $users ) - 1 ) ] );
				$membership->setJoinedAt ( new \DateTime() );
				$membership->setRole ( '' );

				$manager->persist ( $membership );
			}

			$manager->persist ( $group );
			$groups[] = $group;
		}

		$manager->flush ();
	}
}
