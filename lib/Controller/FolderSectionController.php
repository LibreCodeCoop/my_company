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

namespace OCA\MyCompany\Controller;

use OCA\Files\Activity\Helper;
use OCA\Files\AppInfo\Application as FilesAppInfoApplication;
use OCA\Files\Event\LoadAdditionalScriptsEvent;
use OCA\Files\Event\LoadSidebar;
use OCA\MyCompany\AppInfo\Application;
use OCA\Viewer\Event\LoadViewer;
use OCP\App\IAppManager;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\Collaboration\Resources\LoadAdditionalScriptsEvent as ResourcesLoadAdditionalScriptsEvent;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\Files\Folder;
use OCP\Files\IRootFolder;
use OCP\Files\Template\ITemplateManager;
use OCP\IConfig;
use OCP\IInitialStateService;
use OCP\IRequest;
use OCP\IUserSession;
use OCP\Share\IManager;

class FolderSectionController extends Controller {
	public function __construct(
		IRequest $request,
		private IUserSession $userSession,
		private Helper $activityHelper,
		private IConfig $config,
		private IInitialStateService $initialState,
		private IManager $shareManager,
		private IEventDispatcher $eventDispatcher,
		private ITemplateManager $templateManager,
		private IRootFolder $rootFolder,
		private IAppManager $appManager,
	) {
		parent::__construct(Application::APP_ID, $request);
	}
	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function section(string $fileid): TemplateResponse {
		$uid = $this->userSession->getUser()->getUID();
		$baseFolder = $this->rootFolder->getUserFolder($uid);
		$files = $baseFolder->getById($fileid);
		$params = [];

		if (empty($files) && $this->appManager->isEnabledForUser('files_trashbin')) {
			$baseFolder = $this->rootFolder->get($uid . '/files_trashbin/files/');
			$files = $baseFolder->getById($fileid);
			$params['view'] = 'trashbin';
		}

		if (!empty($files)) {
			$file = current($files);
			if ($file instanceof Folder) {
				// set the full path to enter the folder
				$params['dir'] = $baseFolder->getRelativePath($file->getPath());
			} else {
				// set parent path as dir
				$params['dir'] = $baseFolder->getRelativePath($file->getParent()->getPath());
				// and scroll to the entry
				$params['scrollto'] = $file->getName();
			}

			$fileListTemplate = $this->showFileList(...$params);
			return $fileListTemplate;
		}
		throw new \OCP\Files\NotFoundException();

	}

	private function showFileList($dir = '', $view = '', $fileNotFound = false) {
		// Load the files we need
		\OCP\Util::addStyle('my_company', 'filesIndex');
		\OCP\Util::addStyle('files', 'merged');
		\OCP\Util::addScript('files', 'merged-index', 'files');
		\OCP\Util::addScript('files', 'main');

		$userId = $this->userSession->getUser()->getUID();

		// Get all the user favorites to create a submenu
		try {
			$favElements = $this->activityHelper->getFavoriteFilePaths($userId);
		} catch (\RuntimeException $e) {
			$favElements['folders'] = [];
		}

		try {
			// If view is files, we use the directory, otherwise we use the root storage
			$storageInfo = $this->getStorageInfo(($view === 'files' && $dir) ? $dir : '/');
		} catch(\Exception $e) {
			$storageInfo = $this->getStorageInfo();
		}

		$this->initialState->provideInitialState(FilesAppInfoApplication::APP_ID, 'storageStats', $storageInfo);
		$this->initialState->provideInitialState(FilesAppInfoApplication::APP_ID, 'config', [
			'show_hidden' => false
		]);
		$this->initialState->provideInitialState(FilesAppInfoApplication::APP_ID, 'viewConfigs', []);
		$this->initialState->provideInitialState(FilesAppInfoApplication::APP_ID, 'favoriteFolders', $favElements['folders'] ?? []);

		// File sorting user config
		$filesSortingConfig = json_decode($this->config->getUserValue($userId, 'files', 'files_sorting_configs', '{}'), true);
		$this->initialState->provideInitialState(FilesAppInfoApplication::APP_ID, 'filesSortingConfig', $filesSortingConfig);

		$event = new LoadAdditionalScriptsEvent();
		$this->eventDispatcher->dispatchTyped($event);
		$this->eventDispatcher->dispatchTyped(new ResourcesLoadAdditionalScriptsEvent());
		$this->eventDispatcher->dispatchTyped(new LoadSidebar());
		// Load Viewer scripts
		if (class_exists(LoadViewer::class)) {
			$this->eventDispatcher->dispatchTyped(new LoadViewer());
		}

		$this->initialState->provideInitialState(FilesAppInfoApplication::APP_ID, 'templates_path', $this->templateManager->hasTemplateDirectory() ? $this->templateManager->getTemplatePath() : false);
		$this->initialState->provideInitialState(FilesAppInfoApplication::APP_ID, 'templates', $this->templateManager->listCreators());

		$params = [
			'fileNotFound' => $fileNotFound ? 1 : 0
		];

		$response = new TemplateResponse(
			FilesAppInfoApplication::APP_ID,
			'index',
			$params
		);
		$policy = new ContentSecurityPolicy();
		$policy->addAllowedFrameDomain('\'self\'');
		// Allow preview service worker
		$policy->addAllowedWorkerSrcDomain('\'self\'');
		$response->setContentSecurityPolicy($policy);

		return $response;
	}

	/**
	 * @param string $appName
	 * @param string $scriptName
	 * @return string
	 */
	private function renderScript($appName, $scriptName) {
		$content = '';
		$appPath = \OC_App::getAppPath($appName);
		$scriptPath = $appPath . '/' . $scriptName;
		if (file_exists($scriptPath)) {
			// TODO: sanitize path / script name ?
			ob_start();
			include $scriptPath;
			$content = ob_get_contents();
			@ob_end_clean();
		}

		return $content;
	}

	/**
	 * FIXME: Replace with non static code
	 *
	 * @return array
	 * @throws \OCP\Files\NotFoundException
	 */
	private function getStorageInfo(string $dir = '/') {
		$rootInfo = \OC\Files\Filesystem::getFileInfo('/', false);

		return \OC_Helper::getStorageInfo($dir, $rootInfo ?: null);
	}
}
