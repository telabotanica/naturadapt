<?php
/**
 * User: Maxime Cousinou
 * Date: 2019-03-29
 * Time: 14:05
 */

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AppControllerTest extends WebTestCase {
	public function testAnonymousFrontPageIsValid () {
		$client = static::createClient();

		$crawler = $client->request( 'GET', '/' );

		$this->assertEquals(
				200,
				$client->getResponse()->getStatusCode(),
				'Assert frontpage is StatusCode 200'
		);

		$this->assertGreaterThan(
				0,
				$crawler->filter( '.main-header--user a.connexion' )->count(),
				'Assert header contains a Connexion link'
		);

		$this->assertGreaterThan(
				0,
				$crawler->filter( '.groups-list .group__teaser' )->count(),
				'Assert frontpage contains groups list with groups'
		);
	}

	public function testGroupsPageIsValid () {
		$client = static::createClient();

		$crawler = $client->request( 'GET', '/groups' );

		$this->assertEquals(
				200,
				$client->getResponse()->getStatusCode(),
				'Assert groups page is StatusCode 200'
		);

		$this->assertGreaterThan(
				0,
				$crawler->filter( '.groups-list .group__teaser' )->count(),
				'Assert groups contains groups list with groups'
		);
	}

	public function testGroupPageIsValid () {
		$client = static::createClient();

		$crawler = $client->request( 'GET', '/groups' );

		$link = $crawler
				->filter( '.group__public .group-name a' )
				->eq( 0 )
				->link();

		$crawler = $client->click( $link );

		$this->assertEquals(
				200,
				$client->getResponse()->getStatusCode(),
				'Assert group is StatusCode 200'
		);
	}

	public function testGroupPagePageIsValid () {
		$client = static::createClient();

		$crawler = $client->request( 'GET', '/groups' );

		$link = $crawler
				->filter( '.group__public .group-name a' )
				->eq( 0 )
				->link();

		$crawler = $client->click( $link );

		$link = $crawler
				->filter( '.group-app__pages a.page__teaser' )
				->eq( 0 )
				->link();

		$crawler = $client->click( $link );

		$this->assertEquals(
				200,
				$client->getResponse()->getStatusCode(),
				'Assert group page is StatusCode 200'
		);
	}

	public function testMembersPageIsValid () {
		$client = static::createClient();

		$crawler = $client->request( 'GET', '/members' );

		$this->assertEquals(
				200,
				$client->getResponse()->getStatusCode(),
				'Assert members page is StatusCode 200'
		);

		$this->assertGreaterThan(
				0,
				$crawler->filter( '.items-list .user' )->count(),
				'Assert members page contains members list with users'
		);
	}

	public function testMemberPageIsValid () {
		$client = static::createClient();

		$crawler = $client->request( 'GET', '/members' );

		$link = $crawler
				->filter( '.items-list a.user' )
				->eq( 0 )
				->link();

		$crawler = $client->click( $link );

		$this->assertEquals(
				200,
				$client->getResponse()->getStatusCode(),
				'Assert member page is StatusCode 200'
		);
	}
}
