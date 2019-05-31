<?php

namespace App\Controller;

use App\Entity\Site;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController {
	/**
	 * @Route("/search/site/{query}", name="search_site")
	 *
	 * @param \Symfony\Component\HttpFoundation\Request  $request
	 * @param \Doctrine\Common\Persistence\ObjectManager $manager
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function searchSite (
			$query,
			Request $request,
			ObjectManager $manager
	) {
		$query = trim( $query );

		/**
		 * @var \App\Repository\SiteRepository $siteRepository
		 */
		$siteRepository = $manager->getRepository( Site::class );

		$sites   = $siteRepository->search( $query );
		$results = [ 'query' => $query, 'results' => [] ];
		/**
		 * @var Site $site
		 */
		foreach ( $sites as $site ) {
			$results[ 'results' ][] = [
					'id'   => $site->getId(),
					'name' => $site->getName(),
			];
		}

		return new JsonResponse( $results );
	}
}
