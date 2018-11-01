<?php
/*******************************************************************************************************
*                                                                                                      *
* COPYRIGHT NOTICE                                                                                     *
* Unpublished Proprietary Work Copyright 2016 by Blue Coat Systems, Inc., All Rights Reserved. This    *
* software and documentation are an unpublished proprietary work of Blue Coat Systems and are protected*
* by United States copyright laws and international treaties. This work may not be disclosed, copied,  *
* reproduced, translated, modified, compiled, reduced to machine-readable form or distributed in any   *
* manner without the express written permission of Blue Coat Systems, Inc.                             *
*                                                                                                      *
* GOVERNMENT RESTRICTED RIGHTS NOTICE                                                                  *
* This software and documentation are provided with restricted rights. Use, duplication or disclosure  *
* by the Government is subject to restrictions as set forth in subparagraph (c)(2)(ii) of the Rights   *
* in Technical Data and Computer Software clause at DFARS 252.227-7013 or subparagraphs (c)(1) and (2) *
* of the Commercial Computer Software - Restricted Rights at 48 CFR 52.227-19, as applicable.          *
* Contractor/manufacturer is Blue Coat Systems, Inc. 420 N Mary Ave, Sunnyvale, CA 94085.              *
*                                                                                                      *
*                                                                                                      *
*******************************************************************************************************/
use APIv6 as Latest;

class BladeDbRevision extends DbRevision {

	/**
	 * get a new uuid
	 *
	 * @return uuid
	 */
	protected function getUuid() {
		$uuid = CakeText::uuid();
		return $uuid;
	}

	/**
	 *
	 * @param type $count
	 * @return type
	 */
	protected function getQuestionMarks($count) {
		$questionMarks = array_fill(0, $count, '?');
		$result = implode(',', $questionMarks);
		return $result;
	}

	/**
	 *
	 * @param type $arrayParam
	 * @return string
	 */
	protected function convertBooleans($arrayParam) {
		$result = array();
		foreach ($arrayParam as $key => $value) {
			$quotedValue = $value;
			if (is_bool($value)) {
				if ($value) {
					$quotedValue = 'TRUE';
				} else {
					$quotedValue = 'FALSE';
				}
			}
			$result[$key] = $quotedValue;
		}
		return $result;
	}

	/**
	 * gets the favorite by uuid
	 */
	public function getDeepseeFavoriteFromUuid($favoriteUuid) {
		$favorite = array();
		$statement = '
			SELECT *
			FROM deepsee_favorites
			WHERE uuid = ?
			LIMIT 1
		';
		$params = array(
			$favoriteUuid
		);
		$favoritesResults = $this->query($statement, $params, false);
		if ($favoritesResults) {
			foreach ($favoritesResults as $favoritesResult) {
				if (isset($favoritesResult[0])) {
					$favorite = $favoritesResult[0];
				}
			}
		}
		return $favorite;
	}

	/**
	 * gets the favorite by name
	 */
	public function getDeepseeFavoriteFromName($name) {
		$favorite = array();
		$statement = '
			SELECT *
			FROM deepsee_favorites
			WHERE name = ?
			LIMIT 1
		';
		$params = array(
			$name
		);
		$favoritesResults = $this->query($statement, $params, false);
		if ($favoritesResults) {
			foreach ($favoritesResults as $favoritesResult) {
				if (isset($favoritesResult[0])) {
					$favorite = $favoritesResult[0];
				}
			}
		}
		return $favorite;
	}

	protected function getDeepseeFavoriteUuidsCascaded($favoriteUuid, $includeParent = true) {
		$favoriteUuids = array();
		if ($includeParent) {
			$favoriteUuids[] = $favoriteUuid;
		}

		$favoriteUuidsCascaded = array();
		$favoriteUuidsThatUseThisFavorite = $this->getAllDeepseeFavoriteUuidsThatUseFavorite($favoriteUuid);
		foreach ($favoriteUuidsThatUseThisFavorite as $favoriteUuidThatUseThisFavorite) {
			$favoriteUuidsCascaded = $this->getDeepseeFavoriteUuidsCascaded($favoriteUuidThatUseThisFavorite);
			$favoriteUuids = array_merge($favoriteUuidsCascaded, $favoriteUuids);
		}
		return $favoriteUuids;
	}

	protected function getAllDeepseeFavoriteUuidsThatUseFavorite($favoriteUuid) {
		$favoriteUuids = array();

		if ($favoriteUuid) {
			$equalFavoriteUuid = '%favorite=' . $favoriteUuid . '%';
			$notEqualFavoriteUuid = '%favorite!=' . $favoriteUuid . '%';

			$statement = '
				SELECT uuid
				FROM deepsee_favorites
				WHERE
				value LIKE ?
				OR value LIKE ?
			';
			$params = array(
				$equalFavoriteUuid,
				$notEqualFavoriteUuid,
			);
			$results = $this->query($statement, $params, false);
			if ($results) {
				foreach ($results as $result) {
					if (isset($result[0]['uuid'])) {
						$favoriteUuids[] = $result[0]['uuid'];
					}
				}
			}
		}
		return $favoriteUuids;
	}


	/**
	 * gets the action by name
	 */
	protected function getActionFromName($name) {
		$action = array();
		$statement = '
			SELECT *
			FROM actions
			WHERE name = ?
			LIMIT 1
		';
		$params = array(
			$name
		);
		$actionsResults = $this->query($statement, $params, false);
		if ($actionsResults) {
			foreach ($actionsResults as $actionsResult) {
				if (isset($actionsResult[0])) {
					$action = $actionsResult[0];
				}
			}
		}
		return $action;
	}

	/**
	 * gets the action filter relations by action_uuid
	 */
	public function getActionFilterRelationsFromActionUuid($actionUuid) {
		$actionFilterRelations = array();
		$statement = '
			SELECT *
			FROM action_filter_relations
			WHERE action_uuid = ?
		';
		$params = array(
			$actionUuid
		);
		$results = $this->query($statement, $params, false);
		if ($results) {
			foreach ($results as $result) {
				if (isset($result[0])) {
					$actionFilterRelations[] = $result[0];
				}
			}
		}
		return $actionFilterRelations;
	}

	/**
	 * gets the favorite events by name
	 */
	public function getDeepseeFavoriteEventsFromName($name) {
		$favoriteEvents = array();
		$statement = '
			SELECT dfe.*
			FROM deepsee_favorites AS df
			INNER JOIN deepsee_favorite_events AS dfe ON dfe.deepsee_favorite_uuid = df.uuid
			WHERE df.name = ?
		';
		$params = array(
			$name
		);
		$favoriteEventsResults = $this->query($statement, $params, false);
		if ($favoriteEventsResults) {
			foreach ($favoriteEventsResults as $favoriteEventsResult) {
				if (isset($favoriteEventsResult[0])) {
					$favoriteEvents = $favoriteEventsResult[0];
				}
			}
		}
		return $favoriteEvents;
	}

	/**
	 * gets the deepsee favorite action by name
	 */
	protected function getDeepseeFavoriteActionFromName($favoriteName, $actionName) {
		$favorite = $this->getDeepseeFavoriteFromName($favoriteName);
		$action = $this->getActionFromName($actionName);

		$favoriteUuid = null;
		if (isset($favorite['uuid'])) {
			$favoriteUuid = $favorite['uuid'];
		}

		$actionUuid = null;
		if (isset($action['uuid'])) {
			$actionUuid = $action['uuid'];
		}

		$deepseeFavoriteAction = array();

		if ($favoriteUuid && $actionUuid) {
			$statement = '
				SELECT *
				FROM
					deepsee_favorite_actions
				WHERE
					deepsee_favorite_uuid = ?
					AND action_uuid = ?
				LIMIT 1
			';
			$params = array(
				$favoriteUuid,
				$actionUuid
			);
			$deepseeFavoriteActionResults = $this->query($statement, $params, false);
			if ($deepseeFavoriteActionResults) {
				foreach ($deepseeFavoriteActionResults as $deepseeFavoriteActionResult) {
					if (isset($deepseeFavoriteActionResult[0])) {
						$deepseeFavoriteAction = $deepseeFavoriteActionResult[0];
					}
				}
			}
		}
		return $deepseeFavoriteAction;
	}

	/**
	 * gets the deepsee favorite actions by deepsee favorite uuid
	 */
	protected function getDeepseeFavoriteActionsFromDeepseeFavoriteUuid($deepseeFavoriteUuid) {
		$deepseeFavoriteActions = array();

		if ($deepseeFavoriteUuid) {
			$statement = '
				SELECT *
				FROM
					deepsee_favorite_actions
				WHERE
					deepsee_favorite_uuid = ?
			';
			$params = array(
				$deepseeFavoriteUuid
			);
			$deepseeFavoriteActionResults = $this->query($statement, $params, false);
			if ($deepseeFavoriteActionResults) {
				foreach ($deepseeFavoriteActionResults as $deepseeFavoriteActionResult) {
					if (isset($deepseeFavoriteActionResult[0])) {
						$deepseeFavoriteActions[] = $deepseeFavoriteActionResult[0];
					}
				}
			}
		}
		return $deepseeFavoriteActions;
	}

