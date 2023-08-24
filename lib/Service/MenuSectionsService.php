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

use OC\Files\Node\File;
use OCA\MyCompany\Db\ShareMapper;
use OCP\Files\IAppData;
use OCP\Files\IRootFolder;
use OCP\Files\NotFoundException;
use OCP\IURLGenerator;
use OCP\IUserSession;

class MenuSectionsService {
	public function __construct(
		private IUserSession $userSession,
		private ShareMapper $shareMapper,
		private IRootFolder $rootFolder,
		private IAppData $appData,
		private IURLGenerator $urlGenerator,
	) {
	}

	public function getList(): array {
		$items = $this->shareMapper->getShareMenuOfUser($this->userSession->getUser()->getUID());

		$uid = $this->userSession->getUser()->getUID();
		$baseFolder = $this->rootFolder->getUserFolder($uid);

		$list = [];
		foreach ($items as $key => $item) {
			$files = $baseFolder->getById($item['file_id']);
			$list[$key]['id'] = $item['file_id'];
			$list[$key]['name'] = ltrim($item['path'], '/');
			$list[$key]['url'] = $this->urlGenerator->linkToRoute('my_company.FolderSection.section', [
				'fileid' => $item['file_id'],
				'dir' => $item['path'],
			]) . '&fileid=' . $item['file_id'];
			try {
				/** @var File */
				$directoryFile = $files[0]->get('.directory');
				$content = $directoryFile->getContent();
				$parsed = parse_ini_string($content);
				if (!empty($parsed['Icon'])) {
					$iconsFolder = $this->appData->getFolder('icons');
					$icon = $iconsFolder->getFile($parsed['Icon'] . '.svg');
					$list[$key]['icon'] = $icon->getContent();
				}
			} catch (NotFoundException $e) {
			}
		}
		return $list;
	}
}
