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

namespace OCA\MyCompany\Datasource;

use OCA\Analytics\Datasource\IDatasource;
use OCA\MyCompany\Service\CompanyService;
use OCP\IConfig;
use OCP\IL10N;

class AdminAudit implements IDatasource {
	public function __construct(
		private IL10N $l10n,
		private IConfig $config,
		private CompanyService $companyService,
	) {
	}

	/**
	 * @return string Display Name of the datasource
	 */
	public function getName(): string {
		// TRANSLATORS The report name to display file access logs in the app Analytics
		return $this->l10n->t('App: Admin Audit');
	}

	/**
	 * @return int digit unique datasource id
	 */
	public function getId(): int {
		return 6;
	}

	/**
	 * @return array available options of the datasoure
	 */
	public function getTemplate(): array {
		return [];
	}

	/**
	 * Read the Data
	 * @param $option
	 * @return array available options of the datasoure
	 */
	public function readData($option): array {
		$company = $this->companyService->getCompanyFolder();
		\OC::$server->getLogger()->debug('############### Company', [$company]);
		$userAdmin = $this->companyService->getUserAdminFolder();
		\OC::$server->getLogger()->debug('############### user', [$userAdmin]);
		$default = $this->config->getSystemValue('datadirectory', \OC::$SERVERROOT . '/data') . '/audit.log';
		$logFile = $this->config->getAppValue('admin_audit', 'logfile', $default);

		$fp = fopen($logFile, 'r');
		$i = 1;
		while (($buffer = fgets($fp, 4096)) !== false) {
			$json = json_decode($buffer, true);
			if (str_contains($json['message'], '.directory')) {
				continue;
			}
			if (!str_contains($json['message'], 'File accessed')) {
				continue;
			}
			preg_match('/File accessed: "(?<file>.*)"/', $json['message'], $matches);
			$data[] = [
				$json['user'],
				$json['time'],
				$matches['file'],
				$json['remoteAddr'],
				$json['userAgent'],
				$i,
			];
			$i++;
		}
		fclose($fp);

		$header = [
			// TRANSLATORS Column name of report that list log entries of app Audit Report. This column will display the user that trigged the log.
			$this->l10n->t('User'),
			// TRANSLATORS Column name of report that list log entries of app Audit Report. This column will display the date of log.
			$this->l10n->t('Date'),
			// TRANSLATORS Column name of report that list log entries of app Audit Report. This column will display the acessed file.
			$this->l10n->t('File'),
			// TRANSLATORS Column name of report that list log entries of app Audit Report. This column will display the IP of user that trigged the log.
			$this->l10n->t('IP'),
			// TRANSLATORS Column name of report that list log entries of app Audit Report. This column will display the user agent of user that trigged the log.
			$this->l10n->t('userAgent'),
			// TRANSLATORS Column name of report that list log entries of app Audit Report. This column will display a sequencial number of rows to be displayed in report.
			$this->l10n->t('row'),
		];

		return [
			'header' => $header,
			'dimensions' => array_slice($header, 0, count($header) - 1),
			'data' => $data,
			'error' => 0,
		];
	}
}
