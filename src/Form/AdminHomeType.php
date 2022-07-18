<?php

namespace App\Form;

use App\Service\FileManager;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class AdminHomeType extends AbstractType {
	private $fileManager;

	/**
	 * AdminHomeType constructor.
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
			] );
	}

}
