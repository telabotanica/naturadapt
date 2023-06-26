<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Form\AdminLinkType;
use App\Entity\AppLinkGroup;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class AdminMenusType extends AbstractType {
	/**
	 * UserEmailType constructor.
	 */
	public function __construct () {
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildForm ( FormBuilderInterface $builder, array $options ) {

		$builder
			->add( 'navbarLiens', CollectionType::class, [
				'entry_type' => AdminLinkType::class,
				'allow_add' => true,
				'allow_delete' => true,
				'delete_empty' => true
			] )
			->add( 'footbarFirstLiensTitle', TextType::class, [
				'required' => FALSE,
			] )
			->add( 'footbarFirstLiens', CollectionType::class, [
				'entry_type' => AdminLinkType::class,
				'allow_add' => true,
				'allow_delete' => true,
				'delete_empty' => true
			] )
			->add( 'footbarSecondLiensTitle', TextType::class, [
				'required' => FALSE,
			] )
			->add( 'footbarSecondLiens', CollectionType::class, [
				'entry_type' => AdminLinkType::class,
				'allow_add' => true,
				'allow_delete' => true,
				'delete_empty' => true
			] )
			->add( 'footbarThirdLiensTitle', TextType::class, [
				'required' => FALSE,
			] )
			->add( 'footbarThirdLiens', CollectionType::class, [
				'entry_type' => AdminLinkType::class,
				'allow_add' => true,
				'allow_delete' => true,
				'delete_empty' => true
			] )
			->add( 'submit', SubmitType::class )
			->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event){
				$forms = $event->getData();
				foreach($forms as $formTitle=>$formValue) {
					if(is_array($formValue)){
						foreach($formValue as $lienIndex=>$lien) {
							if(!isset($lien['nom']) || $lien['nom'] == "")
								unset($forms[$formTitle][$lienIndex]);
						}
					}
				}
				$event->setData($forms);

			});


	}

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions ( OptionsResolver $resolver ) {
		$resolver->setDefaults( [
				'data_class' => AppLinkGroup::class,
		] );
	}
}
