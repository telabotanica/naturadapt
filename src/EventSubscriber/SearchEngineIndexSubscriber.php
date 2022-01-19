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

	public function __construct ( SearchEngineManager $searchEngineManager ) {
		$this->searchEngineManager = $searchEngineManager;
	}


    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist,
            Events::postRemove,
            Events::postUpdate,
        ];
    }


    public function postPersist(LifecycleEventArgs $args): void
    {
        $this->processIndex('persist', $args);
    }

    public function postRemove(LifecycleEventArgs $args): void
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

        if (!$entity instanceof Usergroup ) {
            return;
        }
		$this->searchEngineManager->setTNTSearchConfiguration();

		if($action=='persist' && $entity->getIsActive()){
			$this->searchEngineManager->insertInGroupIndex($entity);
		} else if($action=='remove' && !$entity->getIsActive()){
			$this->searchEngineManager->deleteInGroupIndex($entity);
		} else if($action=='update'){
			$changes = $args->getEntityManager()->getUnitOfWork()->getEntityChangeSet($args->getObject());
			// If the change concern isActive, we have to check if it is now true(insert) or false(remove)
			if (isset($changes['isActive'])){
				if($changes['isActive'][0]==false){
					$this->searchEngineManager->insertInGroupIndex($entity);
				}else{
					$this->searchEngineManager->deleteInGroupIndex($entity);
				}
			} else {
				$this->searchEngineManager->updateInGroupIndex($entity);
			}
		} else {
			return;
		}
    }
}
