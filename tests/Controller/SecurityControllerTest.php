<?php
/**
 * User: Maxime Cousinou
 * Date: 2019-03-29
 * Time: 14:05
 */

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase {
	public function testLogin () {
		$client = static::createClient();

		$crawler = $client->request( 'GET', '/user/login' );

		$this->assertEquals(
				200,
				$client->getResponse()->getStatusCode(),
				'Assert user login page is StatusCode 200'
		);
	}
}
