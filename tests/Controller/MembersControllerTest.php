<?php
/**
 * User: Maxime Cousinou
 * Date: 2019-03-29
 * Time: 14:05.
 */

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MembersControllerTest extends WebTestCase
{
    /**
     * Test global members list.
     */
    public function testMembersPageIsValid()
    {
        self::ensureKernelShutdown();

        $client = static::createClient();

        $crawler = $client->request('GET', '/members');

        $this->assertEquals(
                302,
                $client->getResponse()->getStatusCode(),
                'Assert members page is StatusCode restricted to logged users'
        );

        //		$this->assertGreaterThan(
//				0,
//				$crawler->filter( '.items-list .user' )->count(),
//				'Assert members page contains members list with users'
//		);
    }

    //	/**
//	 * Test single User page
//	 */
//	public function testMemberPageIsValid () {
//		$client = static::createClient();
//
//		$crawler = $client->request( 'GET', '/members' );
//
//		$link = $crawler
//				->filter( '.items-list a.user' )
//				->eq( 0 )
//				->link();
//
//		$crawler = $client->click( $link );
//
//		$this->assertEquals(
//				200,
//				$client->getResponse()->getStatusCode(),
//				'Assert member page is StatusCode 200'
//		);
//	}
}
