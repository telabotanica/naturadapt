<?php

namespace App\Form;

use App\Entity\Document;
use App\Service\FileManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class DocumentType extends AbstractType {
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
				->add( 'filefile', FileType::class, [
						'required'    => FALSE,
						'mapped'      => FALSE,
						'attr'        => [ 'data-max-size' => $this->fileManager->formatSize( $maxFileSize ) ],
						'constraints' => [
								new File( [
										'maxSize'          => $maxFileSize,
										'mimeTypes'        => [
												'application/pdf',
												'application/x-pdf',
												'application/msword',
												'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
												'application/vnd.ms-excel',
												'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
												'application/vnd.oasis.opendocument.text',
												'application/vnd.oasis.opendocument.spreadsheet',
												'image/gif',
												'image/png',
												'image/jpeg',
										],
										'mimeTypesMessage' => 'filetype_incorrect',
								] ),
						],
				] )
				->add( 'title', TextType::class, [
						'required' => FALSE,
				] )
				->add( 'submit', SubmitType::class );
	}

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions ( OptionsResolver $resolver ) {
		$resolver->setDefaults( [
				'attr'       => [],
				'data_class' => Document::class,
		] );
	}
}
