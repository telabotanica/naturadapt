<?php
/**
 * User: Maxime Cousinou
 * Date: 2019-04-19
 * Time: 11:36
 */

namespace App\Controller;

use App\Entity\User;
use App\Service\FileManager;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class MemberController extends AbstractController {
	/**************************************************
	 * MEMBER
	 **************************************************/

	/**
	 * @Route("/members/{user_id}/avatar", name="member_avatar")
	 *
	 * @param                                            $user_id
	 * @param \Doctrine\Common\Persistence\ObjectManager $manager
	 * @param \App\Service\FileManager                   $fileManager
	 *
	 * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Symfony\Component\HttpFoundation\Response
	 */
	public function memberAvatar (
			$user_id,
			ObjectManager $manager,
			FileManager $fileManager
	) {
		$user = $manager->getRepository( User::class )
						->findOneById( $user_id );

		if ( !empty( $user ) ) {
			$file = $user->getAvatar();

			if ( !empty( $file ) ) {
				return $fileManager->getFile( $file );
			}
		}

		$response = new Response(
				'<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100"><circle r="50" cx="50" cy="50" fill="#333"></circle></svg>',
				Response::HTTP_OK,
				[ 'content-type' => 'image/svg+xml' ]
		);

		return $response;
	}
}
