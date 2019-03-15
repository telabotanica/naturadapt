<?php
/**
 * User: Maxime Cousinou
 * Date: 2019-03-08
 * Time: 11:23
 */

namespace App\Service;

use Doctrine\Common\Persistence\ObjectManager;

class SlugGenerator {
	private $manager;

	public function __construct ( ObjectManager $manager ) {
		$this->manager = $manager;
	}

	/**
	 * Transform (e.g. "Hello World") into a slug (e.g. "hello-world").
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function slugify ( $string ) {
		$rule           = 'NFD; [:Nonspacing Mark:] Remove; NFC';
		$transliterator = \Transliterator::create( $rule );
		$string         = $transliterator->transliterate( $string );
		$string         = strtolower( trim( strip_tags( $string ) ) );
		$string         = preg_replace( '/[^a-z0-9]/', '-', $string );
		$string         = preg_replace( '/-*$/', '', $string );

		return $string;
	}

	public function generateSlug ( $string, $class = FALSE, $slugField = 'slug' ) {
		$slug = SlugGenerator::slugify( $string );

		if ( $class ) {
			$n = 1;
			do {
				$testSlug = $slug . ( ( $n <= 1 ) ? '' : '-' . $n );
				$exists   = $this->manager->getRepository( $class )
										  ->findOneBy( [ $slugField => $testSlug ] );
				$n++;
			} while ( !empty( $exists ) );

			$slug = $testSlug;
		}

		return $slug;
	}
}
