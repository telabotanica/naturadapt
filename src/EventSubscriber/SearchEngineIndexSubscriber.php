<?php

namespace App\EventSubscriber;

use App\Entity\Usergroup;
use App\Entity\DiscussionMessage;
use App\Entity\Article;
use App\Entity\Page;
use App\Entity\User;
use App\Entity\Document;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use App\Service\SearchEngineManager;


class SearchEngineIndexSubscriber implements EventSubscriberInterface
{

	public function __construct(SearchEngineManager $searchEngineManager)
	{
		$this->searchEngineManager = $searchEngineManager;
	}


	public function getSubscribedEvents(): array
	{
		return [
			Events::postPersist,
			Events::preRemove,
			Events::postUpdate,
		];
	}


	public function postPersist(LifecycleEventArgs $args): void
	{
		$this->processIndex('persist', $args);
	}

	public function preRemove(LifecycleEventArgs $args): void
	{
		$this->processIndex('remove', $args);
	}

	public function postUpdate(LifecycleEventArgs $args): void
	{
		$this->processIndex('update', $args);
	}


	private function processIndex(string $action, LifecycleEventArgs $args): void
	{
		$entity = $args->getObject();

		if (!$entity instanceof Usergroup && !$entity instanceof DiscussionMessage && !$entity instanceof Document && !$entity instanceof Page && !$entity instanceof User && !$entity instanceof Article) {
			return;
		}
		$this->searchEngineManager->setTNTSearchConfiguration();

		if ($entity instanceof Usergroup) {
			if ($action == 'persist' && !$entity->getIsActive()) {
				return;
			}
			if ($action == 'remove' && !$entity->getIsActive()) {
				return;
			}
			if ($action == 'update') {
				$changes = $args->getEntityManager()->getUnitOfWork()->getEntityChangeSet($args->getObject());

				//if the changes affect the value of 'isActive', we add/remove the entity in the index
				if (isset($changes['isActive'])) {
					//The usergroup is now active, we add the entity in the index
					if ($changes['isActive'][0] == false) {
						$action = 'persist';
					}
					//The usergroup was deactivate, we remove the entity from the index
					else {
						$action = 'remove';
					}
				}
			}
		}
		$this->searchEngineManager->changeIndex($entity, $action);
	}
}
