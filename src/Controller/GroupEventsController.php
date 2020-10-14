<?php

namespace App\Controller;

use App\Entity\Usergroup;
use App\Security\GroupVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class GroupEventsController extends AbstractController {
	/**************************************************
	 * EVENTS
	 **************************************************/

	/**
	 * @Route("/groups/{groupSlug}/events", name="group_events_index")
	 * @param                                            $groupSlug
	 * @param \Doctrine\ORM\EntityManagerInterface       $manager
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function eventsIndex (
			$groupSlug,
            EntityManagerInterface $manager
	) {
		/**
		 * @var $group \App\Entity\Usergroup
		 */
		$group = $manager->getRepository( Usergroup::class )
						 ->findOneBy( [ 'slug' => $groupSlug ] );

		if ( !$group ) {
			throw $this->createNotFoundException( 'The group does not exist' );
		}

		$this->denyAccessUnlessGranted( GroupVoter::READ, $group );

		$logEvents = $group->getLogEvents();

		return $this->render( 'pages/events/events-index.html.twig', [
				'group'     => $group,
				'logEvents' => $logEvents,
		] );
	}
}
