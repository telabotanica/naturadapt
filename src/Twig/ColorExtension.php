<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class ColorExtension extends AbstractExtension {
	public function getFilters () {
		return [
				new TwigFilter( 'color', [ $this, 'generateFromString' ] ),
		];
	}

	public function generateFromString ( $string ) {
		$c = array_reduce( str_split( substr( $string, 0, 16 ) ), function ( $carry, $c ) {
			return ( $carry + ord( $c ) ) % 256;
		}, 0 );

		return 'hsl(' . $c . ', 80%, 60%)';
	}
}
