<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Page;
use App\Entity\User;
use App\Entity\Usergroup;
use App\Entity\UsergroupMembership;
use App\Service\SlugGenerator;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker;
use Symfony\Component\Intl\Intl;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture {
	private $passwordEncoder;
	private $slugGenerator;

	public function __construct ( UserPasswordEncoderInterface $passwordEncoder, SlugGenerator $slugGenerator ) {
		$this->passwordEncoder = $passwordEncoder;
		$this->slugGenerator   = $slugGenerator;
	}

	public function load ( ObjectManager $manager ) {
		$countries        = [ 'fr_FR', 'en_GB', 'es_ES', 'en_US', 'de_DE' ];
		$inscriptionTypes = [ User::TYPE_PRIVATE, User::TYPE_PROFESSIONNAL, NULL ];

		/**
		 * USERS
		 */
		$users = [];
		for ( $i = 0; $i < 100; $i++ ) {
			$country = $countries[ rand( 0, count( $countries ) - 1 ) ];
			$faker   = Faker\Factory::create( $country );

			$user = new User();
			$user->setCreatedAt( new \DateTime() );
			$name = $faker->firstName() . ' ' . $faker->lastName();
			$user->setName( $name );
			$user->setDisplayName( $name );

			$user->setEmail( sprintf( 'test-%d@test.com', $i ) );
			$user->setPassword( $this->passwordEncoder->encodePassword(
					$user,
					'test'
			) );
			$user->setRoles( [ User::ROLE_USER ] );
			$user->setStatus( User::STATUS_ACTIVE );

			$user->setCountry( substr( $country, 3, 2 ) );
			$user->setInscriptionType( $inscriptionTypes[ rand( 0, count( $inscriptionTypes ) - 1 ) ] );

			$manager->persist( $user );
			$manager->flush();

			$users[] = $user;
		}

		$faker = Faker\Factory::create( 'fr_FR' );

		/**
		 * CATEGORIES
		 */
		$categories = [];
		for ( $i = 0; $i < 10; $i++ ) {
			$category = new Category();
			$category->setName( $faker->sentence( 2 ) );
			$category->setDescription( $faker->sentence( 30 ) );

			$manager->persist( $category );
			$manager->flush();

			$categories[] = $category;
		}

		/**
		 * GROUPS
		 */
		$groups = [];
		for ( $i = 0; $i < 20; $i++ ) {
			$group = new Usergroup();
			$group->setName( mb_convert_case( implode( ' ', $faker->words( rand( 1, 3 ) ) ), MB_CASE_TITLE ) );

			$group->setSlug( $this->slugGenerator->generateSlug( $group->getName(), Usergroup::class, 'slug' ) );
			$group->setDescription( $faker->sentence( 30 ) );
			$group->setPresentation( '<p>' . implode( '</p><p>', $faker->paragraphs( 10 ) ) . '</p>' );
			$group->setVisibility( empty( rand( 0, 1 ) ) ? Usergroup::PRIVATE : Usergroup::PUBLIC );
			$group->setCreatedAt( new \DateTime() );

			$manager->persist( $group );
			$manager->flush();

			for ( $j = 0, $n = rand( 1, 3 ); $j < $n; $j++ ) {
				$group->addCategory( $categories[ rand( 0, count( $categories ) - 1 ) ] );
			}

			$groupUsers = [];

			for ( $j = 0, $n = rand( 3, 20 ); $j < $n; $j++ ) {
				$userId = rand( 0, count( $users ) - 1 );
				if ( !in_array( $userId, $groupUsers ) ) {
					$membership = new UsergroupMembership();
					$membership->setUsergroup( $group );
					$membership->setUser( $users[ $userId ] );
					$membership->setJoinedAt( new \DateTime() );
					$membership->setRole( '' );

					$manager->persist( $membership );

					$groupUsers[] = $userId;
				}
			}

			for ( $j = 0, $n = rand( 3, 10 ); $j < $n; $j++ ) {
				$page = new Page();
				$page->setTitle( $faker->sentence( rand( 3, 10 ) ) );
				$page->setSlug( $this->slugGenerator->generateSlug( $page->getTitle() ) );

				$page->setUsergroup( $group );
				$page->setAuthor( $users[ rand( 0, count( $users ) - 1 ) ] );
				$page->setBody( '<p>' . implode( '</p><p>', $faker->paragraphs( rand( 3, 10 ), FALSE ) ) . '</p>' );

				$page->setCreatedAt( new \DateTime() );

				$manager->persist( $page );
				$manager->flush();
			}

			$manager->persist( $group );
			$manager->flush();

			$groups[] = $group;
		}
	}
}
