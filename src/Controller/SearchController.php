<?php

namespace App\Controller;

use App\Entity\Site;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController {
	/**
	 * @Route("/search/site/{query}", name="search_site")
	 *
	 * @param                                            $query
	 * @param \Doctrine\ORM\EntityManagerInterface       $manager
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function searchSite (
			$query,
            EntityManagerInterface $manager
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
