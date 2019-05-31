<?php
/**
 * User: Maxime Cousinou
 * Date: 2019-03-29
 * Time: 14:05
 */

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FrontControllerTest extends WebTestCase {
	/**
	 * Test Home page
	 */
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
	}
}
