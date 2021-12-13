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
    private $formFactory;

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
        $searchQuery = $request->request->get('searchQuery');

        // TODO: Add Pagination to results
		$page     = $request->query->get( 'page', 0 );
		$per_page = 20;
        $filters = $request->query->get( 'form', [] );

        unset( $filters[ 'submit' ] );

        // If request comes from header search bar
        if ($searchQuery != '') {
            $filters[ 'keywords' ] = explode( ',',  $searchQuery  );
        }
		// If the search url is directly taped
		else if(empty( $filters)){
			$filters[ 'keywords' ] = [];
		}
        // If request is done from search page search bar
        else if ( !empty( $filters[ 'query' ] ) ){
			if(isset($filters[ 'currentTags' ])){
				$filters[ 'keywords' ] = array_merge($filters[ 'currentTags' ], explode( ',',  $filters[ 'query' ]  ));
			} else {
				$filters[ 'keywords' ] = explode( ',',  $filters[ 'query' ]  );
			}
			unset( $filters[ 'query' ] );
		}
		// If request is provoked by filter change
		else {
			// If request is provoked by the last tag removal
			if (isset($filters[ 'currentTags' ])){
				$filters[ 'keywords' ] = $filters[ 'currentTags' ];
			} else {
				$filters[ 'keywords' ] = [];
			}
		}

        $data = $searchEngineManager->getForm(
            $filters,
            [ 'page' => $page, 'per_page' => $per_page ]
        );

        $data[ 'form' ]->handleRequest( $request );

		if (isset($filters[ 'resultType' ])){
			$results = $this->launchSearch($filters[ 'keywords' ], $filters['resultType']);
		} else {
			$results['discussions'] = [];
		}

		$discussionMessages = $results['discussions'];

		return $this->render( 'pages/search/search.html.twig', [
            'form'    => $data[ 'form' ]->createView(),
			'result_number' => count($results['discussions']),
			'discussionMessages' => $discussionMessages
		] );
	}

    /**
     * Returns an array with the configuration of TNTSearch with the
     * database used by the Symfony project.
     *
     * @return type
     */

    private function getTNTSearchConfiguration(){

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
    public function generate_index(){

        $tnt = new TNTSearch;

        // Obtain and load the configuration that can be generated with the previous described method
        $configuration = $this->getTNTSearchConfiguration();
        $tnt->loadConfig($configuration);

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





    public function launchSearch($wordList, $categories=[])
    {
		$text = implode($wordList, ' ');

        $em = $this->getDoctrine()->getManager();

        $tnt = new TNTSearch;

        // Obtain and load the configuration that can be generated with the previous described method
        $configuration = $this->getTNTSearchConfiguration();
        $tnt->loadConfig($configuration);

		$rows = [];

		$this->setFuzziness($tnt);

		if( in_array( "discussions", $categories ) ){
			// Use the generated index in the previous step
			$tnt->selectIndex('discussions_messages.index');
			$maxResults = 20;

			$results = $tnt->search($text, $maxResults);
			$discussionMessagesRepository = $em->getRepository(DiscussionMessage::class);
			$rowsDiscussions = [];
			foreach($results["ids"] as $id){
				// You can optimize this by using the FIELD function of MySQL if you are using mysql
				// more info at: https://ourcodeworld.com/articles/read/1162/how-to-order-a-doctrine-2-query-result-by-a-specific-order-of-an-array-using-mysql-in-symfony-5
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
		}


        // Use the generated index in the previous step
        $tnt->selectIndex('groups.index');

        $maxResults = 20;

        $this->setFuzziness($tnt);

        // Search for a band named like "Guns n' roses"
        $results = $tnt->search($text, $maxResults);

        // Keep a reference to the Doctrine repository of artists
        $usergroupsRepository = $em->getRepository(Usergroup::class);

        // Store the results in an array
        $rowsGroup = [];

        foreach($results["ids"] as $id){
			// You can optimize this by using the FIELD function of MySQL if you are using mysql
            // more info at: https://ourcodeworld.com/articles/read/1162/how-to-order-a-doctrine-2-query-result-by-a-specific-order-of-an-array-using-mysql-in-symfony-5
            $userGroup = $usergroupsRepository->find($id);

            $rowsGroup[] = [
				'id' => $userGroup->getId(),
                'name' => $userGroup->getName(),
                'description' => $userGroup->getDescription()
            ];
        }

		$rows['groups'] = $rowsGroup;


        // Return the results to the user
        // return new JsonResponse($rows);
        return $rows;
    }

	/**
	 * @Route("/searchEngine", name="app_search")
	 */
	public function search()
	{
		$em = $this->getDoctrine()->getManager();

		$tnt = new TNTSearch;

		// Obtain and load the configuration that can be generated with the previous described method
		$configuration = $this->getTNTSearchConfiguration();
		$tnt->loadConfig($configuration);

		// Use the generated index in the previous step
		$tnt->selectIndex('groups.index');

		$maxResults = 20;

		$this->setFuzziness($tnt);

		// Search for a band named like "Guns n' roses"
		$results = $tnt->search("que", $maxResults);

		// Keep a reference to the Doctrine repository of artists
		$usergroupsRepository = $em->getRepository(Usergroup::class);

		// Store the results in an array
		$rows = [];

		foreach($results["ids"] as $id){
			// You can optimize this by using the FIELD function of MySQL if you are using mysql
			// more info at: https://ourcodeworld.com/articles/read/1162/how-to-order-a-doctrine-2-query-result-by-a-specific-order-of-an-array-using-mysql-in-symfony-5
			$userGroup = $usergroupsRepository->find($id);

			$rows[] = [
				'id' => $userGroup->getId(),
				// 'name' => $userGroup->getName(),
				'description' => $userGroup->getDescription()
			];
		}

		// Return the results to the user
		return new JsonResponse($rows);
	}

    protected function setFuzziness($tnt)
    {
        $tnt->fuzziness            = false;
        //the number of one character changes that need to be made to one string to make it the same as another string
        $tnt->fuzzy_distance       = 2;
        //The number of initial characters which will not be “fuzzified”. This helps to reduce the number of terms which must be examined.
        $tnt->fuzzy_prefix_length  = 2;
        //The maximum number of terms that the fuzzy query will expand to. Defaults to 50
        $tnt->fuzzy_max_expansions = 50;
    }



}
