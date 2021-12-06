<?php

namespace App\Service;

use App\Entity\Skill;
use App\Repository\SkillRepository;

use App\Entity\Usergroup;
use App\Repository\UsergroupRepository;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Intl\Intl;
use Symfony\Component\Form\Extension\Core\Type\TextType;

use App\Form\TagType;

class SearchEngineManager {
	private $manager;
	private $formFactory;

	public function __construct ( EntityManagerInterface $manager, ContainerInterface $container ) {
		$this->manager     = $manager;
		$this->formFactory = $container->get( 'form.factory' );
	}


	public function getForm ( $filters, $options = [] ) {

		$tagArray = array_combine($filters[ 'keywords' ], $filters[ 'keywords' ]);

		$form = $this->formFactory	->createBuilder( FormType::class, $filters )
                                  	->setMethod( 'get' )
									->add( 'resultType', ChoiceType::class, [
											'required' => FALSE,
											'expanded' => TRUE,
											'multiple' => TRUE,
											'choices'  => array_combine([
												'pages.group.pages.title',
												'pages.group.discussions.title',
												'pages.group.articles.title',
												'pages.group.documents.title',
												'pages.group.members.title'
											],[
												"pages",
												"discussions",
												"actualites",
												"documents",
												"membres"
											]),
									] )
									->add( 'groups', ChoiceType::class, [
										'required' => FALSE,
										'multiple' => FALSE,
										'expanded' => TRUE,
										'choices'  => [
											'Tous les groupes' => "All Groups",
											'Mes groupes' => "My Groups"
										],
										'data' => "All Groups",
										'placeholder' => false
										] )
									->add( 'particularGroup', EntityType::class, [
										'class'                     => Usergroup::class,
										'required'                  => FALSE,
										'expanded'                  => TRUE,
										'multiple'                  => TRUE,
										'query_builder'             => function ( UsergroupRepository $repository ) {
											return $repository->createQueryBuilder( 'u' )
																->orderBy( 'u.slug', 'ASC' );
										},
										'choice_label'              => 'slug',
									] )
									->add( 'query', SearchType::class, [
										'required' => FALSE,
										] )
									->add( 'currentTags', ChoiceType::class, [
										'required'                  => FALSE,
										'expanded'                  => TRUE,
										'multiple'                  => TRUE,
										'choices'  => $tagArray,
										'choice_attr' => function() {
											return ['checked' => 'checked'];
										},
										] )
									->add( 'submit', SubmitType::class )
									->getForm();

		return [
			'form'    => $form,
		];
	}


}