	/**
	 * gets the action integration provider by name
	 */
	protected function getActionIntegrationProviderFromName($actionName, $integrationProviderName) {
		$action = $this->getActionFromName($actionName);
		$integrationProvider = $this->getIntegrationProviderFromName($integrationProviderName);

		$actionUuid = null;
		if (isset($action['uuid'])) {
			$actionUuid = $action['uuid'];
		}

		$integrationProviderUuid = null;
		if (isset($integrationProvider['uuid'])) {
			$integrationProviderUuid = $integrationProvider['uuid'];
		}

		$actionIntegrationProvider = array();

		if ($integrationProviderUuid && $actionUuid) {
			$statement = '
				SELECT *
				FROM
					actions_integration_providers
				WHERE
					integration_provider_uuid = ?
					AND action_uuid = ?
				LIMIT 1
			';
			$params = array(
				$integrationProviderUuid,
				$actionUuid,
			);
			$actionIntegrationProviderResults = $this->query($statement, $params, false);
			if ($actionIntegrationProviderResults) {
				foreach ($actionIntegrationProviderResults as $actionIntegrationProviderResult) {
					if (isset($actionIntegrationProviderResult[0])) {
						$actionIntegrationProvider = $actionIntegrationProviderResult[0];
					}
				}
			}
		}
		return $actionIntegrationProvider;
	}

	/**
	 * gets the integration provider category type by name
	 */
	protected function getIntegrationProviderCategoryTypeFromName($categoryName, $typeName) {
		$category = $this->getIntegrationProviderCategoryFromName($categoryName);
		$type = $this->getIntegrationProviderTypeFromName($typeName);

		$categoryUuid = null;
		if (isset($category['uuid'])) {
			$categoryUuid = $category['uuid'];
		}

		$typeUuid = null;
		if (isset($type['uuid'])) {
			$typeUuid = $type['uuid'];
		}

		$integrationProviderCategoryType = array();

		if ($categoryUuid && $typeUuid) {
			$statement = '
				SELECT *
				FROM
					integration_provider_categories_integration_provider_types
				WHERE
					integration_provider_category_uuid = ?
					AND integration_provider_type_uuid = ?
				LIMIT 1
			';
			$params = array(
				$categoryUuid,
				$typeUuid
			);
			$integrationProviderCategoryTypeResults = $this->query($statement, $params, false);
			if ($integrationProviderCategoryTypeResults) {
				foreach ($integrationProviderCategoryTypeResults as $integrationProviderCategoryTypeResult) {
					if (isset($integrationProviderCategoryTypeResult[0])) {
						$integrationProviderCategoryType = $integrationProviderCategoryTypeResult[0];
					}
				}
			}
		}
		return $integrationProviderCategoryType;
	}

	/**
	 * gets the integration provider tonic action by name
	 */
	protected function getIntegrationProviderTonicActionFromName($integrationProviderName, $tonicActionName) {
		$integrationProvider = $this->getIntegrationProviderFromName($integrationProviderName);
		$tonicAction = $this->getTonicActionFromName($tonicActionName);

		$integrationProviderUuid = null;
		if (isset($integrationProvider['uuid'])) {
			$integrationProviderUuid = $integrationProvider['uuid'];
		}

		$tonicActionUuid = null;
		if (isset($tonicAction['uuid'])) {
			$tonicActionUuid = $tonicAction['uuid'];
		}

		$integrationProviderTonicAction = array();

		if ($integrationProviderUuid && $tonicActionUuid) {
			$statement = '
				SELECT *
				FROM
					integration_providers_tonic_actions
				WHERE
					integration_provider_uuid = ?
					AND tonic_action_uuid = ?
				LIMIT 1
			';
			$params = array(
				$integrationProviderUuid,
				$tonicActionUuid
			);
			$integrationProviderTonicActionResults = $this->query($statement, $params, false);
			if (isset($integrationProviderTonicActionResults[0][0])) {
				$integrationProviderTonicAction = $integrationProviderTonicActionResults[0][0];
			}
		}
		return $integrationProviderTonicAction;
	}

	/**
	 * gets the integration provider category type tonic action by uuid
	 */
	protected function getIntegrationProviderCategoryTypeTonicActionFromUuid($integrationProviderCategoryTypeUuid, $tonicActionUuid) {
		$integrationProviderCategoryTypeTonicAction = array();

		if ($integrationProviderCategoryTypeUuid && $tonicActionUuid) {
			$statement = '
				SELECT *
				FROM
					integration_provider_categories_types_tonic_actions
				WHERE
					integration_provider_category_integration_provider_type_uuid = ?
					AND tonic_action_uuid = ?
				LIMIT 1
			';
			$params = array(
				$integrationProviderCategoryTypeUuid,
				$tonicActionUuid
			);
			$integrationProviderCategoryTypeTonicActionResults = $this->query($statement, $params, false);
			if (isset($integrationProviderCategoryTypeTonicActionResults[0][0])) {
				$integrationProviderCategoryTypeTonicAction = $integrationProviderCategoryTypeTonicActionResults[0][0];
			}
		}
		return $integrationProviderCategoryTypeTonicAction;
	}

	/**
	 * gets the integration provider by name
	 */
	protected function getIntegrationProviderFromName($name) {
		$integrationProvider = array();
		$statement = '
			SELECT *
			FROM integration_providers
			WHERE name = ?
			LIMIT 1
		';
		$params = array(
			$name
		);
		$integrationProvidersResults = $this->query($statement, $params, false);
		if ($integrationProvidersResults) {
			foreach ($integrationProvidersResults as $integrationProvidersResult) {
				if (isset($integrationProvidersResult[0])) {
					$integrationProvider = $integrationProvidersResult[0];
				}
			}
		}
		return $integrationProvider;
	}

	/**
	 * gets the integration provider by uuid
	 */
	protected function getIntegrationProviderFromUuid($integrationProviderUuid) {
		$integrationProvider = array();
		$statement = '
			SELECT *
			FROM integration_providers
			WHERE uuid = ?
			LIMIT 1
		';
		$params = array(
			$integrationProviderUuid
		);
		$integrationProvidersResults = $this->query($statement, $params, false);
		if ($integrationProvidersResults) {
			foreach ($integrationProvidersResults as $integrationProvidersResult) {
				if (isset($integrationProvidersResult[0])) {
					$integrationProvider = $integrationProvidersResult[0];
				}
			}
		}
		return $integrationProvider;
	}

	/**
	 * gets the integration provider type by name
	 */
	protected function getIntegrationProviderTypeFromName($name) {
		$integrationProviderType = array();
		$statement = '
			SELECT *
			FROM integration_provider_types
			WHERE internal_name = ?
			LIMIT 1
		';
		$params = array(
			$name
		);
		$integrationProviderTypesResults = $this->query($statement, $params, false);
		if ($integrationProviderTypesResults) {
			foreach ($integrationProviderTypesResults as $integrationProviderTypesResult) {
				if (isset($integrationProviderTypesResult[0])) {
					$integrationProviderType = $integrationProviderTypesResult[0];
				}
			}
		}
		return $integrationProviderType;
	}

	/**
	 * gets the integration provider category by name
	 */
	protected function getIntegrationProviderCategoryFromName($name) {
		$integrationProviderCategory = array();
		$statement = '
			SELECT *
			FROM integration_provider_categories
			WHERE name = ?
			LIMIT 1
		';

		$params = array(
			$name
		);

		$integrationProviderCategoriesResults = $this->query($statement, $params, false);
		if ($integrationProviderCategoriesResults) {
			foreach ($integrationProviderCategoriesResults as $integrationProviderCategoriesResult) {
				if (isset($integrationProviderCategoriesResult[0])) {
					$integrationProviderCategory = $integrationProviderCategoriesResult[0];
				}
			}
		}
		return $integrationProviderCategory;
	}

	/**
	 * gets the tonic action by name
	 */
	protected function getTonicActionFromName($name) {
		$tonicAction = array();
		$statement = '
			SELECT *
			FROM tonic_actions
			WHERE name = ?
			LIMIT 1
		';

		$params = array(
			$name
		);

		$tonicActionResults = $this->query($statement, $params, false);
		if (isset($tonicActionResults[0][0])) {
			$tonicAction = $tonicActionResults[0][0];
		}
		return $tonicAction;
	}

	/**
	 * gets the integration provider group by name
	 */
	protected function getIntegrationProviderGroupFromName($name) {
		$integrationProviderGroup = array();
		$statement = '
			SELECT *
			FROM integration_provider_groups
			WHERE name = ?
			LIMIT 1
		';

		$params = array(
			$name
		);

		$integrationProviderGroupsResults = $this->query($statement, $params, false);
		if ($integrationProviderGroupsResults) {
			foreach ($integrationProviderGroupsResults as $integrationProviderGroupsResult) {
				if (isset($integrationProviderGroupsResult[0])) {
					$integrationProviderGroup = $integrationProviderGroupsResult[0];
				}
			}
		}
		return $integrationProviderGroup;
	}

