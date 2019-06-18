<?php

namespace App\Form;

use App\Entity\Usergroup;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UsergroupType extends AbstractType {
	/**
	 * {@inheritdoc}
	 */
	public function buildForm ( FormBuilderInterface $builder, array $options ) {
		$builder
				->add( 'name', TextType::class )
				->add( 'logofile', FileType::class, [
						'required' => FALSE,
						'mapped'   => FALSE,
				] )
				->add( 'coverfile', FileType::class, [
						'required' => FALSE,
						'mapped'   => FALSE,
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
