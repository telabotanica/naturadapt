<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use TeamTNT\TNTSearch\TNTSearch;
use App\Service\SearchEngineManager;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateIndexesCommand extends Command {
	protected static $defaultName = 'search:reindex:all';

	private $searchEngineManager;

	public function __construct ( SearchEngineManager $searchEngineManager ) {
		$this->searchEngineManager = $searchEngineManager;

		parent::__construct();
	}

	protected function configure () {
		$this
				->setDescription( 'Generate all indexes' )
				->setHelp( 'Generate all indexes' );
	}

	protected function execute ( InputInterface $input, OutputInterface $output ) {

		$tnt = new TNTSearch;

		// Obtain and load the configuration that can be generated with the previous described method
		$tnt->loadConfig($this->searchEngineManager->getTNTSearchConfiguration());

		$output->writeln('pages.index generation');
		$indexer = $tnt->createIndex('pages.index');
		$indexer->query('SELECT id, title, body FROM naturadapt_pages;');
		$indexer->run();

		$output->writeln('discussions_messages.index generation');
		$indexer = $tnt->createIndex('discussions_messages.index');
		$indexer->query('SELECT id, body FROM naturadapt_discussion_message;');
		$indexer->run();

		$output->writeln('articles.index generation');
		$indexer = $tnt->createIndex('articles.index');
		$indexer->query('SELECT id, title, body FROM naturadapt_articles;');
		$indexer->run();

		$output->writeln('documents.index generation');
		$indexer = $tnt->createIndex('documents.index');
		$indexer->query('SELECT id, title FROM naturadapt_document;');
		$indexer->run();

		$output->writeln('groups.index generation');
		$indexer = $tnt->createIndex('groups.index');
		$indexer->query('SELECT id, name, description, presentation FROM naturadapt_usergroups;');
		$indexer->run();

		$output->writeln('members.index generation');
		$indexer = $tnt->createIndex('members.index');
		$indexer->query('SELECT id, name, presentation, bio FROM naturadapt_users;');
		$indexer->run();

	}
}
