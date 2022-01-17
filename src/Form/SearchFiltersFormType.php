<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Usergroup;
use App\Repository\UsergroupRepository;


class SearchFiltersFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
			->add( 'result_type', ChoiceType::class, [
				'required' => false,
				'expanded' => true,
				'multiple' => true,
				'choices'  => array(
					'pages.group.pages.title' => 'pages',
					'pages.group.discussions.title' => 'discussions',
					'pages.group.articles.title' => 'actualites',
					'pages.group.documents.title' => 'documents',
					'pages.group.members.title' => 'membres'
				)
			] )
			->add( 'groups', ChoiceType::class, [
				'required' => false,
				'multiple' => false,
				'expanded' => true,
				'choices'  => [
					'Tous les groupes' => 'all',
					'Mes groupes' => 'currentUserGroups'
				],
				'data' => 'all',
				'placeholder' => false
				] )
			->add( 'particularGroups', EntityType::class, [
				'class'                     => Usergroup::class,
				'required'                  => false,
				'expanded'                  => true,
				'multiple'                  => true,
				'query_builder'             => function ( UsergroupRepository $repository ) {
					return $repository->createQueryBuilder( 'u' )
										->orderBy( 'u.slug', 'ASC' );
				},
				'data'                   => $options['particular_groups'],
				'choice_label'              => 'slug',
			] );
    }

	public function configureOptions(OptionsResolver $resolver): void
    {
        // this defines the available options and their default values when
        // they are not configured explicitly when using the form type
        $resolver->setDefaults([
            'particular_groups' => [],
        ]);

    }

}
