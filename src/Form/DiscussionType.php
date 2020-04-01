<?php

namespace App\Form;

use App\Entity\Discussion;
use App\Service\FileManager;
use App\Service\FileMimeManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class DiscussionType extends AbstractType {
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
		$maxFileSize = $this->fileManager->fileUploadMaxSize( '32M' );

		$builder
				->add( 'title', TextType::class )
				->add( 'body', TextareaType::class, [
						'mapped' => FALSE,
				] )
				->add( 'attachment', FileType::class, [
						'required'    => FALSE,
						'mapped'      => FALSE,
						'attr'        => [ 'data-max-size' => $this->fileManager->formatSize( $maxFileSize ) ],
						'constraints' => [
								new File( [
										'maxSize'          => $maxFileSize,
										'mimeTypes'        => array_merge(
												FileMimeManager::getMimes( FileMimeManager::DOCUMENTS ),
												FileMimeManager::getMimes( FileMimeManager::PDF ),
												FileMimeManager::getMimes( FileMimeManager::IMAGES ),
												FileMimeManager::getMimes( FileMimeManager::ARCHIVES )
										),
										'mimeTypesMessage' => 'filetype_incorrect',
								] ),
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
				'data_class' => Discussion::class,
		] );
	}
}
