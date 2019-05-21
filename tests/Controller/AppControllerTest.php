<?php
/**
 * User: Maxime Cousinou
 * Date: 2019-03-29
 * Time: 14:05
 */

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AppControllerTest extends WebTestCase {
	public function testFrontPageIsValid () {
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
				$crawler->filter( '.members-list .user' )->count(),
				'Assert members page contains members list with users'
		);
	}

	public function testMemberPageIsValid () {
		$client = static::createClient();

		$crawler = $client->request( 'GET', '/members' );

		$link = $crawler
				->filter( '.members-list a.user' )// find all links with the text "Greet"
				->eq( 1 )// select the second link in the list
				->link();

		$crawler = $client->click( $link );

		$this->assertEquals(
				200,
				$client->getResponse()->getStatusCode(),
				'Assert member page is StatusCode 200'
		);
	}
}
