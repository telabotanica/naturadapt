<?php

namespace App\Service;

use App\Form\SearchFiltersFormType;
use App\Form\SearchTextsFormType;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;

use Symfony\Component\Form\Extension\Core\Type\FormType;


use Symfony\Component\Intl\Intl;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class SearchEngineManager {
	private $manager;
	private $formFactory;
	private $indexesPath;
	private $dbUrl;

	/*
	* @param string $indexPath
	*/
	public function __construct ( EntityManagerInterface $manager, FormFactoryInterface $formFactory, string $indexesPath, string $dbUrl ) {
		$this->manager     = $manager;
		$this->formFactory = $formFactory;
		$this->indexesPath = $indexesPath;
		$this->dbUrl = $dbUrl;
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
				$formTexts[ 'keywords' ] = explode( ',',  $headbar_query  );
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
				$formTexts[ 'keywords' ] = explode( ',',  $formTexts[ 'query' ]  );
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
            'storage'   => $this->indexesPath,
            // A stemmer is optional
            'stemmer'   => \TeamTNT\TNTSearch\Stemmer\PorterStemmer::class
        ];

        return $config;
    }

	public function setFuzziness($tnt)
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
