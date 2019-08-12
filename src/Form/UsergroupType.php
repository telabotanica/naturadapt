<?php

namespace App\Form;

use App\Entity\Usergroup;
use App\Service\FileManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class UsergroupType extends AbstractType {
	private $fileManager;

	/**
	 * DocumentType constructor.
	 *
	 * @param \App\Service\FileManager $fileManager
	 */
	public function __construct ( FileManager $fileManager ) {
		$this->fileManager = $fileManager;
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildForm ( FormBuilderInterface $builder, array $options ) {
		$maxFileSize = $this->fileManager->fileUploadMaxSize( '5M' );

		$builder
				->add( 'name', TextType::class )
				->add( 'logofile', FileType::class, [
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
				->add( 'coverfile', FileType::class, [
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
				->add( 'description', TextareaType::class )
				->add( 'presentation', TextareaType::class, [
						'required' => FALSE,
				] )
				->add( 'visibility', ChoiceType::class, [
						'required'    => TRUE,
						'expanded'    => TRUE,
						'multiple'    => FALSE,
						'placeholder' => FALSE,
						'choices'     => [
								'pages.group.status.' . Usergroup::PUBLIC  => Usergroup::PUBLIC,
								'pages.group.status.' . Usergroup::PRIVATE => Usergroup::PRIVATE,
						],
				] )
				->add( 'submit', SubmitType::class );
	}

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions ( OptionsResolver $resolver ) {
		$resolver->setDefaults( [
				'attr'       => [],
				'data_class' => Usergroup::class,
		] );
	}
}
