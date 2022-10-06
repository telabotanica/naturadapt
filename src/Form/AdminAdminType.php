<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminAdminType extends AbstractType {
	/**
	 * UserEmailType constructor.
	 */
	public function __construct () {
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildForm ( FormBuilderInterface $builder, array $options ) {
		// $builder
		// 		->add( 'email_new', EmailType::class )
		// 		->add( 'password', PasswordType::class )
		// 		->add( 'submit', SubmitType::class );
	}

	// /**
	//  * {@inheritdoc}
	//  */
	// public function configureOptions ( OptionsResolver $resolver ) {
	// 	$resolver->setDefaults( [
	// 			'attr'       => [],
	// 			'data_class' => User::class,
	// 	] );
	// }
}
