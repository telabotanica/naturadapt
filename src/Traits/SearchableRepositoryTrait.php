<?php

namespace App\Traits;

use Doctrine\ORM\QueryBuilder;


trait SearchableRepositoryTrait
{

	protected function applyGroupsFilters ( QueryBuilder &$qb, string $aliasLetter, array $groups, array $particularGroups )
	{
		/**
		 * GROUPS AND PARTICULAR GROUPS
		 */
		$groupIds = array_merge($groups, $particularGroups);
		$qb->innerJoin( $aliasLetter.'.usergroup', 'g' );
		if ( !empty( $groupIds ) ) {
			$qb->andWhere( $qb->expr()->in( 'g.id', ':groupIds' ) );
			$qb->setParameter('groupIds', $groupIds );
		}
		$qb->innerJoin( 'g.logo', 'l' );
	}

	public function searchFromIdsAndProperties ( array $ids, array $groups, array $particularGroups, array $properties, $options = [] )
	{
		$options = array_merge( [ 'page' => 0, 'limit' => 20 ], $options );
		$qb = $this->createQueryBuilder( 'n' );
		/**
		 * IDS
		 */
		$qb->andWhere( $qb->expr()->in( 'n.id', ':ids' ) );
		$qb->setParameter('ids', $ids);

		$editedProperties = [];
		foreach ($properties as $property) {
			switch ($property) {
				case 'usergroup':
					$this->applyGroupsFilters( $qb, 'n', $groups, $particularGroups );
					array_push($editedProperties, 'g.name AS group_name', 'g.slug AS group_slug', 'l.path AS group_logo_path');
					break;
				case 'discussion':
					$qb->innerJoin( 'n.discussion', 'd' );
					$this->applyGroupsFilters( $qb, 'd', $groups, $particularGroups );
					array_push($editedProperties, 'd.title AS discussion_title', 'd.uuid AS discussion_uuid', 'g.name AS group_name', 'g.slug AS group_slug', 'l.path AS group_logo_path');
					break;
				case 'author':
					$qb->innerJoin( 'n.author', 'a' );
					array_push($editedProperties, 'a.displayName AS author_name');
					break;
				// Name only appears for member category
				case 'name':
					$qb->innerJoin( 'n.usergroupMemberships', 'uM' );
					$this->applyGroupsFilters( $qb, 'uM', $groups, $particularGroups );
					array_push($editedProperties, 'n.'.$property);
					// We add a group by to remove user which are in many particulars groups
					$qb->groupBy('n.id');
					break;
				default:
					array_push($editedProperties, 'n.'.$property);
					break;
			}
		}
		return $qb
			->select($editedProperties)
			->setFirstResult( $options[ 'limit' ] * $options[ 'page' ] )
			->setMaxResults( $options[ 'limit' ] )
			->getQuery()
			->getResult();
	}

	public function searchCountFromIdsAndProperties ( array $ids, array $groups, array $particularGroups, array $properties, $options = [] ) {
		$qb = $this->createQueryBuilder( 'n' );
		/**
		 * IDS
		 */
		$qb->andWhere( $qb->expr()->in( 'n.id', ':ids' ) );
		$qb->setParameter('ids', $ids);

		foreach ($properties as $property) {
			switch ($property) {
				case 'usergroup':
					$this->applyGroupsFilters( $qb, 'n', $groups, $particularGroups );
					break;
				case 'discussion':
					$qb->innerJoin( 'n.discussion', 'd' );
					$this->applyGroupsFilters( $qb, 'd', $groups, $particularGroups );
					break;
				case 'author':
					$qb->innerJoin( 'n.author', 'a' );
					break;
				// Name only appears for member category
				case 'name':
					$qb->innerJoin( 'n.usergroupMemberships', 'uM' );
					$this->applyGroupsFilters( $qb, 'uM', $groups, $particularGroups );
					// We add a group by to remove user which are in many particulars groups
					$qb->addGroupBy('n.id');
					break;
				default:
					break;
			}
		}
		// If name is in the properties, we are working with the member category.
		// In that case the request is different: we want all the users which are at least one time in the filterGroupList, so we need distinct
		if(in_array('name', $properties)){
			$r = count(
				$qb->select('count(distinct n.id) as count')
				->getQuery()
				->getArrayResult()
			);
		} else {
			$r = intval(
				$qb->select('COUNT(n)')
				->getQuery()
				->getSingleScalarResult()
			);
		}
		return $r;
	}
}
