<?php

namespace App\Form;

use App\Entity\Skill;
use App\Entity\User;
use App\Repository\SkillRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserProfileType extends AbstractType {
	/**
	 * {@inheritdoc}
	 */
	public function buildForm ( FormBuilderInterface $builder, array $options ) {
		$builder
				->add( 'name', TextType::class )
				->add( 'displayname', TextType::class )
				->add( 'avatarfile', FileType::class, [
						'required' => FALSE,
						'mapped'   => FALSE,
				] )
				->add( 'city', TextType::class, [
						'required' => FALSE,
				] )
				->add( 'zipcode', TextType::class, [
						'required' => FALSE,
				] )
				->add( 'country', CountryType::class, [
						'required' => FALSE,
				] )
				->add( 'latitude', HiddenType::class, [
						'required' => FALSE,
				] )
				->add( 'longitude', HiddenType::class, [
						'required' => FALSE,
				] )
				->add( 'presentation', TextType::class, [
						'required' => FALSE,
				] )
				->add( 'bio', TextareaType::class, [
						'required' => FALSE,
				] )
				->add( 'inscriptionType', ChoiceType::class, [
						'required'    => FALSE,
						'expanded'    => TRUE,
						'multiple'    => FALSE,
						'placeholder' => FALSE,
						'choices'     => [
								'forms.user.inscription_type.labels.' . User::TYPE_PRIVATE       => User::TYPE_PRIVATE,
								'forms.user.inscription_type.labels.' . User::TYPE_PROFESSIONNAL => User::TYPE_PROFESSIONNAL,
						],
				] )
				->add( 'skills', EntityType::class, [
						'class'                     => Skill::class,
						'required'                  => FALSE,
						'expanded'                  => TRUE,
						'multiple'                  => TRUE,
						'query_builder'             => function ( SkillRepository $repository ) {
							return $repository->createQueryBuilder( 'u' )
											  ->orderBy( 'u.slug', 'ASC' );
						},
						'choice_translation_domain' => 'skills',
						'choice_label'              => 'slug',
				] )
				->add( 'siteName', TextType::class, [
						'required' => FALSE,
						'mapped'   => FALSE,
				] )
				->add( 'submit', SubmitType::class );
	}

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions ( OptionsResolver $resolver ) {
		$resolver->setDefaults( [
										'attr'       => [],
										'data_class' => User::class,
								] );
	}
}
