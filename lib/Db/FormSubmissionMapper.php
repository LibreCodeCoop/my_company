<?php

namespace OCA\MyCompany\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class FormSubmissionMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'forms_v2_submissions');
	}

	/**
	 * @return array
	 * @throws DoesNotExistException if not found
	 */
	public function getAnswersOfNewerstSubmission(int $formId, string $uid): array {
		$qb = $this->db->getQueryBuilder();
		$subselect = $this->db->getQueryBuilder();

		$subselect->select('s.id')
			->from('forms_v2_submissions', 's')
			->where($subselect->expr()->eq('s.form_id', $qb->createNamedParameter($formId, IQueryBuilder::PARAM_INT)))
			->andWhere($subselect->expr()->eq('s.user_id', $qb->createNamedParameter($uid)))
			->orderBy('timestamp', 'desc')
			->setMaxResults(1);

		$qb->select('q.type')
			->addSelect('q.name')
			->addSelect('a.text')
			->addSelect('s.timestamp')
			->from('forms_v2_questions', 'q')
			->join('q', 'forms_v2_answers', 'a', $qb->expr()->eq('a.question_id', 'q.id'))
			->join ('a', 'forms_v2_submissions', 's', $qb->expr()->eq('s.id', 'a.submission_id'))
			->where($qb->expr()->eq('s.id', $qb->createFunction('(' . $subselect->getSQL() . ')')));

		$stmt = $qb->executeQuery();

		$result = [];
		while ($row = $stmt->fetch()) {
			$result[] = $row;
		}
		return $result;
	}
}
