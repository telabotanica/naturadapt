<?php

namespace App\Form;

use App\Service\FileManager;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\File;

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
		$maxFileSize = $this->fileManager->fileUploadMaxSize( '500k' );

		$builder
			->add( 'frontfile', FileType::class, [
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
			->add( 'sur_titre', TextType::class, [
				'required' => FALSE,
				'data' => $options['data']['sur_titre'],
			] )
			->add( 'texte_principal', TextType::class, [
				'required' => FALSE,
				'data' => $options['data']['texte_principal'],
			] )
			->add( 'premier_texte', TextareaType::class, [
				'required' => FALSE,
				'data' => $options['data']['premier_texte'],
			] )
			->add( 'premier_clique_visibility', ChoiceType::class, [
				'required'    => TRUE,
				'expanded'    => TRUE,
				'multiple'    => FALSE,
				'placeholder' => FALSE,
				'choices'     => [
						'oui'  => TRUE,
						'non' => FALSE,
				],
				'data' => $options['data']['premier_clique_visibility'],
			] )
			->add( 'premier_clique_texte', TextType::class, [
				'required' => FALSE,
				'data' => $options['data']['premier_clique_texte'],
			] )
			->add( 'premier_clique_bouton_texte', TextType::class, [
				'required' => FALSE,
				'data' => $options['data']['premier_clique_bouton_texte'],
			] )
			->add( 'premier_clique_lien', TextType::class, [
				'required' => FALSE,
				'data' => $options['data']['premier_clique_lien'],
			] )
			->add( 'second_texte', TextareaType::class, [
				'required' => FALSE,
				'data' => $options['data']['second_texte'],
			] )
			->add( 'second_clique_visibility', ChoiceType::class, [
				'required'    => TRUE,
				'expanded'    => TRUE,
				'multiple'    => FALSE,
				'placeholder' => FALSE,
				'choices'     => [
						'oui'  => TRUE,
						'non'  => FALSE,
				],
				'data' => $options['data']['second_clique_visibility'],
			] )
			->add( 'second_clique_texte', TextType::class, [
				'required' => FALSE,
				'data' => $options['data']['second_clique_texte'],
			] )
			->add( 'second_clique_bouton_texte', TextType::class, [
				'required' => FALSE,
				'data' => $options['data']['second_clique_bouton_texte'],
			] )
			->add( 'second_clique_lien', TextType::class, [
				'required' => FALSE,
				'data' => $options['data']['second_clique_lien'],
			] )
			->add( 'troisieme_texte', TextareaType::class, [
				'required' => FALSE,
				'data' => $options['data']['troisieme_texte'],
			] )
			->add( 'submit', SubmitType::class );
	}

}