	/**
	 * gets the integration provider type field set by name
	 */
	protected function getIntegrationProviderTypeFieldSetFromName($name) {
		$integrationProviderTypeFieldSet = array();
		$statement = '
			SELECT *
			FROM integration_provider_type_field_sets
			WHERE name = ?
			LIMIT 1
		';
		$params = array(
			$name
		);
		$integrationProviderTypeFieldSetsResults = $this->query($statement, $params, false);
		if ($integrationProviderTypeFieldSetsResults) {
			foreach ($integrationProviderTypeFieldSetsResults as $integrationProviderTypeFieldSetsResult) {
				if (isset($integrationProviderTypeFieldSetsResult[0])) {
					$integrationProviderTypeFieldSet = $integrationProviderTypeFieldSetsResult[0];
				}
			}
		}
		return $integrationProviderTypeFieldSet;
	}

	/**
	 *
	 */
	protected function getIntegrationProviderDataAsArray($integrationProvider) {
		$dataAsArray = array();
		if (isset($integrationProvider['data'])) {
			$dataJson = $integrationProvider['data'];
			$dataAsArray = json_decode($dataJson, true);
			if (!$dataAsArray) {
				$dataAsArray = array();
			}
		}
		return $dataAsArray;
	}

	/**
	 *
	 */
	protected function saveIntegrationProviderData($integrationProviderUuid, $dataAsArray) {
		if ($integrationProviderUuid) {
			$data = json_encode($dataAsArray);
			if (!$data) {
				$data = '{}';
			}
			$lastModifiedDate = date('Y-m-d H:i:s');

			$statement = '
				UPDATE
					integration_providers
				SET
					last_modified_date = ?,
					data = ?
				WHERE
					uuid = ?
			';

			$params = array(
				$lastModifiedDate,
				$data,
				$integrationProviderUuid
			);

			$this->query($statement, $params, false);
		}
	}

	/**
	 * delete deepsee favorite from name
	 */
	protected function deleteDeepseeFavoriteFromName($favoriteName) {
		if ($favoriteName) {
			$statement = '
				DELETE FROM
					deepsee_favorites
				WHERE
					name = ?
			';

			$params = array(
				$favoriteName
			);

			$this->query($statement, $params, false);
		}
	}

	/**
	 * delete deepsee favorite from uuid
	 */
	protected function deleteDeepseeFavoriteFromUuid($favoriteUuid) {
		if ($favoriteUuid) {
			$statement = '
				DELETE FROM
					deepsee_favorites
				WHERE
					uuid = ?
			';

			$params = array(
				$favoriteUuid
			);

			$this->query($statement, $params, false);
		}
	}

	/**
	 * delete action from name
	 */
	protected function deleteActionFromName($actionName) {
		if ($actionName) {
			$statement = '
				DELETE FROM
					actions
				WHERE
					name = ?
			';

			$params = array(
				$actionName
			);

			$this->query($statement, $params, false);
		}
	}

	/**
	 * delete action from uuid
	 */
	protected function deleteActionFromUuid($actionUuid) {
		if ($actionUuid) {
			$statement = '
				DELETE FROM
					actions
				WHERE
					uuid = ?
			';

			$params = array(
				$actionUuid
			);

			$this->query($statement, $params, false);
		}
	}

	/**
	 * delete integration provider type from uuid
	 */
	protected function deleteIntegrationProviderTypeFromUuid($integrationProviderTypeUuid) {
		if ($integrationProviderTypeUuid) {
			$statement = '
				DELETE FROM
					integration_provider_types
				WHERE
					uuid = ?
			';

			$params = array(
				$integrationProviderTypeUuid
			);

			$this->query($statement, $params, false);
		}
	}

	/**
	 * delete integration provider from name
	 */
	protected function deleteIntegrationProviderFromName($integrationProviderName) {
		if ($integrationProviderName) {
			$statement = '
				DELETE FROM
					integration_providers
				WHERE
					name = ?
			';

			$params = array(
				$integrationProviderName
			);

			$this->query($statement, $params, false);
		}
	}

	/**
	 * delete integration provider from uuid
	 */
	protected function deleteIntegrationProviderFromUuid($integrationProviderUuid) {
		if ($integrationProviderUuid) {
			$statement = '
				DELETE FROM
					integration_providers
				WHERE
					uuid = ?
			';

			$params = array(
				$integrationProviderUuid
			);

			$this->query($statement, $params, false);
		}
	}

	/**
	 * delete integration provider type from name
	 */
	protected function deleteIntegrationProviderTypeFromName($integrationProviderTypeName) {
		if ($integrationProviderTypeName) {
			$statement = '
				DELETE FROM
					integration_provider_types
				WHERE
					internal_name = ?
			';

			$params = array(
				$integrationProviderTypeName
			);

			$this->query($statement, $params, false);
		}
	}

	/**
	 * delete integration provider type field set from name
	 */
	protected function deleteIntegrationProviderTypeFieldSetFromName($integrationProviderTypeFieldSetName) {
		if ($integrationProviderTypeFieldSetName) {
			$statement = '
				DELETE FROM
					integration_provider_type_field_sets
				WHERE
					name = ?
			';

			$params = array(
				$integrationProviderTypeFieldSetName
			);

			$this->query($statement, $params, false);
		}
	}

	/**
	 * delete integration provider category from name
	 */
	protected function deleteIntegrationProviderCategoryFromName($integrationProviderCategoryName) {
		if ($integrationProviderCategoryName) {
			$statement = '
				DELETE FROM
					integration_provider_categories
				WHERE
					name = ?
			';

			$params = array(
				$integrationProviderCategoryName
			);

			$this->query($statement, $params, false);
		}
	}

	/**
	 * delete tonic action from name
	 */
	protected function deleteTonicActionFromName($tonicActionName) {
		if ($tonicActionName) {
			$statement = '
				DELETE FROM
					tonic_actions
				WHERE
					name = ?
			';

			$params = array(
				$tonicActionName
			);

			$this->query($statement, $params, false);
		}
	}

	/**
	 * get default integration provider category
	 */
	protected function getDefaultIntegrationProviderCategory() {
		$defaultCategory = array(
			'uuid' => '',
			'name' => '',
			'last_modified_date' => 'now()',
			'appliance_id' => 0
		);
		return $defaultCategory;
	}

	/**
	 * get default tonic action
	 */
	protected function getDefaultTonicAction() {
		$defaultCategory = array(
			'uuid' => '',
			'name' => '',
			'appliance_id' => 0
		);
		return $defaultCategory;
	}

	/**
	 * insert integration provider categories
	 */
	protected function insertIntegrationProviderCategory($categoryToInsert) {
		$categoryNameToInsert = '';
		if (isset($categoryToInsert['name'])) {
			$categoryNameToInsert = $categoryToInsert['name'];
		}

		if ($categoryNameToInsert) {
			$integrationProviderCategory = $this->getIntegrationProviderCategoryFromName($categoryNameToInsert);

			if (empty($integrationProviderCategory)) {
				$defaultCategory = $this->getDefaultIntegrationProviderCategory();

				$uuid = $this->getUuid();
				$integrationProviderCategory = array_merge($defaultCategory, $categoryToInsert);
				$integrationProviderCategory['uuid'] = $uuid;

				$categoryValues = $this->getIntegrationProviderCategoryValues($integrationProviderCategory);
				$categoryValuesCount = count($categoryValues);
				$questionMarks = $this->getQuestionMarks($categoryValuesCount);

				$statement = '
					INSERT INTO integration_provider_categories
					(
						uuid,
						name,
						last_modified_date,
						appliance_id
					)
					VALUES ('.$questionMarks.')
				';
				$this->query($statement, $categoryValues, false);
			}
		}
	}

	/**
	 * insert tonic action
	 */
	protected function insertTonicAction($tonicActionToInsert) {
		$tonicActionNameToInsert = '';
		if (isset($tonicActionToInsert['name'])) {
			$tonicActionNameToInsert = $tonicActionToInsert['name'];
		}

		if ($tonicActionNameToInsert) {
			$tonicAction = $this->getTonicActionFromName($tonicActionNameToInsert);

			if (empty($tonicAction)) {
				$defaultTonicAction = $this->getDefaultTonicAction();

				$uuid = $this->getUuid();
				$tonicAction = array_merge($defaultTonicAction, $tonicActionToInsert);
				$tonicAction['uuid'] = $uuid;

				$tonicActionValues = $this->getTonicActionValues($tonicAction);
				$tonicActionValuesCount = count($tonicActionValues);
				$questionMarks = $this->getQuestionMarks($tonicActionValuesCount);

				$statement = '
					INSERT INTO tonic_actions
					(
						uuid,
						name,
						appliance_id
					)
					VALUES ('.$questionMarks.')
				';
				$this->query($statement, $tonicActionValues, false);
			}
		}
	}

	/**
	 *
	 * @param type $type
	 * @return type
	 */
	protected function getIntegrationProviderCategoryValues($categoryParam) {
		$category = $this->convertBooleans($categoryParam);

		$values = array(
			$category['uuid'],
			$category['name'],
			$category['last_modified_date'],
			$category['appliance_id']
		);
		return $values;
	}

