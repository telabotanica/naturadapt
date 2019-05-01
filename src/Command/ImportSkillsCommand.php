<?php
/**
 * User: Maxime Cousinou
 * Date: 2019-03-04
 * Time: 13:30
 */

namespace App\Command;

use App\Entity\CouponActionLabel;
use App\Entity\CouponStatusLabel;
use App\Entity\Skill;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportSkillsCommand extends Command {
	protected static $defaultName = 'import:skills';

	private $manager;

	public function __construct ( ObjectManager $manager ) {
		$this->manager = $manager;

		parent::__construct();
	}

	protected function configure () {
		$this
				->setDescription( 'Import skills' )
				->setHelp( 'Import skills' );
	}

	protected function execute ( InputInterface $input, OutputInterface $output ) {
		/**
		 * @var ObjectManager $manager
		 */
		$manager = $this->manager;

		$skillsRepository = $manager->getRepository( Skill::class );

		$slugs = [
				'botany',
				'mycology',
				'bryology',
		];

		foreach ( $slugs as $slug ) {
			if ( !$skillsRepository->findOneBy( [ 'slug' => $slug ] ) ) {
				$output->writeln( 'Creating ' . $slug );
				$skill = new Skill();
				$skill->setSlug( $slug );

				$manager->persist( $skill );
			}
			else {
				$output->writeln( 'Skipping ' . $slug . ' (exists)' );
			}
		}

		$manager->flush();
		$output->writeln( 'Done.' );
	}
}
