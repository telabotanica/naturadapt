<?php
/**
 * User: Maxime Cousinou
 * Date: 2019-03-29
 * Time: 14:05
 */

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GroupControllerTest extends WebTestCase {
	/**
	 * Test Groups list page
	 */
	public function testGroupsIndexIsValid () {
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
				'Assert groups index contains groups list with groups'
		);

		$publicLink = $crawler
				->filter( '.group__public .group-name a' )
				->eq( 0 )
				->link()
				->getUri();

		$this->assertNotEmpty(
				$publicLink,
				'Assert groups index contains one public group'
		);

		$privateLink = $crawler
				->filter( '.group__private .group-name a' )
				->eq( 0 )
				->link()
				->getUri();

		$this->assertNotEmpty(
				$privateLink,
				'Assert groups index contains one private group'
		);

		return [ 'public' => $publicLink, 'private' => $privateLink ];
	}

	/**
	 * @depends testGroupsIndexIsValid
	 */
	public function testPublicGroupIndexIsValid ( $urls ) {
		$client = static::createClient();

		$crawler = $client->request( 'GET', $urls[ 'public' ] );

		$this->assertEquals(
				200,
				$client->getResponse()->getStatusCode(),
				'Assert public group index is StatusCode 200'
		);

		$pageUrl = $crawler
				->filter( '.group-app__pages a.page__in-items-list' )
				->eq( 0 )
				->link()
				->getUri();

		return [ 'page' => $pageUrl ];
	}

	/**
	 * @depends testPublicGroupIndexIsValid
	 */
	public function testPublicGroupPagePageIsValid ( $urls ) {
		$client = static::createClient();

		$crawler = $client->request( 'GET', $urls[ 'page' ] );

		$this->assertEquals(
				200,
				$client->getResponse()->getStatusCode(),
				'Assert page is StatusCode 200'
		);

		$this->assertGreaterThan(
				0,
				$crawler->filter( '.page-body' )->count(),
				'Assert page contains a body'
		);
	}

	/**
	 * @depends testGroupsIndexIsValid
	 */
	public function testPrivateGroupIndexIsValidAndRestricted ( $urls ) {
		$client = static::createClient();

		$crawler = $client->request( 'GET', $urls[ 'private' ] );

		$this->assertEquals(
				200,
				$client->getResponse()->getStatusCode(),
				'Assert private group index is StatusCode 200'
		);

		$this->assertEmpty(
				$crawler->filter( '.group-app__members' )->count(),
				'Assert private group does not show members list'
		);
	}
}