	/**
	 *
	 * @param type $type
	 * @return type
	 */
	protected function getTonicActionValues($tonicActionParam) {
		$tonicAction = $this->convertBooleans($tonicActionParam);

		$values = array(
			$tonicAction['uuid'],
			$tonicAction['name'],
			$tonicAction['appliance_id'],
		);
		return $values;
	}

	/**
	 * get default integration provider group
	 */
	protected function getDefaultIntegrationProviderGroup() {
		$defaultGroup = array(
			'uuid' => '',
			'name' => '',
			'last_modified_date' => 'now()',
			'appliance_id' => 0
		);
		return $defaultGroup;
	}

	/**
	 * insert default integration provider groups
	 */
	protected function insertIntegrationProviderGroup($groupToInsert) {
		$groupNameToInsert = '';
		if (isset($groupToInsert['name'])) {
			$groupNameToInsert = $groupToInsert['name'];
		}

		if ($groupNameToInsert) {
			$integrationProviderGroup = $this->getIntegrationProviderGroupFromName($groupNameToInsert);

			if (empty($integrationProviderGroup)) {
				$defaultGroup = $this->getDefaultIntegrationProviderGroup();

				$uuid = $this->getUuid();
				$integrationProviderGroup = array_merge($defaultGroup, $groupToInsert);
				$integrationProviderGroup['uuid'] = $uuid;
				$integrationProviderGroupValues = $this->getIntegrationProviderGroupValues($integrationProviderGroup);
				$integrationProviderGroupValuesCount = count($integrationProviderGroupValues);
				$questionMarks = $this->getQuestionMarks($integrationProviderGroupValuesCount);

				$statement = '
					INSERT INTO integration_provider_groups (
						uuid,
						name,
						internal_name,
						description,
						active,
						ordinal,
						last_modified_date,
						appliance_id
					)
					VALUES (
						'.$questionMarks.'
					)
				';
				$this->query($statement, $integrationProviderGroupValues, false);
			}
		}
	}

	/**
	 *
	 * @param type $group
	 * @return type
	 */
	protected function getIntegrationProviderGroupValues($groupParam) {
		$group = $this->convertBooleans($groupParam);

		$values = array(
			$group['uuid'],
			$group['name'],
			$group['internal_name'],
			$group['description'],
			$group['active'],
			$group['ordinal'],
			$group['last_modified_date'],
			$group['appliance_id']
		);
		return $values;
	}

	/**
	 * get default integration provider type
	 */
	protected function getDefaultIntegrationProviderType() {
		$defaultType = array(
			'name' => '',
			'internal_name' => '',
			'creatable' => 'FALSE',
			'deletable' => 'FALSE',
			'edit_type' => 'none',
			'associate_with_action' => 'FALSE',
			'user_initiated' => 'TRUE',
			'licensed' => 'FALSE',
			'field_set' => 'boolean',
			'abyssal' => 'FALSE',
			'pivot_only' => 'FALSE',
			'bigfile' => 'FALSE',
			'league' => 'reputation',
			'organization' => ''
		);
		return $defaultType;
	}


	/**
	 * insert integration provider type
	 */
	protected function insertIntegrationProviderType($typeToInsert) {
		$tableName = 'integration_provider_types';
		$typeNameToInsert = '';
		if (isset($typeToInsert['internal_name'])) {
			$typeNameToInsert = $typeToInsert['internal_name'];
		}

		if ($typeNameToInsert) {
			$integrationProviderType = $this->getIntegrationProviderTypeFromName($typeNameToInsert);

			if (empty($integrationProviderType)) {
				$fieldSetUuid = null;
				$fieldSetName = 'boolean';
				if (isset($typeToInsert['field_set_name'])) {
					$fieldSetName = $typeToInsert['field_set_name'];
					$fieldSet = $this->getIntegrationProviderTypeFieldSetFromName($fieldSetName);
					if (isset($fieldSet['uuid'])) {
						$fieldSetUuid = $fieldSet['uuid'];
					}
				}

				if ($fieldSetUuid) {
					$defaultType = $this->getDefaultIntegrationProviderType();
					$integrationProviderType = array_merge($defaultType, $typeToInsert);

					$uuid = $this->getUuid();
					$integrationProviderType['uuid'] = $uuid;
					$integrationProviderType['integration_provider_type_field_set_uuid'] = $fieldSetUuid;

					$columnNames = $this->getTableColumnNames($tableName);
					$columnNamesStr = implode(', ', $columnNames);
					$integrationProviderTypeValues = $this->getIntegrationProviderTypeValues($columnNames, $integrationProviderType);

					$integrationProviderTypeValuesCount = count($integrationProviderTypeValues);
					$questionMarks = $this->getQuestionMarks($integrationProviderTypeValuesCount);

					$statement = '
						INSERT INTO integration_provider_types (
							' . $columnNamesStr . '
						)
						VALUES (
							'.$questionMarks.'
						)
					';
					$this->query($statement, $integrationProviderTypeValues, false);
				}
			}
		}
	}

	/**
	 * insert integration provider category type tonic action
	 */
	protected function insertIntegrationProviderCategoryTypeTonicAction($categoryTypeTonicActionToInsert) {
		$typeNameToInsert = '';
		if (isset($categoryTypeTonicActionToInsert['type_name'])) {
			$typeNameToInsert = $categoryTypeTonicActionToInsert['type_name'];
		}

		$categoryNameToInsert = '';
		if (isset($categoryTypeTonicActionToInsert['category_name'])) {
			$categoryNameToInsert = $categoryTypeTonicActionToInsert['category_name'];
		}

		$integrationProviderCategoryType = array();
		if ($categoryNameToInsert && $typeNameToInsert) {
			$integrationProviderCategoryType = $this->getIntegrationProviderCategoryTypeFromName($categoryNameToInsert, $typeNameToInsert);
		}

		$integrationProviderCategoryTypeUuid = null;
		if (isset($integrationProviderCategoryType['uuid'])) {
			$integrationProviderCategoryTypeUuid = $integrationProviderCategoryType['uuid'];
		}

		$tonicActionNameToInsert = '';
		if (isset($categoryTypeTonicActionToInsert['tonic_action_name'])) {
			$tonicActionNameToInsert = $categoryTypeTonicActionToInsert['tonic_action_name'];
		}

		$tonicAction = array();
		if ($tonicActionNameToInsert) {
			$tonicAction = $this->getTonicActionFromName($tonicActionNameToInsert);
		}

		$tonicActionUuid = null;
		if (isset($tonicAction['uuid'])) {
			$tonicActionUuid = $tonicAction['uuid'];
		}

		if ($integrationProviderCategoryTypeUuid && $tonicActionUuid) {
			$integrationProviderCategoryTypeTonicAction = $this->getIntegrationProviderCategoryTypeTonicActionFromUuid($integrationProviderCategoryTypeUuid, $tonicActionUuid);

			if (empty($integrationProviderCategoryTypeTonicAction)) {
				$integrationProviderCategoryTypeTonicAction = array();

				$uuid = $this->getUuid();
				$integrationProviderCategoryTypeTonicAction['uuid'] = $uuid;
				$integrationProviderCategoryTypeTonicAction['integration_provider_category_integration_provider_type_uuid'] = $integrationProviderCategoryTypeUuid;
				$integrationProviderCategoryTypeTonicAction['tonic_action_uuid'] = $tonicActionUuid;
				$integrationProviderCategoryTypeTonicAction['appliance_id'] = 0;

				$integrationProviderCategoryTypeTonicActionValues = $this->getIntegrationProviderCategoryTypeTonicActionValues($integrationProviderCategoryTypeTonicAction);
				$integrationProviderCategoryTypeTonicActionValuesCount = count($integrationProviderCategoryTypeTonicActionValues);
				$questionMarks = $this->getQuestionMarks($integrationProviderCategoryTypeTonicActionValuesCount);

				$statement = '
					INSERT INTO integration_provider_categories_types_tonic_actions (
						uuid,
						integration_provider_category_integration_provider_type_uuid,
						tonic_action_uuid,
						appliance_id
					)
					VALUES (
						'.$questionMarks.'
					)
				';
				$this->query($statement, $integrationProviderCategoryTypeTonicActionValues, false);
			}
		}
	}

	/**
	 *
	 * @param type $type
	 * @return type
	 */
	protected function getIntegrationProviderTypeValues($columnNames, $typeParam) {
		$type = $this->convertBooleans($typeParam);

		$values = array();
		foreach ($columnNames as $columnName) {
			$valueExists = array_key_exists($columnName, $type);
			if ($valueExists) {
				$value = $type[$columnName];
				$values[] = $value;
			}
		}
		return $values;
	}

	/**
	 * get default integration provider type field set
	 */
	protected function getDefaultIntegrationProviderTypeFieldSet() {
		$defaultType = array(
			'uuid' => null,
			'name' => '',
			'last_modified_date' => 'now()',
			'appliance_id' => 0
		);
		return $defaultType;
	}

