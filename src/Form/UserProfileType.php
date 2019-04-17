<?php
/**
 * User: Maxime Cousinou
 * Date: 2019-04-17
 * Time: 18:04
 */

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserProfileType extends AbstractType {
	/**
	 * {@inheritdoc}
	 */
	public function buildForm ( FormBuilderInterface $builder, array $options ) {
		$builder
				->add( 'name', TextType::class )
				->add( 'displayname', TextType::class )
				->add( 'avatarfile', FileType::class, [ 'required' => FALSE, 'mapped' => FALSE ] )
				->add( 'submit', SubmitType::class );
	}

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions ( OptionsResolver $resolver ) {
		$resolver->setDefaults( [
										'attr'       => [],
										'data_class' => User::class,
								] );
	}
}
