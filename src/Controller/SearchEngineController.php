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
    public const NUMBER_OF_ITEMS_BY_INDEX = 20;
    public const DISCUSSION_INDEX = 'discussions_messages.index';
	public const ACTUALITE_INDEX = 'articles.index';
	public const PAGE_INDEX = 'pages.index';
	public const DOCUMENT_INDEX = 'documents.index';
	public const MEMBER_INDEX = 'members.index';



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
			'result_number' => count(array_merge($results['discussions'], $results['actualites'], $results['pages'], $results['documents'], $results['membres'])),
			'discussionMessages' => $results['discussions'],
			'actualites' => $results['actualites'],
			'pages' => $results['pages'],
			'documents' => $results['documents'],
			'membres' => $results['membres'],
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
        // $indexer = $tnt->createIndex('groups.index');
		// $indexer = $tnt->createIndex('pages.index');
		// $indexer = $tnt->createIndex('documents.index');
		$indexer = $tnt->createIndex('members.index');

        // The result with all the rows of the query will be the data
        // that the engine will use to search, in our case we only want 2 columns
        // (note that the primary key needs to be included)
        // $indexer->query('SELECT id, title, body FROM naturadapt_usergroups;');
		// $indexer->query('SELECT id, title, body FROM naturadapt_pages;');
		// $indexer->query('SELECT id, title FROM naturadapt_document;');
		$indexer->query('SELECT id, name, presentation, bio FROM naturadapt_users;');


        // Generate index file !
        $indexer->run();

        return new Response(
            '<html><body>Index succesfully generated !</body></html>'
        );
    }


    public function launchSearch(array $formFilters, array $formTexts): array
    {
		$categories = $formFilters["result_type"];

		$text = implode($formTexts["keywords"], ' ');
        $em = $this->getDoctrine()->getManager();
        $tnt = new TNTSearch;

        // Obtain and load the configuration that can be generated with the previous described method
        $tnt->loadConfig($this->getTNTSearchConfiguration());
		$results = [];
		$this->setFuzziness($tnt);

		if( in_array( 'discussions', $categories ) ){
			$results['discussions'] = $this->searchResultByCategory(
				'discussions',
				self::DISCUSSION_INDEX,
				$em->getRepository(DiscussionMessage::class),
				['id', 'title', 'body', 'author', 'group', 'uuid'],
				$tnt,
				$text
			);
		} else {
			$results['discussions'] = [];
		}

		if( in_array( 'actualites', $categories ) ){
			$results['actualites'] = $this->searchResultByCategory(
				'actualites',
				self::ACTUALITE_INDEX,
				$em->getRepository(Article::class),
				['id', 'title', 'body', 'author', 'group', 'slug'],
				$tnt,
				$text
			);
		} else {
			$results['actualites'] = [];
		}

		if( in_array( 'pages', $categories ) ){
			$results['pages'] = $this->searchResultByCategory(
				'pages',
				self::PAGE_INDEX,
				$em->getRepository(Page::class),
				['id', 'title', 'body', 'author', 'group', 'slug'],
				$tnt,
				$text
			);
		} else {
			$results['pages'] = [];
		}

		if( in_array( 'documents', $categories ) ){
			$results['documents'] = $this->searchResultByCategory(
				'documents',
				self::DOCUMENT_INDEX,
				$em->getRepository(Document::class),
				['id', 'title', 'group'],
				$tnt,
				$text
			);
		} else {
			$results['documents'] = [];
		}

		if( in_array( 'membres', $categories ) ){
			$results['membres'] = $this->searchResultByCategory(
				'membres',
				self::MEMBER_INDEX,
				$em->getRepository(User::class),
				['id', 'name', 'presentation', 'bio'],
				$tnt,
				$text
			);
		} else {
			$results['membres'] = [];
		}

        return $results;
    }

	private function searchResultByCategory($category, $index, $repository, $propertyList, $tnt, $text){
		$tnt->selectIndex($index);
		$results = $tnt->search($text, self::NUMBER_OF_ITEMS_BY_INDEX);
		// $repository = $em->getRepository($class);
		$rows = [];
		foreach($results["ids"] as $id){
			$item = $repository->find($id);
			$result = [];
			foreach($propertyList as $property){
				switch ($property) {
					case 'id':
						$result['id'] = $item->getId();
						break;
					case 'title':
						if ($category==='discussions'){
							$result['title'] = $tnt->highlight($item->getDiscussion()->getTitle(), $text, 'em', ['wholeWord' => false,]);
						} else {
							$result['title'] = $tnt->highlight($item->getTitle(), $text, 'em', ['wholeWord' => false,]);
						}
						break;
					case 'body':
						$relevantBody = $tnt->snippet($text, strip_tags($item->getBody()));
						$result['body'] = $tnt->highlight($relevantBody, $text, 'em', ['wholeWord' => false]);
						break;
					case 'author':
						$result['author'] = $item->getAuthor()->getDisplayName();
						break;
					case 'presentation':
						$result['presentation'] = $item->getPresentation();
						break;
					case 'bio':
						$result['bio'] = $item->getBio();
						break;
					case 'name':
						$result['name'] = $item->getName();
						break;
					case 'group':
						if ($category==='discussions'){
							$result['group'] = $item->getDiscussion()->getUsergroup();
						} else {
							$result['group'] = $item->getUsergroup();
						}
						break;
					case 'slug':
						$result['slug'] = $item->getSlug();
						break;
					case 'uuid':
						if ($category==='discussions'){
							$result['uuid'] = $item->getDiscussion()->getUuid();
						}
						break;
				}
			}
			array_push($rows, $result);
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
