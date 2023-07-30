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
	 */
	public function getAnswersOfNewerstSubmission(int $formId, string $uid): array {
		$qb = $this->db->getQueryBuilder();
		$order = $this->db->getQueryBuilder();
		$submission = $this->db->getQueryBuilder();

		$order->select($order->func()->count('*'))
			->from('forms_v2_options')
			->where($order->expr()->lte('id', 'o.id'))
			->andWhere($order->expr()->eq('question_id', 'q.id'));

		$submission->select('s.id')
			->from('forms_v2_submissions', 's')
			->where($submission->expr()->eq('s.form_id', $qb->createNamedParameter($formId, IQueryBuilder::PARAM_INT)))
			->andWhere($submission->expr()->eq('s.user_id', $qb->createNamedParameter($uid)))
			->orderBy('timestamp', 'desc')
			->setMaxResults(1);

		$qb->select('q.type')
			->addSelect('q.name')
			->addSelect('a.text')
			->addSelect('s.timestamp')
			->addSelect($qb->createFunction('(' . $order->getSQL() . ') AS ' . $qb->quoteAlias('order')))
			->from('forms_v2_questions', 'q')
			->join('q', 'forms_v2_answers', 'a', $qb->expr()->eq('a.question_id', 'q.id'))
			->join('a', 'forms_v2_submissions', 's', $qb->expr()->eq('s.id', 'a.submission_id'))
			->leftJoin('q', 'forms_v2_options', 'o', $qb->expr()->andX(
				$qb->expr()->eq('o.question_id', 'q.id'),
				$qb->expr()->eq('o.text', 'a.text')
			))
			->where($qb->expr()->eq('s.id', $qb->createFunction('(' . $submission->getSQL() . ')')));

		$stmt = $qb->executeQuery();

		$result = [];
		while ($row = $stmt->fetch()) {
			$row['order'] = $row['order'] == '0' ? null : (int) $row['order'];
			$result[] = $row;
		}
		return $result;
	}
}
