<?php

namespace App\Twig;

use Symfony\Component\Intl\Intl;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class CountryNameExtension extends AbstractExtension {
	public function getFilters () {
		return [
				new TwigFilter( 'countryName', [ $this, 'countryFromCode' ] ),
		];
	}

	public function countryFromCode ( $countryCode ) {
		return Intl::getRegionBundle()->getCountryName( $countryCode );
	}
}
