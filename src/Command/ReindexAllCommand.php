<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use TeamTNT\TNTSearch\TNTSearch;
use App\Service\SearchEngineManager;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReindexAllCommand extends Command {
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

		$indexes=['pages', 'discussions_messages', 'articles', 'documents', 'groups', 'members'];

		foreach($indexes as $index){
			$output->writeln($index.'.index generation');
			$indexer = $tnt->createIndex($index.'.index');
			$indexer->query($this->getQueryFromIndex($index));
			$indexer->run();
		}

	}

	protected function getQueryFromIndex($indexName) {
		switch ($indexName) {
			case 'pages':
				return 'SELECT id, title, body FROM naturadapt_pages;';
			case 'discussions_messages':
				return 'SELECT id, body FROM naturadapt_discussion_message;';
			case 'articles':
				return 'SELECT id, title, body FROM naturadapt_articles;';
			case 'documents':
				return 'SELECT id, title FROM naturadapt_document;';
			case 'groups':
				return 'SELECT id, name, description, presentation FROM naturadapt_usergroups;';
			case 'members':
				return 'SELECT id, name, presentation, bio FROM naturadapt_users;';
			default:
				return '';
		}
	}
}
