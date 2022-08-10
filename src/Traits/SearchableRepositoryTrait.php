<?php

namespace App\Traits;

use Doctrine\ORM\QueryBuilder;


trait SearchableRepositoryTrait
{

	/*
	* @param QueryBuilder &$qb
	* @param string $aliasLetter
	* @param array $groups
	* @param array $particularGroups
	* @param string|null $currentUserId
	*/
	protected function applyGroupsFilters(QueryBuilder &$qb, string $aliasLetter, array $groups, array $particularGroups, $currentUserId)
	{
		/**
		 * GROUPS AND PARTICULAR GROUPS
		 */
		$groupIds = array_merge($groups, $particularGroups);
		$qb->innerJoin($aliasLetter . '.usergroup', 'g');
		if (!empty($groupIds)) {
			$qb->andWhere($qb->expr()->in('g.id', ':groupIds'));
			$qb->setParameter('groupIds', $groupIds);
		}
		$qb->leftJoin('g.logo', 'l');

		/**
		 * PUBLIC OR PRIVATE GROUPS ACCORDING TO CONNEXION
		 */
		if (is_null($currentUserId)) {
			$qb->andWhere($qb->expr()->eq('g.visibility', ':public'));
		} else {
			$qb->leftJoin('g.members', 'gM');

			$qb->andWhere(
				$qb->expr()->orX(
					$qb->expr()->eq('g.visibility', ':public'),
					$qb->expr()->eq('gM.user', ':currentUserId')
				)
			);
			$qb->setParameter('currentUserId', $currentUserId);
		}
		$qb->setParameter('public', 'public');
		$qb->groupBy('n.id');
	}

	/*
	* @param array $ids
	* @param array $groups
	* @param array $particularGroups
	* @param string|null $currentUserId
	* @param array $properties
	* @param array $options
	* @return int
	*/
	public function searchFromIdsAndProperties(array $ids, array $groups, array $particularGroups, $currentUserId, array $properties, $options = [])
	{
		$options = array_merge(['page' => 0, 'limit' => 20], $options);
		$qb = $this->createQueryBuilder('n');
		/**
		 * IDS
		 */
		$qb->andWhere($qb->expr()->in('n.id', ':ids'));
		$qb->setParameter('ids', $ids);

		$editedProperties = [];
		foreach ($properties as $property) {
			switch ($property) {
				case 'usergroup':
					$this->applyGroupsFilters($qb, 'n', $groups, $particularGroups, $currentUserId);
					array_push($editedProperties, 'g.name AS group_name', 'g.slug AS group_slug', 'l.path AS group_logo_path');
					break;
				case 'discussion':
					$qb->innerJoin('n.discussion', 'd');
					$this->applyGroupsFilters($qb, 'd', $groups, $particularGroups, $currentUserId);
					array_push($editedProperties, 'd.title AS discussion_title', 'd.uuid AS discussion_uuid', 'g.name AS group_name', 'g.slug AS group_slug', 'l.path AS group_logo_path');
					break;
				case 'author':
					$qb->innerJoin('n.author', 'a');
					array_push($editedProperties, 'a.displayName AS author_name');
					break;
				case 'name':
					// Name only appears for member category
					$qb->innerJoin('n.usergroupMemberships', 'uM');
					$this->applyGroupsFilters($qb, 'uM', $groups, $particularGroups, $currentUserId);
					array_push($editedProperties, 'n.' . $property);
					// We add a group by to remove user which are in many particulars groups
					$qb->groupBy('n.id');
					break;
				default:
					array_push($editedProperties, 'n.' . $property);
					break;
			}
		}
		return $qb
			->select($editedProperties)
			->setFirstResult($options['limit'] * $options['page'])
			->setMaxResults($options['limit'])
			->getQuery()
			->getResult();
	}

	/*
	* @param array $ids
	* @param array $groups
	* @param array $particularGroups
	* @param string|null $currentUserId
	* @param array $properties
	* @param array $options
	* @return int
	*/
	public function searchCountFromIdsAndProperties(array $ids, array $groups, array $particularGroups, $currentUserId, array $properties, $options = [])
	{
		$qb = $this->createQueryBuilder('n');
		/**
		 * IDS
		 */
		$qb->andWhere($qb->expr()->in('n.id', ':ids'));
		$qb->setParameter('ids', $ids);

		foreach ($properties as $property) {
			switch ($property) {
				case 'usergroup':
					$this->applyGroupsFilters($qb, 'n', $groups, $particularGroups, $currentUserId);
					break;
				case 'discussion':
					$qb->innerJoin('n.discussion', 'd');
					$this->applyGroupsFilters($qb, 'd', $groups, $particularGroups, $currentUserId);
					break;
				case 'author':
					$qb->innerJoin('n.author', 'a');
					break;
					// Name only appears for member category
				case 'name':
					$qb->innerJoin('n.usergroupMemberships', 'uM');
					$this->applyGroupsFilters($qb, 'uM', $groups, $particularGroups, $currentUserId);
					// We add a group by to remove user which are in many particulars groups
					$qb->addGroupBy('n.id');
					break;
				default:
					break;
			}
		}
		$r = count(
			$qb->select('count(distinct n.id) as count')
				->getQuery()
				->getArrayResult()
		);
		return $r;
	}
}