	/**
	 * insert integration provider type
	 */
	protected function insertIntegrationProviderTypeFieldSet($typeFieldSetToInsert) {
		$typeFieldSetNameToInsert = '';
		if (isset($typeFieldSetToInsert['name'])) {
			$typeFieldSetNameToInsert = $typeFieldSetToInsert['name'];
		}

		if ($typeFieldSetNameToInsert) {
			$integrationProviderTypeFieldSet = $this->getIntegrationProviderTypeFieldSetFromName($typeFieldSetNameToInsert);

			if (empty($integrationProviderTypeFieldSet)) {
				$defaultTypeFieldSet = $this->getDefaultIntegrationProviderTypeFieldSet();
				$integrationProviderTypeFieldSet = array_merge($defaultTypeFieldSet, $typeFieldSetToInsert);

				$uuid = $this->getUuid();
				$integrationProviderTypeFieldSet['uuid'] = $uuid;
				$integrationProviderTypeFieldSetValues = $this->getIntegrationProviderTypeFieldSetValues($integrationProviderTypeFieldSet);
				$integrationProviderTypeFieldSetValuesCount = count($integrationProviderTypeFieldSetValues);
				$questionMarks = $this->getQuestionMarks($integrationProviderTypeFieldSetValuesCount);

				$statement = '
					INSERT INTO integration_provider_type_field_sets (
						uuid,
						name,
						last_modified_date,
						appliance_id
					)
					VALUES (
						'.$questionMarks.'
					)
				';
				$this->query($statement, $integrationProviderTypeFieldSetValues, false);
			}
		}
	}

	/**
	 * insert integration provider tonic action
	 */
	protected function insertIntegrationProviderTonicAction($integrationProviderTonicActionToInsert) {
		$integrationProviderTonicActionNameToInsert = '';
		if (isset($integrationProviderTonicActionToInsert['tonic_action_name'])) {
			$integrationProviderTonicActionNameToInsert = $integrationProviderTonicActionToInsert['tonic_action_name'];
		}

		$tonicAction = array();
		if ($integrationProviderTonicActionNameToInsert) {
			$tonicAction = $this->getTonicActionFromName($integrationProviderTonicActionNameToInsert);
		}

		$tonicActionUuid = null;
		if (isset($tonicAction['uuid'])) {
			$tonicActionUuid = $tonicAction['uuid'];
		}

		$tonicActionName = null;
		if (isset($tonicAction['name'])) {
			$tonicActionName = $tonicAction['name'];
		}

		$integrationProviderNameToInsert = '';
		if (isset($integrationProviderTonicActionToInsert['integration_provider_name'])) {
			$integrationProviderNameToInsert = $integrationProviderTonicActionToInsert['integration_provider_name'];
		}

		$integrationProvider = array();
		if ($integrationProviderNameToInsert) {
			$integrationProvider = $this->getIntegrationProviderFromName($integrationProviderNameToInsert);
		}

		$integrationProviderUuid = null;
		if (isset($integrationProvider['uuid'])) {
			$integrationProviderUuid = $integrationProvider['uuid'];
		}

		$integrationProviderName = null;
		if (isset($integrationProvider['name'])) {
			$integrationProviderName = $integrationProvider['name'];
		}

		if ($integrationProviderName && $tonicActionName) {
			$integrationProviderTonicAction = $this->getIntegrationProviderTonicActionFromName($integrationProviderName, $tonicActionName);

			if (empty($integrationProviderTonicAction)) {
				$integrationProviderTonicAction = array();

				$uuid = $this->getUuid();
				$integrationProviderTonicAction['uuid'] = $uuid;
				$integrationProviderTonicAction['integration_provider_uuid'] = $integrationProviderUuid;
				$integrationProviderTonicAction['tonic_action_uuid'] = $tonicActionUuid;
				$integrationProviderTonicAction['appliance_id'] = 0;

				$integrationProviderTonicActionValues = $this->getIntegrationProviderTonicActionValues($integrationProviderTonicAction);
				$integrationProviderTonicActionValuesCount = count($integrationProviderTonicActionValues);
				$questionMarks = $this->getQuestionMarks($integrationProviderTonicActionValuesCount);

				$statement = '
					INSERT INTO integration_providers_tonic_actions
					(
						uuid,
						integration_provider_uuid,
						tonic_action_uuid,
						appliance_id
					)
					VALUES ('.$questionMarks.')
				';
				$this->query($statement, $integrationProviderTonicActionValues, false);
			}
		}
	}

	/**
	 *
	 * @param type $typeFieldSetParam
	 * @return type
	 */
	protected function getIntegrationProviderTypeFieldSetValues($typeFieldSetParam) {
		$type = $this->convertBooleans($typeFieldSetParam);

		$values = array(
			$type['uuid'],
			$type['name'],
			$type['last_modified_date'],
			$type['appliance_id']
		);
		return $values;
	}

	/**
	 *
	 * @param type $integrationProviderTonicAction
	 * @return type
	 */
	protected function getIntegrationProviderTonicActionValues($integrationProviderTonicActionParam) {
		$integrationProviderTonicAction = $this->convertBooleans($integrationProviderTonicActionParam);

		$values = array(
			$integrationProviderTonicAction['uuid'],
			$integrationProviderTonicAction['integration_provider_uuid'],
			$integrationProviderTonicAction['tonic_action_uuid'],
			$integrationProviderTonicAction['appliance_id']
		);
		return $values;
	}

	/**
	 * get default integration provider category type
	 */
	protected function getDefaultIntegrationProviderCategoryType() {
		$defaultCategoryType = array(
			'uuid' => '',
			'integration_provider_category_uuid' => '',
			'integration_provider_type_uuid' => '',
			'appliance_id' => 0,
			'last_modified_date' => 'now()'
		);
		return $defaultCategoryType;
	}

	/**
	 * insert integration provider category type
	 */
	protected function insertIntegrationProviderCategoryType($categoryTypeToInsert) {
		$typeNameToInsert = '';
		if (isset($categoryTypeToInsert['type_name'])) {
			$typeNameToInsert = $categoryTypeToInsert['type_name'];
		}

		$categoryNameToInsert = '';
		if (isset($categoryTypeToInsert['category_name'])) {
			$categoryNameToInsert = $categoryTypeToInsert['category_name'];
		}

		if ($categoryNameToInsert && $typeNameToInsert) {
			$integrationProviderCategoryType = $this->getIntegrationProviderCategoryTypeFromName($categoryNameToInsert, $typeNameToInsert);

			if (empty($integrationProviderCategoryType)) {
				$category = $this->getIntegrationProviderCategoryFromName($categoryNameToInsert);
				$type = $this->getIntegrationProviderTypeFromName($typeNameToInsert);

				$categoryUuid = null;
				if (isset($category['uuid'])) {
					$categoryUuid = $category['uuid'];
				}

				$typeUuid = null;
				if (isset($type['uuid'])) {
					$typeUuid = $type['uuid'];
				}

				if ($categoryUuid && $typeUuid) {
					$defaultCategoryType = $this->getDefaultIntegrationProviderCategoryType();
					$integrationProviderCategoryType = array_merge($defaultCategoryType, $categoryTypeToInsert);

					$uuid = $this->getUuid();
					$integrationProviderCategoryType['uuid'] = $uuid;
					$integrationProviderCategoryType['integration_provider_category_uuid'] = $categoryUuid;
					$integrationProviderCategoryType['integration_provider_type_uuid'] = $typeUuid;
					$integrationProviderCategoryTypeValues = $this->getIntegrationProviderCategoryTypeValues($integrationProviderCategoryType);
					$integrationProviderCategoryTypeValuesCount = count($integrationProviderCategoryTypeValues);
					$questionMarks = $this->getQuestionMarks($integrationProviderCategoryTypeValuesCount);

					$statement = '
						INSERT INTO integration_provider_categories_integration_provider_types (
							uuid,
							integration_provider_category_uuid,
							integration_provider_type_uuid,
							appliance_id,
							last_modified_date
						)
						VALUES (
							'.$questionMarks.'
						)
					';
					$this->query($statement, $integrationProviderCategoryTypeValues, false);
				}
			}
		}
	}

	/**
	 *
	 * @param type $type
	 * @return type
	 */
	protected function getIntegrationProviderCategoryTypeValues($categoryTypeParam) {
		$categoryType = $this->convertBooleans($categoryTypeParam);

		$values = array(
			$categoryType['uuid'],
			$categoryType['integration_provider_category_uuid'],
			$categoryType['integration_provider_type_uuid'],
			$categoryType['appliance_id'],
			$categoryType['last_modified_date']

		);
		return $values;
	}

	/**
	 *
	 * @param type $categoryTypeTonicAction
	 * @return type
	 */
	protected function getIntegrationProviderCategoryTypeTonicActionValues($categoryTypeTonicActionParam) {
		$categoryTypeTonicAction = $this->convertBooleans($categoryTypeTonicActionParam);

		$values = array(
			$categoryTypeTonicAction['uuid'],
			$categoryTypeTonicAction['integration_provider_category_integration_provider_type_uuid'],
			$categoryTypeTonicAction['tonic_action_uuid'],
			$categoryTypeTonicAction['appliance_id']

		);
		return $values;
	}

