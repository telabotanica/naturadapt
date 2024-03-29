<?php

namespace App\Form;

use App\Entity\Skill;
use App\Entity\User;
use App\Repository\SkillRepository;
use App\Service\FileManager;
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
use Symfony\Component\Validator\Constraints\File;

class UserProfileType extends AbstractType {
	private $fileManager;

	/**
	 * DocumentType constructor.
	 *
	 * @param \App\Service\FileManager $fileManager
	 */
	public function __construct ( FileManager $fileManager) {
		$this->fileManager = $fileManager;
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildForm ( FormBuilderInterface $builder, array $options) {
		$maxFileSize = $this->fileManager->fileUploadMaxSize( '5M' );
		$hasBeenNotified = $options['has_been_notified'];

		$builder
				->add( 'name', TextType::class )
				->add( 'displayname', TextType::class )
				->add( 'avatarfile', FileType::class, [
						'required'    => FALSE,
						'mapped'      => FALSE,
						'attr'        => [ 'data-max-size' => $this->fileManager->formatSize( $maxFileSize ) ],
						'constraints' => [
								new File( [
										'maxSize'          => $maxFileSize,
										'mimeTypes'        => [
												'image/png',
												'image/jpeg',
										],
										'mimeTypesMessage' => 'filetype_incorrect',
								] ),
						],
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
						'attr'     => [ 'maxlength' => 32 ],
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
				->add( 'hasAdaptativeApproach', ChoiceType::class, [
						'required'    => FALSE,
						'expanded'    => TRUE,
						'multiple'    => FALSE,
						'placeholder' => FALSE,
						'choices'     => [
								'forms.user.has_adaptative_approach.labels.' . User::TYPE_HAS_ADAPTATIVE_APPROACH_YES => TRUE,
								'forms.user.has_adaptative_approach.labels.' . User::TYPE_HAS_ADAPTATIVE_APPROACH_NO  => FALSE,
						],
						'attr' => [
							'class' => $hasBeenNotified ? '' : 'adaptative-approach-form-has-not-been-notified',
						],
				] )
				->add ( 'adaptativeApproachDescription', TextType::class, [
					'required' => FALSE,
					'attr'     => [ 'maxlength' => 50 ],
				] )
				->add( 'adaptativeApproachLink', TextType::class, [
					'required' => FALSE
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
				'has_been_notified' => TRUE,
		] );
	}
}
