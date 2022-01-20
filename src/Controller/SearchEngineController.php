<?php

namespace App\Controller;

// Import TNTSearch
use TeamTNT\TNTSearch\TNTSearch;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Usergroup;
use App\Entity\DiscussionMessage;
use App\Entity\Article;
use App\Entity\Page;
use App\Entity\User;
use App\Entity\Document;

use App\Service\SearchEngineManager;
use Symfony\Component\HttpFoundation\Request;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SearchEngineController extends AbstractController
{

	/**
	 * @Route(
	 * "/search/{searchQuery}",
	 * name="search_page",
	 * defaults={"searchQuery" = ""},
	 * requirements={"searchQuery"=".+"}
	 * )
	 *
 	 * @param \Symfony\Component\HttpFoundation\Request $request
   	 * @param \App\Service\SearchEngineManager      $searchEngineManager
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function searchPage (
		Request $request,
		SearchEngineManager $searchEngineManager,
		string $searchQuery
	) {
		$form = $request->query->get( 'form', [] );
		$headbarSearchQuery = $request->request->get('searchQuery');
		$groupQuery = $request->request->get('groupQuery');

		// TODO: Add Pagination to results
		$page     = $request->query->get( 'page', 0 );
		$per_page = 20;

		$formObj = $searchEngineManager->getForm(
			$form,
			$headbarSearchQuery,
			$groupQuery,
			[ 'page' => $page, 'per_page' => $per_page ]
		);

		$formObj['form']->handleRequest( $request );

		//Setting Tntsearch option
		$searchEngineManager->setTNTSearchConfiguration();

		//Launch Search
		$results = $searchEngineManager->search(implode($formObj['formTexts']['keywords'], ' '), $formObj['formFilters']['result_type'], $formObj['formFilters']['groups'], $formObj['formFilters']['particularGroups']);

		return $this->render( 'pages/search/search.html.twig', [
			'form'    => $formObj['form']->createView(),
			'result_number' => array_sum(array_map("count", $results)),
			'results' => $results,
		] );
	}

}
