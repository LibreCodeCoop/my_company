<?php

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
