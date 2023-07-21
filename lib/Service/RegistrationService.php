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
use OC\Files\Filesystem;
use OCP\Files\File;
use OCP\Files\Folder;
use OCP\Files\IRootFolder;
use OCP\Files\NotFoundException;
use OCP\IL10N;
use OCP\IUserSession;

class RegistrationService {
	public function __construct(
		private IL10N $l,
		private IRootFolder $rootFolder,
		private IUserSession $userSession,
	) {
	}
	public function uploadPdf(?array $file): void {
		$userFolder = $this->getUserFolder();
		if (
			$file['error'] !== 0 ||
			!is_uploaded_file($file['tmp_name']) ||
			Filesystem::isFileBlacklisted($file['tmp_name'])
		) {
			throw new InvalidArgumentException($this->l->t('Invalid file provided'));
		}
		if ($file['size'] > 2 * 1024 * 1024) {
			throw new InvalidArgumentException($this->l->t('File is too big'));
		}
		$content = file_get_contents($file['tmp_name']);
		unlink($file['tmp_name']);
		$userFolder->newFile('matricula.pdf', $content);
	}

	private function getUserFolder(): Folder {
		try {
			$folder = $this->rootFolder->get('/__groupfolders/2');
		} catch (NotFoundException $e) {
			$folder = $this->rootFolder->newFolder('/__groupfolders/2');
		}
		$username = $this->userSession->getUser()->getUID();
		try {
			return $folder->get($username);
		} catch (NotFoundException $e) {
			return $folder->newFolder($username);
		}
	}

	public function getRegistrationFile(): File {
		$folder = $this->getUserFolder();
		return $folder->get('matricula.pdf');
	}
}
