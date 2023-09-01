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
		$nav = new \OCP\Template('files', 'appnavigation', '');

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

		$collapseClasses = '';
		if (count($favElements['folders']) > 0) {
			$collapseClasses = 'collapsible';
		}

		$favoritesSublistArray = [];

		$navBarPositionPosition = 6;
		foreach ($favElements['folders'] as $favElement) {
			$element = [
				'id' => str_replace('/', '-', $favElement),
				'dir' => $favElement,
				'order' => $navBarPositionPosition,
				'name' => basename($favElement),
				'icon' => 'folder',
				'params' => [
					'view' => 'files',
					'dir' => $favElement,
				],
			];

			array_push($favoritesSublistArray, $element);
			$navBarPositionPosition++;
		}

		$navItems = \OCA\Files\App::getNavigationManager()->getAll();

		// add the favorites entry in menu
		$navItems['favorites']['sublist'] = $favoritesSublistArray;
		$navItems['favorites']['classes'] = $collapseClasses;

		// parse every menu and add the expanded user value
		foreach ($navItems as $key => $item) {
			$navItems[$key]['expanded'] = $this->config->getUserValue($userId, 'files', 'show_' . $item['id'], '0') === '1';
		}

		$nav->assign('navigationItems', $navItems);

		$contentItems = [];

		try {
			// If view is files, we use the directory, otherwise we use the root storage
			$storageInfo = $this->getStorageInfo(($view === 'files' && $dir) ? $dir : '/');
		} catch(\Exception $e) {
			$storageInfo = $this->getStorageInfo();
		}

		$this->initialState->provideInitialState(FilesAppInfoApplication::APP_ID, 'storageStats', $storageInfo);
		$this->initialState->provideInitialState(FilesAppInfoApplication::APP_ID, 'navigation', $navItems);
		$this->initialState->provideInitialState(FilesAppInfoApplication::APP_ID, 'config', [
			'show_hidden' => false
		]);
		$this->initialState->provideInitialState(FilesAppInfoApplication::APP_ID, 'viewConfigs', []);

		// File sorting user config
		$filesSortingConfig = json_decode($this->config->getUserValue($userId, 'files', 'files_sorting_configs', '{}'), true);
		$this->initialState->provideInitialState(FilesAppInfoApplication::APP_ID, 'filesSortingConfig', $filesSortingConfig);

		// render the container content for every navigation item
		foreach ($navItems as $item) {
			$content = '';
			if (isset($item['script'])) {
				$content = $this->renderScript($item['appname'], $item['script']);
			}
			// parse submenus
			if (isset($item['sublist'])) {
				foreach ($item['sublist'] as $subitem) {
					$subcontent = '';
					if (isset($subitem['script'])) {
						$subcontent = $this->renderScript($subitem['appname'], $subitem['script']);
					}
					$contentItems[$subitem['id']] = [
						'id' => $subitem['id'],
						'content' => $subcontent
					];
				}
			}
			$contentItems[$item['id']] = [
				'id' => $item['id'],
				'content' => $content
			];
		}

		$this->eventDispatcher->dispatchTyped(new ResourcesLoadAdditionalScriptsEvent());
		$event = new LoadAdditionalScriptsEvent();
		$this->eventDispatcher->dispatchTyped($event);
		$this->eventDispatcher->dispatchTyped(new LoadSidebar());
		// Load Viewer scripts
		if (class_exists(LoadViewer::class)) {
			$this->eventDispatcher->dispatchTyped(new LoadViewer());
		}

		$this->initialState->provideInitialState(FilesAppInfoApplication::APP_ID, 'templates_path', $this->templateManager->hasTemplateDirectory() ? $this->templateManager->getTemplatePath() : false);
		$this->initialState->provideInitialState(FilesAppInfoApplication::APP_ID, 'templates', $this->templateManager->listCreators());

		$params = [];
		$params['usedSpacePercent'] = (int) $storageInfo['relative'];
		$params['owner'] = $storageInfo['owner'] ?? '';
		$params['ownerDisplayName'] = $storageInfo['ownerDisplayName'] ?? '';
		$params['isPublic'] = false;
		$params['allowShareWithLink'] = $this->shareManager->shareApiAllowLinks() ? 'yes' : 'no';
		$params['defaultFileSorting'] = $filesSortingConfig['files']['sorting_mode'] ?? 'basename';
		$params['defaultFileSortingDirection'] = $filesSortingConfig['files']['sorting_direction'] ?? 'asc';
		$params['showgridview'] = $this->config->getUserValue($userId, 'files', 'show_grid', false);
		$showHidden = (bool) $this->config->getUserValue($userId, 'files', 'show_hidden', false);
		$params['showHiddenFiles'] = $showHidden ? 1 : 0;
		$cropImagePreviews = (bool) $this->config->getUserValue($userId, 'files', 'crop_image_previews', true);
		$params['cropImagePreviews'] = $cropImagePreviews ? 1 : 0;
		$params['fileNotFound'] = $fileNotFound ? 1 : 0;
		$params['appNavigation'] = $nav;
		$params['appContents'] = $contentItems;
		$params['hiddenFields'] = $event->getHiddenFields();

		$response = new TemplateResponse(
			FilesAppInfoApplication::APP_ID,
			'index',
			$params
		);
		$policy = new ContentSecurityPolicy();
		$policy->addAllowedFrameDomain('\'self\'');
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
