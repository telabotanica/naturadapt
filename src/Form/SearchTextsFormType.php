<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;

class SearchTextsFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
			->add( 'query', SearchType::class, [
				'required' => false,
				] )
			->add( 'current_tags', ChoiceType::class, [
				'required'                  => false,
				'expanded'                  => true,
				'multiple'                  => true,
				'choices'  => $options['tag_array'],
				'choice_attr' => function() {
					return ['checked' => 'checked'];
				},
				] );
    }

	public function configureOptions(OptionsResolver $resolver): void
    {
        // this defines the available options and their default values when
        // they are not configured explicitly when using the form type
        $resolver->setDefaults([
            'tag_array' => [],
        ]);

    }

}
