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

namespace OCA\MyCompany\Service;

use InvalidArgumentException;
use OCA\GroupFolders\Folder\FolderManager;
use OCA\GroupFolders\Mount\MountProvider;
use OCP\Files\Folder;
use OCP\Files\IAppData;
use OCP\Files\IRootFolder;
use OCP\Files\NotFoundException;
use OCP\Files\SimpleFS\ISimpleFolder;
use OCP\IConfig;
use OCP\IDBConnection;
use OCP\IGroup;
use OCP\IGroupManager;
use OCP\IL10N;
use OCP\IRequest;
use OCP\IUserSession;

class CompanyService {

	public function __construct(
		private IRequest $request,
		private IDBConnection $db,
		private IRootFolder $rootFolder,
		private IUserSession $userSession,
		private IAppData $appData,
		private FolderManager $groupFolderManager,
		private IGroupManager $groupManager,
		private MountProvider $groupFolderMountProvider,
		private IConfig $config,
		private IL10N $l,
	) {
	}

	public function add(string $code, string $name = '', string $domain = '', bool $force = true): void {
		$code = $this->slugify($code);
		if (!empty($domain)) {
			list($codeFromDomain) = explode('.', $domain);
			if ($codeFromDomain !== $code) {
				throw new InvalidArgumentException('The subdomain need to be equal to the code.');
			}
		} else {
			$domain = $code . '.' . $this->request->getServerHost();
		}
		$group = $this->groupManager->get($code);
		if ($group instanceof IGroup) {
			if (!$force) {
				throw new InvalidArgumentException('Already exists a company with this code');
			}
		} else {
			$group = $this->groupManager->createGroup($code);
			if ($group === null) {
				throw new InvalidArgumentException('Not supported by backend');
			}
		}
		if (!empty($name) && $group->getDisplayName() !== $name) {
			$group->setDisplayName($name);
		}

		$trustedDomains = $this->config->getSystemValue('trusted_domains');

		$exists = array_filter($trustedDomains, fn ($host) => str_contains($host, $domain));
		if (!$exists) {
			if (empty($domain)) {
				$trustedDomains[] = $code . '.' . $this->request->getServerHost();
			} else {
				$trustedDomains[] = $domain;
			}
			$this->config->setSystemValue('trusted_domains', $trustedDomains);
		}
	}

	public function disable(string $code): void {
		$code = $this->slugify($code);
		if (!$this->groupManager->groupExists($code)) {
			throw new InvalidArgumentException('Company not found with this code');
		}
		$trustedDomains = $this->config->getSystemValue('trusted_domains');
		$toRemove = array_filter($trustedDomains, fn ($host) => str_contains($host, $code));
		$trustedDomains = array_filter($trustedDomains, fn ($host) => $host !== $toRemove);
		$this->config->setSystemValue('trusted_domains', $trustedDomains);
	}

	private function slugify(string $text): string {
		// replace everything except alphanumeric with a single '-'
		$text = preg_replace('/[^A-Za-z0-9]+/', '-', $text);
		$text = strtolower($text);
		return trim($text, '-');
	}

	public function getCompanyCode(): string {
		$host = $this->request->getServerHost();
		list($subdomain) = explode('.', $host);
		return $subdomain;
	}

	public function getCompanyFolder(string $type = ''): Folder {
		$folderId = $this->getGroupFolderIdFromCompanyCode($this->getCompanyCode(), $type);
		$folder = $this->groupFolderMountProvider->getFolder($folderId);
		return $folder;
	}

	public function getUserAdminFolder(): Folder {
		$companyFolder = $this->getCompanyFolder('admin');
		$username = $this->userSession->getUser()->getUID();
		try {
			return $companyFolder->get($username);
		} catch (NotFoundException $e) {
			return $companyFolder->newFolder($username);
		}
	}

	public function getUserAdminRegistrationFolder(): Folder {
		$userFolder = $this->getUserAdminFolder();
		// TRANSLATORS Folder name that contains all documents of employee. Inside this folder, by example, will be saved the registratin form signed.
		$registerFilesFolderName = $this->l->t('register-files');
		try {
			return $userFolder->get($registerFilesFolderName);
		} catch (NotFoundException $e) {
			return $userFolder->newFolder($registerFilesFolderName);
		}
	}

	public function getThemeFolder(): ISimpleFolder {
		try {
			return $this->appData->getFolder('themes');
		} catch (NotFoundException $e) {
			return $this->appData->newFolder('themes');
		}
	}

	private function getGroupFolderIdFromCompanyCode(string $companyCode, string $type = ''): int {
		if (!$this->groupManager->groupExists($companyCode)) {
			throw new \Exception('Company not allowed to use this system');
		}

		$mountPointName = $companyCode . ($type ? '-' . $type : '');

		if ($mountPointName !== $companyCode) {
			if (!$this->groupManager->groupExists($mountPointName)) {
				$this->groupManager->createGroup($mountPointName);
			}
		}

		$query = $this->db->getQueryBuilder();
		$query->select('folder_id')
			->from('group_folders')
			->where($query->expr()->eq('mount_point', $query->createNamedParameter($mountPointName)));
		$result = $query->executeQuery();
		$folderId = $result->fetchOne();

		if (!$folderId) {
			$folderId = $this->groupFolderManager->createFolder($mountPointName);
			$this->groupFolderManager->addApplicableGroup($folderId, $mountPointName);
		}
		return (int) $folderId;
	}
}
