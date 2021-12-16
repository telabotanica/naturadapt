<?php

namespace App\Controller;

// Import TNTSearch
use TeamTNT\TNTSearch\TNTSearch;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Usergroup;
use App\Entity\DiscussionMessage;

use App\Service\SearchEngineManager;
use Symfony\Component\HttpFoundation\Request;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SearchEngineController extends AbstractController
{
    public const NUMBER_OF_ITEMS_BY_INDEX = 20;
    public const DISCUSSION_INDEX = 'discussions_messages.index';

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

		$results = $this->launchSearch($formObj['formFilters'], $formObj['formTexts']);

		return $this->render( 'pages/search/search.html.twig', [
            'form'    => $formObj['form']->createView(),
			'result_number' => count($results['discussions']),
			'discussionMessages' => $results['discussions']
		] );
	}

    /**
     * Returns an array with the configuration of TNTSearch with the
     * database used by the Symfony project.
     *
     * @return type
     */
    private function getTNTSearchConfiguration(): array
	{

        $databaseURL = $_ENV['DATABASE_URL'];

        $databaseParameters = parse_url($databaseURL);

        $config = [
            'driver'    => $databaseParameters["scheme"],
            'host'      => $databaseParameters["host"],
            'database'  => str_replace("/", "", $databaseParameters["path"]),
            'username'  => $databaseParameters["user"],
            'password'  => $databaseParameters["pass"],
            // Create the fuzzy_storage directory in your project to store the index file
            'storage'   => '/var/www/tntsearch/examples/',
            // A stemmer is optional
            'stemmer'   => \TeamTNT\TNTSearch\Stemmer\PorterStemmer::class
        ];

        return $config;
    }


    /**
     * @Route("/generate-index", name="app_generate-index")
     */
    public function generate_index()
	{

        $tnt = new TNTSearch;

        // Obtain and load the configuration that can be generated with the previous described method
        $tnt->loadConfig($this->getTNTSearchConfiguration());

        // The index file will have the following name, feel free to change it as you want
        $indexer = $tnt->createIndex('groups.index');

        // The result with all the rows of the query will be the data
        // that the engine will use to search, in our case we only want 2 columns
        // (note that the primary key needs to be included)
        $indexer->query('SELECT id, name, slug, description FROM naturadapt_usergroups;');

        // Generate index file !
        $indexer->run();

        return new Response(
            '<html><body>Index succesfully generated !</body></html>'
        );
    }



    public function launchSearch(array $formFilters, array $formTexts): array
    {
		$categories = $formFilters["result_type"];

		// $aaa = $form["search_texts"]["current_tags"];

		// $text = $formTexts["current_tags"];
		$text = implode($formTexts["keywords"], ' ');
        $em = $this->getDoctrine()->getManager();
        $tnt = new TNTSearch;

        // Obtain and load the configuration that can be generated with the previous described method
        $tnt->loadConfig($this->getTNTSearchConfiguration());
		$rows = [];
		$this->setFuzziness($tnt);

		if( in_array( "discussions", $categories ) ){
			$tnt->selectIndex(self::DISCUSSION_INDEX);
			$results = $tnt->search($text, self::NUMBER_OF_ITEMS_BY_INDEX);
			$discussionMessagesRepository = $em->getRepository(DiscussionMessage::class);
			$rowsDiscussions = [];
			foreach($results["ids"] as $id){
				$discussionMessages = $discussionMessagesRepository->find($id);
				$relevantBody = $tnt->snippet($text, strip_tags($discussionMessages->getBody()));
				$rowsDiscussions[] = [
					'id' => $discussionMessages->getId(),
					'title' => $tnt->highlight($discussionMessages->getDiscussion()->getTitle(), $text, 'em', ['wholeWord' => false,]),
					'body' => $tnt->highlight($relevantBody, $text, 'em', ['wholeWord' => false]),
					'author' => $discussionMessages->getAuthor()->getDisplayName()
				];
			}
			$rows['discussions'] = $rowsDiscussions;
		} else {
			$rows['discussions'] = [];
		}

        return $rows;
    }

    protected function setFuzziness($tnt)
    {
		//TODO: Remove function if fuzziness is finally not used
        $tnt->fuzziness            = false;
        //the number of one character changes that need to be made to one string to make it the same as another string
        $tnt->fuzzy_distance       = 2;
        //The number of initial characters which will not be “fuzzified”. This helps to reduce the number of terms which must be examined.
        $tnt->fuzzy_prefix_length  = 2;
        //The maximum number of terms that the fuzzy query will expand to. Defaults to 50
        $tnt->fuzzy_max_expansions = 50;
    }



}
