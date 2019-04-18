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

		$user->setEmail( ' Test@Test.com ' );

		$this->assertEquals(
				'test@test.com',
				$user->getEmail(),
				'Assert User has normalized email'
		);
	}

	public function testUserHasANameField () {
		$user = new User();

		$user->setName( ' firstname lastname ' );

		$this->assertEquals(
				'Firstname Lastname',
				$user->getName(),
				'Assert User has normalized name'
		);
	}

	public function testUserHasADisplayNameField () {
		$user = new User();

		$user->setDisplayName( ' BatmaN ' );

		$this->assertEquals(
				'BatmaN',
				$user->getDisplayName(),
				'Assert User has trimmed displayname'
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

	public function testUserHasZipcode () {
		$user = new User();

		$user->setZipcode( ' 59300 ' );

		$this->assertEquals(
				'59300',
				$user->getZipcode(),
				'Assert User has trimmed zipcode'
		);
	}

	public function testUserHasCity () {
		$user = new User();

		$user->setCity( ' valenciennes ' );

		$this->assertEquals(
				'Valenciennes',
				$user->getCity(),
				'Assert User has normalized city'
		);
	}

	public function testUserHasCountry () {
		$user = new User();

		$user->setCountry( ' france ' );

		$this->assertEquals(
				'FRANCE',
				$user->getCountry(),
				'Assert User has normalized country'
		);
	}

	public function testUserHasPresentation () {
		$user = new User();

		$user->setPresentation( ' Web developer ' );

		$this->assertEquals(
				'Web developer',
				$user->getPresentation(),
				'Assert User has presentation'
		);
	}
}
