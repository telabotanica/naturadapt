<?php

namespace App\Service;

use TeamTNT\TNTSearch\TNTSearch;
use App\Entity\Usergroup;
use App\Entity\DiscussionMessage;
use App\Entity\Article;
use App\Entity\Page;
use App\Entity\User;
use App\Entity\Document;
use App\Form\SearchFiltersFormType;
use App\Form\SearchTextsFormType;

use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Filesystem\Filesystem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Intl\Intl;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;

class SearchEngineManager {
	/** KernelInterface $appKernel */
	private $appKernel;
	private $manager;
	private $formFactory;
	private $indexesPath;
	private $dbUrl;
	private $categoriesParameters;
	private $tnt;

	private const NUMBER_OF_ITEMS_BY_INDEX = 20;

	/*
	* @param string $indexPath
	*/
	public function __construct (KernelInterface $appKernel, EntityManagerInterface $manager, FormFactoryInterface $formFactory, string $indexesPath, string $dbUrl, array $categoriesParameters ) {
		$this->appKernel = $appKernel;
		$this->manager     = $manager;
		$this->formFactory = $formFactory;
		$filesystem = new Filesystem();
		if(!$filesystem->exists($indexesPath)){
			$filesystem->mkdir($indexesPath);
		}
		$this->indexesPath = $indexesPath;
		$this->dbUrl = $dbUrl;
		$this->categoriesParameters = $categoriesParameters;
	}


	public function getForm ( array $form, $headbar_query, array $options = [] ): array
	{
		// If not requested from searchpage(search url is written, clicked from menu or header searchbar)
		if(empty( $form)){
			$formTexts = [];
			$form['search_filters'][ 'result_type' ] = ["pages","discussions","actualites","documents","membres"];
			$formTexts[ 'current_tags' ] = [];
			// If requested from header searchBar
			if($headbar_query){
				$formTexts[ 'keywords' ] = explode( '_ET_',  $headbar_query  );
			} else {
				$formTexts[ 'keywords' ] = [];
			}
		}
		// If requested from search Page
		else {
			$formTexts = $form["search_texts"];

			if (!isset($form["search_filters"][ 'result_type' ])){
				$form["search_filters"][ 'result_type' ] = ["pages","discussions","actualites","documents","membres"];
			}

			// If request is done from search bar
			if ( !empty( $formTexts[ 'query' ] ) ){
				$formTexts[ 'keywords' ] = explode( '_ET_',  $formTexts[ 'query' ]  );
				unset( $formTexts[ 'query' ] );
			} else {
				$formTexts[ 'keywords' ] = [];
			}

			// If Tags was already presents in last request
			if(isset($formTexts[ 'current_tags' ]) && is_array($formTexts['current_tags'])){
				$formTexts[ 'keywords' ] = array_merge($formTexts[ 'current_tags' ], $formTexts[ 'keywords' ]);
			}
		}

		$form["search_texts"] = $formTexts;

		$tag_array = array_combine($formTexts[ 'keywords' ], $formTexts[ 'keywords' ]);

		$formObj = $this->formFactory	->createBuilder( FormType::class, [], array('csrf_protection' => false) )
								  		->setMethod( 'get' )
										->add('search_filters', SearchFiltersFormType::class)
										->add('search_texts', SearchTextsFormType::class, [
											'tag_array' => $tag_array
										])
										->getForm();
		return [
			'form' => $formObj,
			'formFilters' => $form["search_filters"],
			'formTexts' => $formTexts
		];
	}

	public function setTNTSearchConfiguration()
	{
		$this->tnt = new TNTSearch;
		// Obtain and load the configuration that can be generated with the previous described method
		$this->tnt->loadConfig($this->getTNTSearchConfiguration());
		$this->setFuzziness($this->tnt);
	}

	/**
	 * Returns an array with the configuration of TNTSearch with the
	 * database used by the Symfony project.
	 *
	 * @return type
	 */
	public function getTNTSearchConfiguration(): array
	{

		$databaseURL = $this->dbUrl;

		$databaseParameters = parse_url($databaseURL);

		$projectRoot = $this->appKernel->getProjectDir();

		$config = [
			'driver'    => $databaseParameters["scheme"],
			'host'      => $databaseParameters["host"],
			'database'  => str_replace("/", "", $databaseParameters["path"]),
			'username'  => $databaseParameters["user"],
			'password'  => $databaseParameters["pass"],
			// Create the fuzzy_storage directory in your project to store the index file
			'storage'   => $projectRoot .'/'. $this->indexesPath,
			// A stemmer is optional
			'stemmer'   => \TeamTNT\TNTSearch\Stemmer\PorterStemmer::class
		];

		return $config;
	}

	public function setFuzziness($tnt)
	{
		//TODO: Remove function if fuzziness is finally not used
		$tnt->fuzziness            = true;
		//the number of one character changes that need to be made to one string to make it the same as another string
		$tnt->fuzzy_distance       = 2;
		//The number of initial characters which will not be “fuzzified”. This helps to reduce the number of terms which must be examined.
		$tnt->fuzzy_prefix_length  = 2;
		//The maximum number of terms that the fuzzy query will expand to. Defaults to 50
		$tnt->fuzzy_max_expansions = 50;
	}

	public function search($em, string $text, array $categories): array
	{
		$this->tnt->asYouType = false;
		$results = [];
		foreach($categories as $category){
			$categoryParams = $this->categoriesParameters[$category];
			$this->tnt->selectIndex($categoryParams['index']);
			$searchResults = $this->tnt->search($text, self::NUMBER_OF_ITEMS_BY_INDEX);
			$results[$category] = $this->searchResultByCategory(
				$text,
				$searchResults['ids'],
				$category,
				$em->getRepository('App\Entity\\' . $categoryParams['class']),
				$categoryParams['propertyList']
			);
		}
		return $results;
	}

	public function searchGroup($em, string $text): array
	{
		$this->tnt->selectIndex('groups.index');
		$this->tnt->asYouType = true;
		$results = $this->tnt->search($text);
		$repository = $em->getRepository('App\Entity\Usergroup');
		return $results['ids'];
	}

	public function searchResultByCategory($text, $ids, $category, $repository, $propertyList)
	{
		$rows = [];
		foreach($ids as $id){
			$item = $repository->find($id);
			$result = [];
			foreach($propertyList as $property){
				switch ($property) {
					case 'id':
						$result['id'] = $item->getId();
						break;
					case 'title':
						if ($category==='discussions'){
							$result['title'] = $this->tnt->highlight($item->getDiscussion()->getTitle(), $text, 'em', ['wholeWord' => false,]);
						} else {
							$result['title'] = $this->tnt->highlight($item->getTitle(), $text, 'em', ['wholeWord' => false,]);
						}
						break;
					case 'body':
						$relevantBody = $this->tnt->snippet($text, strip_tags($item->getBody()));
						$result['body'] = $this->tnt->highlight($relevantBody, $text, 'em', ['wholeWord' => false]);
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
					default:
						break;
				}
			}
			array_push($rows, $result);
		}
		return $rows;
	}

	public function snippetGroupsText(string $text, array $groups){
		foreach ( $groups as $group ) {
			$textTemp=$this->tnt->snippet($text, strip_tags($group->getDescription()), 120, 60);
			if($textTemp !=='.....'){
				$group->setDescription($this->tnt->snippet($text, strip_tags($group->getDescription()), 120, 60));
			}
		}
		return $groups;
	}

	public function highlightText(string $text, string $groupsHTML){
		$groupsHTML = $this->tnt->highlight($groupsHTML, $text, 'em', ['wholeWord' => false]);
		return $groupsHTML;
	}

}
