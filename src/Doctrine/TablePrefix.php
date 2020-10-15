<?php

namespace App\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class TablePrefix implements EventSubscriber {
	protected $prefix = '';
	protected $params;

	public function __construct ( string $prefix, ParameterBagInterface $params ) {
		$this->prefix    = $prefix;
		$this->params = $params;
	}

	public function getSubscribedEvents () {
		return [ 'loadClassMetadata' ];
	}

	public function loadClassMetadata ( LoadClassMetadataEventArgs $eventArgs ) {
		$classMetadata = $eventArgs->getClassMetadata();

		if ( $classMetadata->getTableName() === $this->params->get( 'migrations_table_name' ) ) {
			return;
		}

		if ( !$classMetadata->isInheritanceTypeSingleTable() || $classMetadata->getName() === $classMetadata->rootEntityName ) {
			$classMetadata->setPrimaryTable( [
													 'name' => $this->prefix . $classMetadata->getTableName(),
											 ] );
		}

		foreach ( $classMetadata->getAssociationMappings() as $fieldName => $mapping ) {
			if ( $mapping[ 'type' ] == \Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_MANY && $mapping[ 'isOwningSide' ] ) {
				$mappedTableName = $mapping[ 'joinTable' ][ 'name' ];

				$classMetadata->associationMappings[ $fieldName ][ 'joinTable' ][ 'name' ] = $this->prefix . $mappedTableName;
			}
		}
	}
}
