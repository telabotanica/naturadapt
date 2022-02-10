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
use App\Form\SearchFiltersFormType;
use App\Form\SearchTextsFormType;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Filesystem\Filesystem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Security\Core\Security;

class SearchEngineManager
{
	/**
	 * @var Security
	 */
	private $security;
	/** KernelInterface $appKernel */
	private $appKernel;
	private $manager;
	private $formFactory;
	private $indexesPath;
	private $dbUrl;
	private $categoriesParameters;
	private $tnt;

	private const NUMBER_OF_ITEMS_BY_INDEX = 500;

	/*
	* @param string $indexPath
	*/
	public function __construct(Security $security, EntityManagerInterface $manager, FormFactoryInterface $formFactory, string $projectDir, string $indexesPath, string $dbUrl, array $categoriesParameters)
	{
		$this->projectDir = $projectDir;
		$this->security = $security;
		$this->manager     = $manager;
		$this->formFactory = $formFactory;
		$filesystem = new Filesystem();
		if (!$filesystem->exists($indexesPath)) {
			$filesystem->mkdir($indexesPath);
		}
		$this->indexesPath = $indexesPath;
		$this->dbUrl = $dbUrl;
		$this->categoriesParameters = $categoriesParameters;
	}


	public function getForm(array $form, $headbarQuery, $groupIdQuery): array
	{
		$groupQuery = [];
		// If not requested from searchpage(search url is written, clicked from menu or header searchbar)
		if (empty($form)) {
			$formTexts = [];
			$form['search_filters']['result_type'] = ["pages", "discussions", "actualites", "documents", "membres"];
			$form['search_filters']['groups'] = 'all';
			$form['search_filters']['particularGroups'] = [];
			$formTexts['current_tags'] = [];
			// If requested from header searchBar
			if ($headbarQuery) {
				$formTexts['keywords'] = explode('_ET_',  $headbarQuery);
			} else {
				$formTexts['keywords'] = [];
			}
			// If requested from group page search bar
			if ($groupIdQuery) {
				$repository = $this->manager->getRepository('App\Entity\Usergroup');
				$groupQuery = [$repository->find($groupIdQuery)];
				$form['search_filters']['particularGroups'] = [$groupIdQuery];
			}
		}
		// If requested from search Page
		else {
			$formTexts = $form["search_texts"];

			if (!isset($form["search_filters"]['result_type'])) {
				$form["search_filters"]['result_type'] = ["pages", "discussions", "actualites", "documents", "membres"];
			}
			if (!isset($form['search_filters']['groups'])) {
				$form["search_filters"]['groups'] = 'all';
			}
			if (!isset($form['search_filters']['particularGroups'])) {
				$form["search_filters"]['particularGroups'] = [];
			}

			// If request is done from search bar
			if (!empty($formTexts['query'])) {
				$formTexts['keywords'] = explode('_ET_',  $formTexts['query']);
				unset($formTexts['query']);
			} else {
				$formTexts['keywords'] = [];
			}

			// If Tags was already presents in last request
			if (isset($formTexts['current_tags']) && is_array($formTexts['current_tags'])) {
				$formTexts['keywords'] = array_merge($formTexts['current_tags'], $formTexts['keywords']);
			}
		}

		$form["search_texts"] = $formTexts;

		$tag_array = array_combine($formTexts['keywords'], $formTexts['keywords']);

		$formObj = $this->formFactory->createBuilder(FormType::class, [], array('csrf_protection' => false))
			->setMethod('get')
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
	 * @return array
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
			'storage'   => $this->projectDir . '/' . $this->indexesPath,
			// A stemmer is optional
			'stemmer'   => \TeamTNT\TNTSearch\Stemmer\FrenchStemmer::class
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
	public function search(string $text, array $categories, string $groupsFilter, array $particularGroupsFilter, array $options): array
	{
		$this->tnt->asYouType = false;
		$results = [];
		$maxCountPerCategory = 0;
		$totalCount = 0;
		$groups = [];
		$isUserConnected = false;
		$currentUserId = null;
		$currentUser = $this->security->getUser();
		//Test if a user is connected
		if ((isset($currentUser) && !empty($currentUser))) {
			//Test if we want to filter in the groups of the current user
			if (($groupsFilter != 'all')) {
				$groups = array_map(
					function (UsergroupMembership $membership) {
						return $membership->getUsergroup()->getId();
					},
					iterator_to_array(
						$currentUser->getUsergroupMemberships()
					)
				);
			}
			$isUserConnected = true;
			$currentUserId = $currentUser->getId();
		}

		//filter according categories
		foreach ($categories as $category) {
			$categoryParams = $this->categoriesParameters[$category];
			$this->tnt->selectIndex($categoryParams['index']);
			//Search match in tnt index
			$searchResults = $this->tnt->searchBoolean($text, self::NUMBER_OF_ITEMS_BY_INDEX);
			//Get data of the matching objects
			$repository = $this->manager->getRepository('App\Entity\\' . $categoryParams['class']);
			$entities = $repository->searchFromIdsAndProperties($searchResults['ids'], $groups, $particularGroupsFilter, $currentUserId, $categoryParams['propertyList'], ['page' => $options['page'], 'limit' => $options['per_index_per_page']]);
			//Style
			$toHightlight = ['title', 'discussion_title', 'name'];
			$toSnippetAndHightlight = ['body', 'presentation', 'bio'];
			$results[$category] = $this->applyTntStyles($text, $entities, $toHightlight, $toSnippetAndHightlight);
			$categoryCount = $repository->searchCountFromIdsAndProperties($searchResults['ids'], $groups, $particularGroupsFilter, $currentUserId, $categoryParams['propertyList']);
			if ($maxCountPerCategory < $categoryCount) {
				$maxCountPerCategory = $categoryCount;
			}
			$totalCount = $totalCount + $categoryCount;
		}
		return [
			'results'             =>    $results,
			'total'               =>    $totalCount,
			'maxCountPerCategory' =>    $maxCountPerCategory,
			'connexionBoolean'    =>    $isUserConnected,
		];
	}

	public function searchGroup($em, string $text): array
	{
		$this->tnt->selectIndex('groups.index');
		$this->tnt->asYouType = true;
		$results = $this->tnt->search($text);
		return $results['ids'];
	}

	public function applyTntStyles(string $text, array $entities, array $propertiestoHightlight, array $propertiestoSnippetAndHightlight): array
	{
		foreach ($entities as $key => $entity) {
			foreach ($entity as $property => $value) {
				if (in_array($property, $propertiestoHightlight)) {
					$entities[$key][$property] = $this->tnt->highlight($value, $text, 'em', ['wholeWord' => false,]);
				} else if (in_array($property, $propertiestoSnippetAndHightlight)) {
					$snippetedValue = $this->tnt->snippet($text, strip_tags($value));
					if ($snippetedValue !== '.....') {
						$entities[$key][$property] = $this->tnt->highlight($snippetedValue, $text, 'em', ['wholeWord' => false,]);
					} else {
						$entities[$key][$property] = $this->tnt->highlight($value, $text, 'em', ['wholeWord' => false,]);
					}
				}
			}
		}
		return $entities;
	}

	public function snippetGroupsText(string $text, array $groups)
	{
		foreach ($groups as $group) {
			$descriptionHtml = strip_tags($group->getDescription());
			$textTemp = $this->tnt->snippet($text, $descriptionHtml);
			// If snippet returned '.....' (case for a long text without match) we display the text without snippet
			if ($textTemp !== '.....') {
				$group->setDescription($this->tnt->snippet($text, $descriptionHtml, 120, 30));
			}
		}
		return $groups;
	}

	public function highlightText(string $text, string $groupsHTML)
	{
		return $this->tnt->highlight($groupsHTML, $text, 'em', ['wholeWord' => false]);
	}

	/**
	 * @param \App\Entity\DiscussionMessage|\App\Entity\Article|\App\Entity\Page|\App\Entity\User|\App\Entity\Document       $entity
	 * @param string       $action
	 */
	public function changeIndex($entity, $action)
	{
		$category = $this->getCategoryFromEntity($entity);
		$categoryParams = $this->categoriesParameters[$category];
		$this->tnt->selectIndex($categoryParams['index']);
		$index = $this->tnt->getIndex();
		switch ($action) {
			case 'persist':
				$index->insert($this->getEntityPropertyList($entity, $categoryParams['indexPropertyList']));
				break;
			case 'remove':
				$index->delete($entity->getId());
				break;
			case 'update':
				$index->update($entity->getId(), $this->getEntityPropertyList($entity, $categoryParams['indexPropertyList']));
				break;
			default:
				break;
		}
	}

	public function getEntityPropertyList($entity, $properties)
	{
		$result = [];
		foreach ($properties as $property) {
			//We generate the name of the getter to get the property
			$result[$property] = $entity->{'get' . $property}();
		}
		return $result;
	}

	public function getCategoryFromEntity($entity)
	{
		switch (get_class($entity)) {
			case 'App\Entity\DiscussionMessage':
				return 'discussions';
				break;
			case 'App\Entity\Document':
				return 'documents';
				break;
			case 'App\Entity\Page':
				return 'pages';
				break;
			case 'App\Entity\User':
				return 'membres';
				break;
			case 'App\Entity\Article':
				return 'actualites';
				break;
			case 'App\Entity\Usergroup':
				return 'groups';
				break;
			default:
				return;
		}
	}
}
