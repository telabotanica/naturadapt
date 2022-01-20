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
					'search.form.result_types.pages' => 'pages',
					'search.form.result_types.discussions' => 'discussions',
					'search.form.result_types.articles' => 'actualites',
					'search.form.result_types.documents' => 'documents',
					'search.form.result_types.members' => 'membres'
				)
			] )
			->add( 'groups', ChoiceType::class, [
				'required' => false,
				'multiple' => false,
				'expanded' => true,
				'choices'  => [
					'search.form.groups.all_groups' => 'all',
					'search.form.groups.my_groups' => 'My Groups'
				],
				'data' => 'all',
				'placeholder' => false
				] )
			->add( 'particularGroup', EntityType::class, [
				'class'                     => Usergroup::class,
				'required'                  => false,
				'expanded'                  => true,
				'multiple'                  => true,
				'query_builder'             => function ( UsergroupRepository $repository ) {
					return $repository->createQueryBuilder( 'u' )
										->orderBy( 'u.slug', 'ASC' );
				},
				'choice_label'              => 'slug',
			] );
    }

}
