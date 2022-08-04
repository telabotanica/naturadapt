<?php

namespace App\Form;

use App\Entity\AppLink;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminLinkType extends AbstractType {
	/**
	 * UserEmailType constructor.
	 */
	public function __construct () {
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildForm ( FormBuilderInterface $builder, array $options ) {

		print('aaa');

		// print($options);
		$builder
			->add( 'nom', TextType::class, [
				'required' => FALSE,
			] )
			->add( 'lien', TextType::class, [
				'required' => FALSE,
			] );
		}

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions ( OptionsResolver $resolver ) {
		$resolver->setDefaults( [
				'attr'       => [],
				'data_class' => AppLink::class,
		] );
	}
}