	/**
	 * get default integration provider
	 */
	protected function getDefaultIntegrationProvider($useGroup = true) {
		$defaultProvider = array(
			'uuid' => '',
			'name' => '',
			'description' => '',
			'integration_provider_type_uuid' => '',
			'integration_provider_category_uuid' => '',
			'class_type' => '',
			'active' => '',
			'ordinal' => null,
			'appliance_id' => 0,
			'last_modified_date,' => 'now()',
			'data' => array()
		);
		if ($useGroup) {
			$defaultProvider['integration_provider_group_uuid'] = '';
		}
		$defaultProvider['pivot_url'] = '';

		return $defaultProvider;
	}

	/**
	 * insert integration provider
	 */
	protected function insertIntegrationProvider($providerToInsert, $useGroup = true) {
		$providerNameToInsert = '';
		if (isset($providerToInsert['name'])) {
			$providerNameToInsert = $providerToInsert['name'];
		}

		if ($providerNameToInsert) {
			$integrationProvider = $this->getIntegrationProviderFromName($providerNameToInsert);

			if (empty($integrationProvider)) {
				$groupUuid = null;
				if ($useGroup) {
					$groupName = '';
					if (isset($providerToInsert['group_name'])) {
						$groupName = $providerToInsert['group_name'];
						$group = $this->getIntegrationProviderGroupFromName($groupName);
						if (isset($group['uuid'])) {
							$groupUuid = $group['uuid'];
						}
					}
				}

				$categoryUuid = null;
				$categoryName = '';
				if (isset($providerToInsert['category_name'])) {
					$categoryName = $providerToInsert['category_name'];
					$category = $this->getIntegrationProviderCategoryFromName($categoryName);
					if (isset($category['uuid'])) {
						$categoryUuid = $category['uuid'];
					}
				}

				$typeUuid = null;
				$typeName = '';
				if (isset($providerToInsert['type_name'])) {
					$typeName = $providerToInsert['type_name'];
					$type = $this->getIntegrationProviderTypeFromName($typeName);
					if (isset($type['uuid'])) {
						$typeUuid = $type['uuid'];
					}
				}

				if ($categoryUuid && $typeUuid) {
					$defaultProvider = $this->getDefaultIntegrationProvider($useGroup);
					$integrationProvider = array_merge($defaultProvider, $providerToInsert);

					$uuid = $this->getUuid();
					$integrationProvider['uuid'] = $uuid;
					$integrationProvider['integration_provider_category_uuid'] = $categoryUuid;
					$integrationProvider['integration_provider_type_uuid'] = $typeUuid;
					if ($useGroup) {
						$integrationProvider['integration_provider_group_uuid'] = $groupUuid;
					}

					$data = $integrationProvider['data'];
					$data['type'] = $typeName;
					$data['category'] = $categoryName;
					$data['integration_provider_uuid'] = $uuid;
					$dataJsonStr = json_encode($data);
					$integrationProvider['data'] = $dataJsonStr;

					$integrationProviderValues = $this->getIntegrationProviderValues($integrationProvider, $useGroup);
					$integrationProviderValuesCount = count($integrationProviderValues);
					$questionMarks = $this->getQuestionMarks($integrationProviderValuesCount);

					$fieldNames = array(
						'uuid',
						'integration_provider_type_uuid',
						'integration_provider_category_uuid',
						'class_type',
						'name',
						'description',
						'active',
						'ordinal',
						'last_modified_date',
						'data',
						'appliance_id',
						'pivot_url'
					);
					if ($useGroup) {
						$fieldNames[] = 'integration_provider_group_uuid';
					}
					$fieldNamesStr = implode(',', $fieldNames);

					$statement = '
						INSERT INTO integration_providers (
							' . $fieldNamesStr . '
						)
						VALUES (
							'.$questionMarks.'
						)
					';
					$this->query($statement, $integrationProviderValues, false);
				}
			}
		}
	}

	/**
	 *
	 * @param type $type
	 * @return type
	 */
	protected function getIntegrationProviderValues($providerParam, $useGroup = true) {
		$provider = $this->convertBooleans($providerParam);

		$values = array(
			$provider['uuid'],
			$provider['integration_provider_type_uuid'],
			$provider['integration_provider_category_uuid'],
			$provider['class_type'],
			$provider['name'],
			$provider['description'],
			$provider['active'],
			$provider['ordinal'],
			$provider['last_modified_date'],
			$provider['data'],
			$provider['appliance_id'],
			$provider['pivot_url']
		);
		if ($useGroup) {
			$values[] = $provider['integration_provider_group_uuid'];
		}
		return $values;
	}

	/**
	 * get default favorite
	 */
	protected function getDefaultFavorite() {
		$defaultFavorite = array(
			'uuid' => '',
			'name' => '',
			'value' => null,
			'user_id' => null,
			'active' => true,
			'shared' => true,
			'nested' => 0,
			'last_modified_date' => 'now()',
			'class' => 0,
			'creatable' => false,
			'deletable' => false,
			'edit_type' => 'none',
			'ordinal' => null,
			'frequency' => null,
			'last_execution' => null,
			'time_of_execution' => null,
			'end_time_of_execution' => '23:59:59',
			'linked_uuid' => null,
			'original_params' => null,
			'hidden' => false
		);
		return $defaultFavorite;
	}

	/**
	 * insert favorite
	 */
	protected function insertFavorite($favoriteToInsert) {
		$favoriteNameToInsert = '';
		if (isset($favoriteToInsert['name'])) {
			$favoriteNameToInsert = $favoriteToInsert['name'];
		}

		if ($favoriteNameToInsert) {
			$favorite = $this->getDeepseeFavoriteFromName($favoriteNameToInsert);

			if (empty($favorite)) {

				$defaultFavorite = $this->getDefaultFavorite();
				$favorite = array_merge($defaultFavorite, $favoriteToInsert);

				if (!isset($favoriteToInsert['uuid'])) {
					$uuid = $this->getUuid();
					$favorite['uuid'] = $uuid;
				}

				$favoriteValues = $this->getFavoriteValues($favorite);
				$favoriteValuesCount = count($favoriteValues);
				$questionMarks = $this->getQuestionMarks($favoriteValuesCount);

				$statement = '
					INSERT INTO deepsee_favorites (
						uuid,
						name,
						value,
						user_id,
						active,
						shared,
						nested,
						last_modified_date,
						class,
						creatable,
						deletable,
						edit_type,
						ordinal,
						frequency,
						last_execution,
						time_of_execution,
						end_time_of_execution,
						linked_uuid,
						original_params,
						hidden
					)
					VALUES (
						'.$questionMarks.'
					)
				';
				$this->query($statement, $favoriteValues, false);
			}
			return $favorite['uuid'];
		}
	}

	/**
	 *
	 * @param type $type
	 * @return type
	 */
	protected function getFavoriteValues($favoriteParam) {
		$favorite = $this->convertBooleans($favoriteParam);

		$values = array(
			$favorite['uuid'],
			$favorite['name'],
			$favorite['value'],
			$favorite['user_id'],
			$favorite['active'],
			$favorite['shared'],
			$favorite['nested'],
			$favorite['last_modified_date'],
			$favorite['class'],
			$favorite['creatable'],
			$favorite['deletable'],
			$favorite['edit_type'],
			$favorite['ordinal'],
			$favorite['frequency'],
			$favorite['last_execution'],
			$favorite['time_of_execution'],
			$favorite['end_time_of_execution'],
			$favorite['linked_uuid'],
			$favorite['original_params'],
			$favorite['hidden'],
		);
		return $values;
	}

	/**
	 * Updates a favorites value
	 *
	 * @param string $favoriteName Name of the favorite to update
	 * @param array $favoriteValue New value of the favorite
	 * @param string $favoritePath New gauge path. This is optional but can be used to create advanced paths that don't match
	 * the $favoriteValue
	 * @return mixed false if error / empty array on success
	 */
	protected function updateFavoriteValue($favoriteName, array $favoriteValue, $favoritePath = '') {
		$favorite = $this->getDeepseeFavoriteFromName($favoriteName);

		if (!$favorite || empty($favorite)) {
			return true;
		}

		if (!$favoritePath) {
			$that = $this;
			$favoriteLookup = function($innerFavoriteName) use ($that) {
				$favoriteValue = $that->getDeepseeFavoriteFromName($innerFavoriteName);
				return $favoriteValue['value'];
			};
			$favoritePath = Latest\GaugePath::createGaugePath($favoriteValue, $favoriteLookup);
		}

		$favoriteValues = array(
			'value' => json_encode($favoriteValue),
			'gauge_path' => $favoritePath,
			'uuid' => $favorite['uuid'],
		);

		$statement = '
			UPDATE deepsee_favorites SET
			value = :value,
			gauge_path = :gauge_path,
			last_modified_date = now()
			WHERE
			uuid = :uuid
			';
		return $this->query($statement, $favoriteValues, false);
	}

