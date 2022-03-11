<?php

namespace App\Controller;

// Import TNTSearch
use TeamTNT\TNTSearch\TNTSearch;

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
	public function searchPage(
		Request $request,
		SearchEngineManager $searchEngineManager
	) {
		$form = $request->query->get('form', []);
		$headbarSearchQuery = $request->request->get('searchQuery');
		$groupQuery = $request->request->get('groupQuery');

		// TODO: Add Pagination to results
		$page     = $request->query->get('page', 0);

		$formObj = $searchEngineManager->getForm(
			$form,
			$headbarSearchQuery,
			$groupQuery
		);

		$formObj['form']->handleRequest($request);

		//Setting Tntsearch option
		$searchEngineManager->setTNTSearchConfiguration();

		$categories = $formObj['formFilters']['result_type'];
		if (count($categories) > 3) {
			$per_index_per_page = 5;
		} else {
			$per_index_per_page = 10;
		}

		//Launch Search
		$data = $searchEngineManager->search(implode(' ', $formObj['formTexts']['keywords']), $categories, $formObj['formFilters']['groups'], $formObj['formFilters']['particularGroups'], ['page' => $page, 'per_index_per_page' => $per_index_per_page]);

		return $this->render('pages/search/search.html.twig', [
			'form'    => $formObj['form']->createView(),
			'result_number' => $data['total'],
			'results' => $data["results"],
			'pager'   => [
				'base_url' => $request->getPathInfo() . '?' . http_build_query(['form' => $form]) . '&',
				'page'     => $page,
				'last'     => ceil($data['maxCountPerCategory'] / $per_index_per_page) - 1,
			],
			'isCurrentConnexion' => $data['connexionBoolean'],
		]);
	}
}
