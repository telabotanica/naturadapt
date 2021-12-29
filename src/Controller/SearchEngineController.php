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

        // TODO: Add Pagination to results
		$page     = $request->query->get( 'page', 0 );
		$per_page = 20;

        $formObj = $searchEngineManager->getForm(
            $form,
			$headbarSearchQuery,
            [ 'page' => $page, 'per_page' => $per_page ]
        );

        $formObj['form']->handleRequest( $request );

		//Setting Tntsearch option
		$searchEngineManager->setTNTSearchConfiguration();

		//Launch Search
        $em = $this->getDoctrine()->getManager();
		$results = $searchEngineManager->search($em, implode($formObj['formTexts']["keywords"], ' '), $formObj['formFilters']["result_type"]);

		return $this->render( 'pages/search/search.html.twig', [
            'form'    => $formObj['form']->createView(),
			'result_number' => array_sum(array_map("count", $results)),
			'results' => $results,
		] );
	}


   /**
     * @Route("/generate-index", name="app_generate-index")
	 *
	 * Call this function to generate all indexes with route
     */
    public function generate_index()
	{

        $tnt = new TNTSearch;

        // Obtain and load the configuration that can be generated with the previous described method
        $tnt->loadConfig($searchEngineManager->getTNTSearchConfiguration());

		$indexer = $tnt->createIndex('pages.index');
		$indexer->query('SELECT id, title, body FROM naturadapt_pages;');
		$indexer->run();

		$indexer = $tnt->createIndex('discussions_messages.index');
		$indexer->query('SELECT id, body FROM naturadapt_discussion_message;');
		$indexer->run();

		$indexer = $tnt->createIndex('articles.index');
		$indexer->query('SELECT id, title, body FROM naturadapt_articles;');
		$indexer->run();

		$indexer = $tnt->createIndex('documents.index');
		$indexer->query('SELECT id, title FROM naturadapt_document;');
		$indexer->run();

        $indexer = $tnt->createIndex('groups.index');
		$indexer->query('SELECT id, name, description, presentation FROM naturadapt_usergroups;');
		$indexer->run();

		$indexer = $tnt->createIndex('members.index');
		$indexer->query('SELECT id, name, presentation, bio FROM naturadapt_users;');
		$indexer->run();

        return new Response(
            '<html><body>All Indexes succesfully generated !</body></html>'
        );
    }


}