	/**
	 * Updates a favorites value
	 *
	 * @param string $favoriteName Name of the favorite to update
	 * @param array $favoriteValue New value of the favorite
	 * @param string $favoritePath New gauge path. This is optional but can be used to create advanced paths that don't match
	 * the $favoriteValue
	 * @return void
	 */
	protected function updateFavoriteValueWithNestedUuid($favoriteName, array $favoriteValue, $favoritePath = '') {
		$favorite = $this->getDeepseeFavoriteFromName($favoriteName);

		if (!$favorite || empty($favorite)) {
			return;
		}

		if (!$favoritePath) {
			$favoriteLookup = $this->getFavoriteValueClosure();
			$favoritePath = Latest\GaugePath::createGaugePath($favoriteValue, $favoriteLookup);
		}

		$favoriteValues = array(
			'value' => json_encode($favoriteValue),
			'gauge_path' => $favoritePath,
			'uuid' => $favorite['uuid'],
		);

		$statement = '
			UPDATE deepsee_favorites SET
			value = :value,
			gauge_path = :gauge_path,
			last_modified_date = now()
			WHERE
			uuid = :uuid
			';
		$this->query($statement, $favoriteValues, false);
	}

	/**
	 * Updates a favorites value
	 *
	 * @param string $oldName Name of the favorite to update
	 * @param array $newName New name of the favorite
	 * @return void
	 */
	protected function updateFavoriteName($oldName, $newName) {
		if ($oldName && $newName) {
			$favorite = $this->getDeepseeFavoriteFromName($oldName);

			$favoriteUuid = null;
			if (isset($favorite['uuid'])) {
				$favoriteUuid = $favorite['uuid'];
			}

			if ($favoriteUuid) {
				$statement = '
					UPDATE deepsee_favorites SET
					name = ?,
					last_modified_date = now()
					WHERE
					uuid = ?
				';
				$params = array(
					$newName,
					$favoriteUuid
				);
				$this->query($statement, $params, false);
			}
		}
	}

	/**
	 * get default favorite event
	 */
	protected function getDefaultFavoriteEvent() {
		$defaultFavoriteEvent = array(
			'uuid' => '',
			'deepsee_favorite_uuid' => null,
			'name' => '',
			'event' => 'daily'
		);
		return $defaultFavoriteEvent;
	}

	/**
	 * insert favorite event
	 */
	protected function insertFavoriteEvent($favoriteEventToInsert) {
		$favoriteUuid = null;

		$favoriteNameToInsert = '';
		if (isset($favoriteEventToInsert['name'])) {
			$favoriteNameToInsert = $favoriteEventToInsert['name'];
		}




		if ($favoriteNameToInsert) {
			$favoriteEvents = $this->getDeepseeFavoriteEventsFromName($favoriteNameToInsert);

			if (empty($favoriteEvents)) {
				$favorite = $this->getDeepseeFavoriteFromName($favoriteNameToInsert);

				if (isset($favorite['uuid'])) {
					$favoriteUuid = $favorite['uuid'];
				}
			}

			if ($favoriteUuid) {
				$favoriteEventToInsert['deepsee_favorite_uuid'] = $favoriteUuid;

				$defaultFavoriteEvent = $this->getDefaultFavoriteEvent();
				$favoriteEvent = array_merge($defaultFavoriteEvent, $favoriteEventToInsert);

				$favoriteEventValues = $this->getFavoriteEventValues($favoriteEvent);
				$favoriteEventValuesCount = count($favoriteEventValues);
				$questionMarks = $this->getQuestionMarks($favoriteEventValuesCount);

				$statement = '
					INSERT INTO deepsee_favorite_events (
						deepsee_favorite_uuid,
						event
					)
					VALUES (
						'.$questionMarks.'
					)
				';
				$this->query($statement, $favoriteEventValues, false);
			}
		}
	}

	/**
	 *
	 * @param type $type
	 * @return type
	 */
	protected function getFavoriteEventValues($favoriteEventParam) {
		$favoriteEvent = $this->convertBooleans($favoriteEventParam);

		$values = array(
			$favoriteEvent['deepsee_favorite_uuid'],
			$favoriteEvent['event']
		);
		return $values;
	}

	/**
	 * get default action
	 */
	protected function getDefaultAction() {
		$defaultAction = array(
			'uuid' => '',
			'name' => '',
			'type' => Latest\Action::TYPE_DATA_ENRICHEMENT,
			'data' => array(),
			'status' => Latest\Action::STATUS_INACTIVE,
			'shared' => true,
			'user_id' => null,
			'last_modified_date' => 'now()',
			'creatable' => false,
			'deletable' => false,
			'edit_type' => 'none',
			'ordinal' => null,
			'snmp_template_id' => null,
			'smtp_template_id' => null,
			'syslog_template_id' => null,
			'hidden' => false,

			// data values
			'importance' => '3',
			'notification_interval' => '900',
			'rule_email_configured' => '0',
		);
		return $defaultAction;
	}

	/**
	 * insert action
	 */
	protected function insertAction($actionToInsert) {
		$actionNameToInsert = '';
		if (isset($actionToInsert['name'])) {
			$actionNameToInsert = $actionToInsert['name'];
		}

		if ($actionNameToInsert) {
			$action = $this->getActionFromName($actionNameToInsert);

			if (empty($action)) {

				$defaultAction = $this->getDefaultAction();
				$action = array_merge($defaultAction, $actionToInsert);

				$uuid = $this->getUuid();
				$action['uuid'] = $uuid;

				$data = $action['data'];
				switch ($action['type']) {
					case Latest\Action::TYPE_ALERT:
						$data['importance'] = $action['importance'];
						$data['notification_interval'] = $action['notification_interval'];
						$data['rule_email_configured'] = $action['rule_email_configured'];
						break;
					case Latest\Action::TYPE_DATA_ENRICHEMENT:
						$data['importance'] = '';
						$data['plugin_params'] = array(
							'plugin19' => array(
								'enable' => 1,
							),
						);
						break;
				}
				$action['data'] = json_encode($data);

				$actionValues = $this->getActionValues($action);
				$actionValuesCount = count($actionValues);
				$questionMarks = $this->getQuestionMarks($actionValuesCount);

				$statement = '
					INSERT INTO actions (
						uuid,
						name,
						type,
						data,
						status,
						shared,
						user_id,
						last_modified_date,
						creatable,
						deletable,
						edit_type,
						ordinal,
						snmp_template_id,
						smtp_template_id,
						syslog_template_id,
						hidden
					)
					VALUES (
						'.$questionMarks.'
					)
				';
				$this->query($statement, $actionValues, false);
			}
		}
	}

	/**
	 * Updates a action name
	 *
	 * @param string $oldName Name of the favorite to update
	 * @param array $newName New name of the favorite
	 * @return void
	 */
	protected function updateActionName($oldName, $newName) {
		if ($oldName && $newName) {
			$action = $this->getActionFromName($oldName);

			$actionUuid = null;
			if (isset($action['uuid'])) {
				$actionUuid = $action['uuid'];
			}

			if ($actionUuid) {
				$statement = '
					UPDATE actions SET
					name = ?,
					last_modified_date = now()
					WHERE
					uuid = ?
				';
				$params = array(
					$newName,
					$actionUuid
				);
				$this->query($statement, $params, false);
			}
		}
	}

	/**
	 *
	 * @param type $type
	 * @return type
	 */
	protected function getActionValues($actionParam) {
		$action = $this->convertBooleans($actionParam);

		$values = array(
			$action['uuid'],
			$action['name'],
			$action['type'],
			$action['data'],
			$action['status'],
			$action['shared'],
			$action['user_id'],
			$action['last_modified_date'],
			$action['creatable'],
			$action['deletable'],
			$action['edit_type'],
			$action['ordinal'],
			$action['snmp_template_id'],
			$action['smtp_template_id'],
			$action['syslog_template_id'],
			$action['hidden'],
		);
		return $values;
	}

	/**
	 * get default favorite action
	 */
	protected function getDefaultFavoriteAction() {
		$defaultFavoriteAction = array(
			'uuid' => '',
			'deepsee_favorite_uuid' => '',
			'action_uuid' => '',
			'appliance_id' => null,
			'favorite_type' => 0,
			'action_filter_uuid' => null,
		);
		return $defaultFavoriteAction;
	}

	/**
	 * insert favorite action
	 */
	protected function insertFavoriteAction($favoriteActionToInsert) {
		$favoriteNameToInsert = '';
		if (isset($favoriteActionToInsert['favorite_name'])) {
			$favoriteNameToInsert = $favoriteActionToInsert['favorite_name'];
		}

		$actionNameToInsert = '';
		if (isset($favoriteActionToInsert['action_name'])) {
			$actionNameToInsert = $favoriteActionToInsert['action_name'];
		}

		if ($actionNameToInsert && $favoriteNameToInsert) {
			$favoriteAction = $this->getDeepseeFavoriteActionFromName($favoriteNameToInsert, $actionNameToInsert);

			if (empty($favoriteAction)) {
				$action = $this->getActionFromName($actionNameToInsert);
				$favorite = $this->getDeepseeFavoriteFromName($favoriteNameToInsert);

				$actionUuid = null;
				if (isset($action['uuid'])) {
					$actionUuid = $action['uuid'];
				}

				$favoriteUuid = null;
				if (isset($favorite['uuid'])) {
					$favoriteUuid = $favorite['uuid'];
				}

				if ($actionUuid && $favoriteUuid) {
					$defaultFavoriteAction = $this->getDefaultFavoriteAction();
					$favoriteAction = array_merge($defaultFavoriteAction, $favoriteActionToInsert);

					$uuid = $this->getUuid();
					$favoriteAction['uuid'] = $uuid;
					$favoriteAction['deepsee_favorite_uuid'] = $favoriteUuid;
					$favoriteAction['action_uuid'] = $actionUuid;
					$favoriteActionValues = $this->getFavoriteActionValues($favoriteAction);
					$favoriteActionValuesCount = count($favoriteActionValues);
					$questionMarks = $this->getQuestionMarks($favoriteActionValuesCount);

					$statement = '
						INSERT INTO deepsee_favorite_actions (
							uuid,
							deepsee_favorite_uuid,
							action_uuid,
							appliance_id,
							favorite_type,
							action_filter_uuid
						)
						VALUES (
							'.$questionMarks.'
						)
					';
					$this->query($statement, $favoriteActionValues, false);
				}
			}
		}
	}

