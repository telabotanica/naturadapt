<?php
/**
 * User: Maxime Cousinou
 * Date: 2019-04-17
 * Time: 18:04
 */

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
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
				->add( 'avatarfile', FileType::class, [
						'required' => FALSE,
						'mapped'   => FALSE,
				] )
				->add( 'city', TextType::class, [
						'required' => FALSE,
				] )
				->add( 'zipcode', TextType::class, [
						'required' => FALSE,
				] )
				->add( 'country', TextType::class, [
						'required' => FALSE,
				] )
				->add( 'presentation', TextType::class, [
						'required' => FALSE,
				] )
				->add( 'bio', TextareaType::class, [
						'required' => FALSE,
				] )
				->add( 'inscriptionType', ChoiceType::class, [
						'required'    => FALSE,
						'expanded'    => TRUE,
						'multiple'    => FALSE,
						'placeholder' => FALSE,
						'choices'     => [
								'forms.inscription_type.labels.' . User::TYPE_PRIVATE       => User::TYPE_PRIVATE,
								'forms.inscription_type.labels.' . User::TYPE_PROFESSIONNAL => User::TYPE_PROFESSIONNAL,
						],
				] )
				->add( 'site', TextType::class, [ 'required' => FALSE ] )
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
