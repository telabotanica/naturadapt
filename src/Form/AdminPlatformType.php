<?php

namespace App\Form;

use App\Service\FileManager;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;


class AdminPlatformType extends AbstractType {
	private $fileManager;

	/**
	 * AdminPlatformType constructor.
	 */
	public function __construct ( FileManager $fileManager ) {
		$this->fileManager = $fileManager;
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildForm ( FormBuilderInterface $builder, array $options ) {
		$maxFileSize = $this->fileManager->fileUploadMaxSize( '500k' );

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
				] )
				->add( 'submit', SubmitType::class );
	}
}