	/**
	 *
	 * @param type $type
	 * @return type
	 */
	protected function getFavoriteActionValues($favoriteActionParam) {
		$favoriteAction = $this->convertBooleans($favoriteActionParam);

		$values = array(
			$favoriteAction['uuid'],
			$favoriteAction['deepsee_favorite_uuid'],
			$favoriteAction['action_uuid'],
			$favoriteAction['appliance_id'],
			$favoriteAction['favorite_type'],
			$favoriteAction['action_filter_uuid'],
		);
		return $values;
	}

	/**
	 * get default action integration provider
	 */
	protected function getDefaultActionIntegrationProvider() {
		$defaultActionIntegrationProvider = array(
			'uuid' => '',
			'action_uuid' => '',
			'integration_provider_uuid' => '',
			'appliance_id' => 0,
			'last_modified_date' => 'now()',
		);
		return $defaultActionIntegrationProvider;
	}

	/**
	 * insert action integration provider
	 */
	protected function insertActionIntegrationProvider($actionIntegrationProviderToInsert) {
		$integrationProviderNameToInsert = '';
		if (isset($actionIntegrationProviderToInsert['integration_provider_name'])) {
			$integrationProviderNameToInsert = $actionIntegrationProviderToInsert['integration_provider_name'];
		}

		$actionNameToInsert = '';
		if (isset($actionIntegrationProviderToInsert['action_name'])) {
			$actionNameToInsert = $actionIntegrationProviderToInsert['action_name'];
		}

		if ($actionNameToInsert && $integrationProviderNameToInsert) {
			$actionIntegrationProvider = $this->getActionIntegrationProviderFromName($actionNameToInsert, $integrationProviderNameToInsert);

			if (empty($actionIntegrationProvider)) {
				$action = $this->getActionFromName($actionNameToInsert);
				$integrationProvider = $this->getIntegrationProviderFromName($integrationProviderNameToInsert);

				$actionUuid = null;
				if (isset($action['uuid'])) {
					$actionUuid = $action['uuid'];
				}

				$integrationProviderUuid = null;
				if (isset($integrationProvider['uuid'])) {
					$integrationProviderUuid = $integrationProvider['uuid'];
				}

				if ($actionUuid && $integrationProviderUuid) {
					$defaultActionIntegrationProvider = $this->getDefaultActionIntegrationProvider();
					$actionIntegrationProvider = array_merge($defaultActionIntegrationProvider, $actionIntegrationProviderToInsert);

					$uuid = $this->getUuid();
					$actionIntegrationProvider['uuid'] = $uuid;
					$actionIntegrationProvider['integration_provider_uuid'] = $integrationProviderUuid;
					$actionIntegrationProvider['action_uuid'] = $actionUuid;
					$actionIntegrationProviderValues = $this->getActionIntegrationProviderValues($actionIntegrationProvider);
					$actionIntegrationProviderValuesCount = count($actionIntegrationProviderValues);
					$questionMarks = $this->getQuestionMarks($actionIntegrationProviderValuesCount);

					$statement = '
						INSERT INTO actions_integration_providers (
							uuid,
							action_uuid,
							integration_provider_uuid,
							appliance_id,
							last_modified_date
						)
						VALUES (
							'.$questionMarks.'
						)
					';
					$this->query($statement, $actionIntegrationProviderValues, false);
				}
			}
		}
	}

	/**
	 *
	 * @param type $type
	 * @return type
	 */
	protected function getActionIntegrationProviderValues($actionIntegrationProviderParam) {
		$actionIntegrationProvider = $this->convertBooleans($actionIntegrationProviderParam);

		$values = array(
			$actionIntegrationProvider['uuid'],
			$actionIntegrationProvider['action_uuid'],
			$actionIntegrationProvider['integration_provider_uuid'],
			$actionIntegrationProvider['appliance_id'],
			$actionIntegrationProvider['last_modified_date'],
		);
		return $values;
	}

	/**
	 * update all favorite gauge paths
	 */
	public function refreshDeepseeFavoriteGaugePaths($favoriteNames) {
		$favoriteLookup = $this->getFavoriteValueClosure();

		foreach ($favoriteNames as $favoriteName) {
			$favorite = $this->getDeepseeFavoriteFromName($favoriteName);

			if (!empty($favorite)) {
				$uuid = null;
				if (isset($favorite['uuid'])) {
					$uuid = $favorite['uuid'];
				}

				$value = '';
				if (isset($favorite['value'])) {
					$value = $favorite['value'];
				}

				$pathParts = json_decode($value, true);
				if (!$pathParts) {
					$pathParts = array();
				}

				$pathPartsEncoded = json_encode($pathParts);
				$gaugePath = Latest\GaugePath::createGaugePath($pathParts, $favoriteLookup);
				if (!$gaugePath) {
					$gaugePath = '';
				}

				$lastModifiedDate = 'now()';

				$statement = '
					UPDATE
						deepsee_favorites
					SET
						value = ?,
						gauge_path = ?,
						last_modified_date = ?
					WHERE
						uuid = ?
				';
				$params = array(
					$pathPartsEncoded,
					$gaugePath,
					$lastModifiedDate,
					$uuid
				);
				$this->query($statement, $params, false);
			}
		}
	}

	/**
	 * returns a function that returns
	 * a favorite value given a favorite uuid
	 */
	public function getFavoriteValueClosure() {
		$that = $this;
		return function ($favoriteNameOrUuid) use ($that) {
			$favorite = $that->getDeepseeFavoriteFromUuid($favoriteNameOrUuid);

			$valueNew = array();
			$value = '';

			if (!empty($favorite)) {
				$value = $favorite['value'];
				$valueNew = json_decode($value, true);
			}
			return $valueNew;
		};
	}

	/**
	 * get the table columns
	 */
	public function getTableColumnNames($tableName) {
		$columnNames = array();
		if ($tableName) {
			$statement = '
				SELECT
					column_name
				FROM information_schema.columns
				WHERE
					table_name = ?
			';
			$params = array(
				$tableName
			);
			$useCache = false;
			$results = $this->query($statement, $params, $useCache);
			if ($results) {
				foreach ($results as $result) {
					if (isset($result[0]['column_name'])) {
						$columnNames[] = $result[0]['column_name'];
					}
				}
			}
		}
		return $columnNames;
	}

	/**
	 * get malware integration provider uuid
	 */
	public function getMalwareIntegrationProviderUuid() {
		$integrationProviderUuid = null;
		$integrationProviderCategoryName = 'malware';
		$integrationProviderTypeInternalName = 'norman';

		$statement = '
			SELECT
				ip.uuid
			FROM
				integration_providers AS ip
			INNER JOIN integration_provider_types AS ipt ON ipt.uuid = ip.integration_provider_type_uuid
			INNER JOIN integration_provider_categories AS ipc ON ipc.uuid = ip.integration_provider_category_uuid
			INNER JOIN integration_provider_type_field_sets AS iptfs ON ipt.integration_provider_type_field_set_uuid = iptfs.uuid
			WHERE
				ipc.name = ?
				AND ipt.internal_name = ?
			LIMIT 1
		';
		$params = array(
			$integrationProviderCategoryName,
			$integrationProviderTypeInternalName
		);
		$useCache = false;
		$results = $this->query($statement, $params, $useCache);
		if (isset($results[0][0]['uuid'])) {
			$integrationProviderUuid = $results[0][0]['uuid'];
		}
		return $integrationProviderUuid;
	}

	protected function deleteDuplicateDeepseeFavorites() {
		$isCmc = $this->getIsCmc();
		if (!$isCmc) {
			$params = array();
			$query = "
				DELETE
				FROM
					deepsee_favorites AS df
				WHERE
					df.uuid IN (
						SELECT
							df2.uuid
						FROM (
							SELECT
								df1.uuid,
								ROW_NUMBER() OVER (partition BY lower(df1.name) ORDER BY df1.last_modified_date DESC) AS row_num
							FROM
								deepsee_favorites AS df1
							WHERE
								df1.appliance_id IS NULL
						) AS df2
						WHERE
							df2.row_num > 1
					)
			";
			// $this->DeepseeFavorite->debugQuery($query, $params);
			$useCache = false;
			$this->DeepseeFavorite->query($query, $params, $useCache);
		}
	}
}
