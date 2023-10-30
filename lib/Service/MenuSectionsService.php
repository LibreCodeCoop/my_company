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
use OCP\Files\NotPermittedException;
use OCP\IEmojiHelper;
use OCP\IURLGenerator;
use OCP\IUserSession;

class MenuSectionsService {
	/**
	 * https://github.com/sebdesign/cap-height -- for 500px height
	 * Automated check: https://codepen.io/skjnldsv/pen/PydLBK/
	 * Noto Sans cap-height is 0.715 and we want a 200px caps height size
	 * (0.4 letter-to-total-height ratio, 500*0.4=200), so: 200/0.715 = 280px.
	 * Since we start from the baseline (text-anchor) we need to
	 * shift the y axis by 100px (half the caps height): 500/2+100=350
	 *
	 * Copied from @see \OC\Avatar\Avatar::$svgTemplate with some changes:
	 * - {font} is injected
	 * - size fixed to 512
	 * - font-size reduced to 240
	 * - font-weight and fill color are removed as they are not applicable
	 */
	private string $svgTemplate = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
		<svg width="512" height="512" version="1.1" viewBox="0 0 500 500" xmlns="http://www.w3.org/2000/svg">
			<text x="50%" y="330" style="font-size:240px;font-family:{font};text-anchor:middle;">{letter}</text>
		</svg>';

	public function __construct(
		private IUserSession $userSession,
		private ShareMapper $shareMapper,
		private IRootFolder $rootFolder,
		private IAppData $appData,
		private IURLGenerator $urlGenerator,
		private IEmojiHelper $emojiHelper,
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
					if ($this->emojiHelper->isValidSingleEmoji($parsed['Icon'])) {
						$list[$key]['icon'] = $this->getEmojiAvatar($parsed['Icon']);
					} else {
						$iconsFolder = $this->appData->getFolder('icons');
						$icon = $iconsFolder->getFile($parsed['Icon'] . '.svg');
						$list[$key]['icon'] = $icon->getContent();
					}
				}
			} catch (NotFoundException | NotPermittedException $e) {
			}
		}
		return $list;
	}

	private function getEmojiAvatar(string $emoji, string $fillColor = 'ffffff'): string {
		return str_replace([
			'{letter}',
			'{fill}',
			'{font}',
		], [
			$emoji,
			$fillColor,
			implode(',', [
				"'Segoe UI'",
				'Roboto',
				'Oxygen-Sans',
				'Cantarell',
				'Ubuntu',
				"'Helvetica Neue'",
				'Arial',
				'sans-serif',
				"'Noto Color Emoji'",
				"'Apple Color Emoji'",
				"'Segoe UI Emoji'",
				"'Segoe UI Symbol'",
				"'Noto Sans'",
			]),
		], $this->svgTemplate);
	}
}
