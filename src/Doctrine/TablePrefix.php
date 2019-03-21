<?php

namespace App\Doctrine;

use Doctrine\Common\EventSubscriber;
use \Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TablePrefix implements EventSubscriber {
	protected $prefix = '';
	protected $container;

	public function __construct ( string $prefix, ContainerInterface $container ) {
		$this->prefix    = $prefix;
		$this->container = $container;
	}

	public function getSubscribedEvents () {
		return [ 'loadClassMetadata' ];
	}

	public function loadClassMetadata ( LoadClassMetadataEventArgs $eventArgs ) {
		$classMetadata = $eventArgs->getClassMetadata ();

		if ( $classMetadata->getTableName () === $this->container->getParameter ( 'doctrine_migrations.table_name' ) ) {
			return;
		}

		if ( !$classMetadata->isInheritanceTypeSingleTable () || $classMetadata->getName () === $classMetadata->rootEntityName ) {
			$classMetadata->setPrimaryTable ( [
													  'name' => $this->prefix . $classMetadata->getTableName (),
											  ] );
		}

		foreach ( $classMetadata->getAssociationMappings () as $fieldName => $mapping ) {
			if ( $mapping[ 'type' ] == \Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_MANY && $mapping[ 'isOwningSide' ] ) {
				$mappedTableName = $mapping[ 'joinTable' ][ 'name' ];

				$classMetadata->associationMappings[ $fieldName ][ 'joinTable' ][ 'name' ] = $this->prefix . $mappedTableName;
			}
		}
	}
}
