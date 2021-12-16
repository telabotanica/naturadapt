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

	public function __construct ( EntityManagerInterface $manager, FormFactoryInterface $formFactory  ) {
		$this->manager     = $manager;
		$this->formFactory = $formFactory;
	}


	public function getForm ( array $form, $headbar_query, array $options = [] ): array
	{
		// If not requested from searchpage(search url is written, clicked from menu or header searchbar)
		if(empty( $form)){
			$formFilters = [];
			$formTexts = [];
			$formFilters[ 'result_type' ] = ["pages","discussions","actualites","documents","membres"];
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
			$formFilters = $form["search_filters"];
			$formTexts = $form["search_texts"];

			if (!isset($formFilters[ 'result_type' ])){
				$formFilters[ 'result_type' ] = [];
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

		$form["search_filters"] = $formFilters;
		$form["search_texts"] = $formTexts;

		$tag_array = array_combine($formTexts[ 'keywords' ], $formTexts[ 'keywords' ]);

		$form = $this->formFactory	->createBuilder( FormType::class, [], array('csrf_protection' => false) )
                                  	->setMethod( 'get' )
									->add('search_filters', SearchFiltersFormType::class)
									->add('search_texts', SearchTextsFormType::class, [
										'tag_array' => $tag_array
									])
									->getForm();
		return [
			'form' => $form,
			'formFilters' => $formFilters,
			'formTexts' => $formTexts
		];
	}


}
