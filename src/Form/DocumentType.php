<?php

namespace App\Form;

use App\Entity\Document;
use App\Entity\DocumentFolder;
use App\Service\FileManager;
use App\Service\FileMimeManager;
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

		/**
		 * @var Document $document
		 */
		$document = $builder->getData();

		$builder
				->add( 'filefile', FileType::class, [
						'required'    => FALSE,
						'mapped'      => FALSE,
						'attr'        => [ 'data-max-size' => $this->fileManager->formatSize( $maxFileSize ) ],
						'constraints' => [
								new File( [
										'maxSize'          => $maxFileSize,
										'mimeTypes'        => array_merge(
												FileMimeManager::getMimes( FileMimeManager::DOCUMENTS ),
												FileMimeManager::getMimes( FileMimeManager::PDF ),
												FileMimeManager::getMimes( FileMimeManager::IMAGES )
										),
										'mimeTypesMessage' => 'filetype_incorrect',
								] ),
						],
				] )
				->add( 'folderTitle', TextType::class, [
						'data'     => !empty( $document->getFolder() ) ? $document->getFolder()->getTitle() : '',
						'required' => FALSE,
						'mapped'   => FALSE,
						'attr'     => [ 'data-list' => empty( $options[ 'folders' ] )
								? ''
								: implode( ', ', array_map( function ( DocumentFolder $folder ) {
									return $folder->getTitle();
								}, $options[ 'folders' ] ) ),
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
				'folders'    => '',
		] );
	}
}
