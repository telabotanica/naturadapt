<?php
/**
 * User: Maxime Cousinou
 * Date: 2019-03-29
 * Time: 14:05
 */

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase {
	public function testUserHasAnEmailField () {
		$user = new User();

		$user->setEmail( 'test@test.com' );

		$this->assertEquals(
				'test@test.com',
				$user->getEmail(),
				'Assert User has email'
		);
	}

	public function testUserHasANameField () {
		$user = new User();

		$user->setName( 'User Name' );

		$this->assertEquals(
				'User Name',
				$user->getName(),
				'Assert User has name'
		);
	}

	public function testUserHasADisplayNameField () {
		$user = new User();

		$user->setDisplayName( 'Batman' );

		$this->assertEquals(
				'Batman',
				$user->getDisplayName(),
				'Assert User has displayname'
		);
	}

	public function testUserDisplayNameDefaultsToName () {
		$user = new User();

		$user->setName( 'User Name' );

		$this->assertEquals(
				'User Name',
				$user->getDisplayName(),
				'Assert User displayname default to name'
		);
	}
}
