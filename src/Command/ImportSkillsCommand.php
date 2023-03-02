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
				'beekeeping',
				'gardening',
				'permaculture',
				'nutrition',
				'photography',
				'video',
				'botany',
				'mycology',
				'bryology',
				'lichenology',
				'entomology',
				'hymenoptera',
				'lepidoptera',
				'diptera',
				'beetles',
				'saproxylo',
				'mammalogy',
				'ichthyology',
				'ornithology',
				'herpetology',
				'batrachology',
				'malacology',
				'arachnology',
				'carcinology',
				'ketology',
				'market-gardening',
				'viticulture',
				'arboriculture',
				'field-crops',
				'agroforestry',
				'mixed-farming',
				'agricultural-techniques-plant-cover',
				'management-techniques-for-grass-and-flower-strips',
				'hedge-management-techniques-in-agriculture',
				'animal-husbandry',
				'technical-landscape-management',
				'landscape-design',
				'hedge-planting',
				'soil-life',
				'renaturation',
				'pond-management',
				'wetland-management',
				'rewilding',
				'pedology',
				'geology',
				'oceanography',
				'hydrology',
				'hydromorphology',
				'climatology',
				'sociology',
				'ethnology',
				'ethology',
				'data-management',
				'statistics',
				'fundamental-research',
				'applied-research',
				'cartography',
				'web-development',
				'communication',
				'accounting',
				'network-facilitation',
				'stakeholder-consultation',
				'environmental-law',
				'environmental-education-eedd',
				'project-set-up-and-management',
				'science-popularization',
				'mediation',
				'adult-education',
				'renaturation-of-anthropised-areas',
				'apagogy',
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
