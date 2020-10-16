<?php

namespace App\Command;

use App\Entity\Skill;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportSkillsCommand extends Command {
	protected static $defaultName = 'import:skills';

	private $manager;

	public function __construct ( EntityManagerInterface $manager ) {
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
		 * @var EntityManagerInterface $manager
		 */
		$manager = $this->manager;

		$skillsRepository = $manager->getRepository( Skill::class );

		$slugs = [
				'botany',
				'bryology',
				'mycology',
				'lichenology',
				'entomology',
				'mammalogy',
				'ichthyology',
				'ornithology',
				'herpetology',
				'malacology',
				'arachnology',
				'carcinology',
				'cetology',
				'pedology',
				'geology',
				'oceanography',
				'hydrology',
				'hydromorphology',
				'climatology',
				'sociology',
				'data-management',
				'statistics',
				'cartography',
				'web-development',
				'communication',
				'accounting',
				'network-animation',
				'concertation-of-actors',
				'environmental-law',
				'environmental-education',
				'project-management',
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
