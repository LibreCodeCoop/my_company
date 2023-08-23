<?php

declare(strict_types=1);
/**
 * @copyright Copyright (c) 2023, Vitor Mattos <vitor@php.rio>
 *
 * @author Vitor Mattos <vitor@php.rio>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\MyCompany\Db;

use OCA\MyCompany\AppInfo\Application;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
use OCP\IConfig;
use OCP\IDBConnection;

/**
 * @template-extends QBMapper<Entity>
 */
class ShareMapper extends QBMapper {
	public function __construct(
		IDBConnection $db,
		private IConfig $config,
	) {
		parent::__construct($db, 'share');
	}

	public function getShareMenuOfUser(string $uid): array {
		$qb = $this->db->getQueryBuilder();
		$qb->selectAlias('s.item_source', 'file_id')
			->selectAlias('s.file_target', 'path')
			->from('share', 's')
			->join('s', 'systemtag_object_mapping', 'som', $qb->expr()->andX(
				$qb->expr()->eq('som.objecttype', $qb->createNamedParameter('files')),
				$qb->expr()->eq('s.item_source', 'som.objectid')
			))
			->join('som', 'systemtag', 'st', $qb->expr()->eq('st.id', 'som.systemtagid'))
			->where($qb->expr()->eq('s.item_type', $qb->createNamedParameter('folder')))
			->andWhere($qb->expr()->eq('s.share_with', $qb->createNamedParameter($uid)))
			->andWhere($qb->expr()->eq('st.name', $qb->createNamedParameter(
				$this->config->getAppValue(Application::APP_ID, 'tag_menu', 'menu')
			)));
		$stmt = $qb->executeQuery();
		$result = [];
		while ($row = $stmt->fetch()) {
			$result[] = $row;
		}
		return $result;
	}
}
