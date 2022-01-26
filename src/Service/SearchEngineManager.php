<?php

namespace App\Service;

use TeamTNT\TNTSearch\TNTSearch;
use App\Entity\Usergroup;
use App\Entity\UsergroupMembership;

use App\Entity\DiscussionMessage;
use App\Entity\Article;
use App\Entity\Page;
use App\Entity\User;
use App\Entity\Document;
use App\Entity\DiscussionMessageRepository;
use App\Entity\ArticlesRepository;
use App\Entity\PageRepository;
use App\Entity\UserRepository;
use App\Entity\DocumentRepository;
use App\Repository\UsergroupRepository;

use App\Form\SearchFiltersFormType;
use App\Form\SearchTextsFormType;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Filesystem\Filesystem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Intl\Intl;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Security\Core\Security;

class SearchEngineManager {
	/**
     * @var Security
     */
    private $security;
	private $currentUserGroupIdList;
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
	public function __construct (Security $security, EntityManagerInterface $manager, FormFactoryInterface $formFactory, string $projectDir, string $indexesPath, string $dbUrl, array $categoriesParameters ) {
		$this->projectDir = $projectDir;
		$this->security = $security;
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


	public function getForm (array $form, $headbarQuery, $groupIdQuery, array $options = [] ): array
	{
		$groupQuery = [];
		// If not requested from searchpage(search url is written, clicked from menu or header searchbar)
		if(empty( $form)){
			$formTexts = [];
			$form['search_filters'][ 'result_type' ] = ["pages","discussions","actualites","documents","membres"];
			$form['search_filters'][ 'groups' ] = 'all';
			$form['search_filters']['particularGroups']=[];
			$formTexts[ 'current_tags' ] = [];
			// If requested from header searchBar
			if($headbarQuery){
				$formTexts[ 'keywords' ] = explode( '_ET_',  $headbarQuery  );
			} else {
				$formTexts[ 'keywords' ] = [];
			}
			// If requested from group page search bar
			if($groupIdQuery){
				$repository = $this->manager>getRepository('App\Entity\Usergroup');
				$groupQuery = [$repository->find($groupIdQuery)];
				$form['search_filters'][ 'particularGroups' ] = [$groupIdQuery];
			}
		}
		// If requested from search Page
		else {
			$formTexts = $form["search_texts"];

			if (!isset($form["search_filters"][ 'result_type' ])){
				$form["search_filters"][ 'result_type' ] = ["pages","discussions","actualites","documents","membres"];
			}
			if (!isset($form['search_filters'][ 'groups' ])){
				$form["search_filters"][ 'groups' ] = 'all';
			}
			if (!isset($form['search_filters'][ 'particularGroups' ])){
				$form["search_filters"][ 'particularGroups' ] = [];
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
										->add('search_filters', SearchFiltersFormType::class, [
											'particular_groups' => $groupQuery
										])
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

		$config = [
			'driver'    => $databaseParameters["scheme"],
			'host'      => $databaseParameters["host"],
			'database'  => str_replace("/", "", $databaseParameters["path"]),
			'username'  => $databaseParameters["user"],
			'password'  => $databaseParameters["pass"],
			// Create the fuzzy_storage directory in your project to store the index file
			'storage'   => $this->projectDir .'/'. $this->indexesPath,
			// A stemmer is optional
			'stemmer'   => \TeamTNT\TNTSearch\Stemmer\PorterStemmer::class
		];

		return $config;
	}

	/**
     * @param \TeamTNT\TNTSearch\TNTSearch $tnt
     */
	public function setFuzziness($tnt)
	{
		//TODO: Remove function if fuzziness is finally not used
		$tnt->fuzziness            = true;
		//the number of one character changes that need to be made to one string to make it the same as another string
		$tnt->fuzzy_distance       = 1;
		//The number of initial characters which will not be “fuzzified”. This helps to reduce the number of terms which must be examined.
		$tnt->fuzzy_prefix_length  = 2;
		//The maximum number of terms that the fuzzy query will expand to. Defaults to 50
		$tnt->fuzzy_max_expansions = 50;
	}

	/**
	 * Launch Search with tntsearch and return an array of the results
	 *
	 * @return array
     */
	public function search(string $text, array $categories, string $groups, array $particularGroups): array
	{
		$results = [];

		$this->currentUserGroupIdList= array_map(
			function ( UsergroupMembership $membership ) {
				return $membership->getUsergroup()->getId();
			},
			iterator_to_array(
				$this->security->getUser()->getUsergroupMemberships()
			)
		);

		//filter according categories
		foreach($categories as $category){
			$categoryParams = $this->categoriesParameters[$category];
			$this->tnt->selectIndex($categoryParams['index']);
			$searchResults = $this->tnt->search($text, self::NUMBER_OF_ITEMS_BY_INDEX);
			$rows = [];
			$repository = $this->manager->getRepository('App\Entity\\' . $categoryParams['class']);
			foreach($searchResults['ids'] as $id){
				$item = $repository->find($id);
				// Pass to the following loop if we do not want all groups and if the current item is not in the list of group of the current user
				if(($groups!=='all') && (!$this->isItemGroupIdInIdList($category, $item, $this->currentUserGroupIdList))){
					continue;
				}
				// Pass to the following loop if search in particular groups is asked but the item is not in those groups
				if(((!empty($particularGroups)) && (!$this->isItemGroupIdInIdList($category, $item, array_map('intval', $particularGroups))))){
					continue;
				}
				$result = [];
				foreach($categoryParams['propertyList'] as $property){
					$result[$property]=$this->getStyledDataFromProperty($property, $text, $item, $category);
				}
				array_push($rows, $result);
			}
			$results[$category] = $rows;

		}
		return $results;
	}

	/**
	 * @param string                          $property
	 * @param string                          $text
	 * @param \App\Entity\DiscussionMessage|\App\Entity\Article|\App\Entity\Page|\App\Entity\User|\App\Entity\Document $item
	 * @param string                          $category
	 *
	 * @return string
	 */
	public function getStyledDataFromProperty($property, $text, $item, $category)
	{
		switch ($property) {
			case 'id':
				return $item->getId();
			case 'title':
				if ($category==='discussions'){
					return $this->tnt->highlight($item->getDiscussion()->getTitle(), $text, 'em', ['wholeWord' => false,]);
				} else {
					return $this->tnt->highlight($item->getTitle(), $text, 'em', ['wholeWord' => false,]);
				}
			case 'body':
				$relevantBody = $this->tnt->snippet($text, strip_tags($item->getBody()));
				return $this->tnt->highlight($relevantBody, $text, 'em', ['wholeWord' => false]);
			case 'author':
				return $item->getAuthor()->getDisplayName();
			case 'presentation':
				return $item->getPresentation();
			case 'bio':
				return $item->getBio();
			case 'name':
				return $item->getName();
			case 'group':
				if ($category==='discussions'){
					return $item->getDiscussion()->getUsergroup();
				} else {
					return $item->getUsergroup();
				}
			case 'slug':
				return $item->getSlug();
			case 'uuid':
				if ($category==='discussions'){
					return $item->getDiscussion()->getUuid();
				}
			default:
				return '';
		}
	}

	/**
	* Check if the group Id(or one of the groups in case of the member category) of an item is in a list of Id
	*
    * @param \App\Entity\DiscussionMessage|\App\Entity\Article|\App\Entity\Page|\App\Entity\User|\App\Entity\Document $item
	*
	* @return bool
    */
	public function isItemGroupIdInIdList(string $category, $item, array $idList){
		if ($category==='discussions'){
			return in_array($item->getDiscussion()->getUsergroup()->getId(),  $idList);
		} else if ($category==='membres') {
			$memberGroups = array_map(
				function ( UsergroupMembership $membership ) {
					return $membership->getUsergroup()->getId();
				},
				iterator_to_array(
					$item->getUsergroupMemberships()
				)
			);
			return !empty(array_intersect($memberGroups, $idList));
		} else {
			return in_array($item->getUsergroup()->getId(),  $idList);
		}
	}

}
