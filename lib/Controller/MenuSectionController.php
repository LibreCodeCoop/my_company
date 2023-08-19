<?php

namespace OCA\MyCompany\Controller;

use OCA\Files\Activity\Helper;
use OCA\Files\AppInfo\Application as FilesAppInfoApplication;
use OCA\Files\Event\LoadAdditionalScriptsEvent;
use OCA\MyCompany\AppInfo\Application;
use OCA\Viewer\Event\LoadViewer;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IInitialState;
use OCP\Collaboration\Resources\LoadAdditionalScriptsEvent as ResourcesLoadAdditionalScriptsEvent;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\Files\Template\ITemplateManager;
use OCP\IConfig;
use OCP\IRequest;
use OCP\IUserSession;
use OCP\Share\IManager;

class MenuSectionController extends Controller {
	public function __construct(
		IRequest $request,
		private IUserSession $userSession,
		private Helper $activityHelper,
		private IConfig $config,
		private IInitialState $initialState,
		private IManager $shareManager,
		private IEventDispatcher $eventDispatcher,
		private ITemplateManager $templateManager,
	) {
		parent::__construct(Application::APP_ID, $request);
	}
	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function section(string $fileId): TemplateResponse {
		$fileListTemplate = $this->showFileList(
			fileId: $fileId,
		);
		return $fileListTemplate;
	}

	private function showFileList($dir = '', $view = '', $fileId = null, $fileNotFound = false, $openfile = null) {
		$nav = new \OCP\Template('files', 'appnavigation', '');

		// Load the files we need
		\OCP\Util::addStyle('files', 'merged');
		\OCP\Util::addScript('files', 'merged-index', 'files');
		\OCP\Util::addScript('files', 'main');

		$userId = $this->userSession->getUser()->getUID();

		$nav->assign('navigationItems', []);

		$contentItems = [];

		try {
			// If view is files, we use the directory, otherwise we use the root storage
			$storageInfo =  $this->getStorageInfo(($view === 'files' && $dir) ? $dir : '/');
		} catch(\Exception $e) {
			$storageInfo = $this->getStorageInfo();
		}

		$this->initialState->provideInitialState('storageStats', $storageInfo);
		$this->initialState->provideInitialState('navigation', []);
		$this->initialState->provideInitialState('config', []);
		$this->initialState->provideInitialState('viewConfigs', []);
		$this->initialState->provideInitialState('favoriteFolders', []);

		// File sorting user config
		$filesSortingConfig = json_decode($this->config->getUserValue($userId, 'files', 'files_sorting_configs', '{}'), true);
		$this->initialState->provideInitialState('filesSortingConfig', $filesSortingConfig);

		$this->eventDispatcher->dispatchTyped(new ResourcesLoadAdditionalScriptsEvent());
		$event = new LoadAdditionalScriptsEvent();
		$this->eventDispatcher->dispatchTyped($event);
		// Load Viewer scripts
		if (class_exists(LoadViewer::class)) {
			$this->eventDispatcher->dispatchTyped(new LoadViewer());
		}

		$this->initialState->provideInitialState('templates_path', $this->templateManager->hasTemplateDirectory() ? $this->templateManager->getTemplatePath() : false);
		$this->initialState->provideInitialState('templates', $this->templateManager->listCreators());

		$params = [];
		$params['usedSpacePercent'] = (int) $storageInfo['relative'];
		$params['owner'] = $storageInfo['owner'] ?? '';
		$params['ownerDisplayName'] = $storageInfo['ownerDisplayName'] ?? '';
		$params['isPublic'] = false;
		$params['allowShareWithLink'] = $this->shareManager->shareApiAllowLinks() ? 'yes' : 'no';
		$params['defaultFileSorting'] = $filesSortingConfig['files']['mode'] ?? 'basename';
		$params['defaultFileSortingDirection'] = $filesSortingConfig['files']['direction'] ?? 'asc';
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

		$this->provideInitialState($dir, $openfile);

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

	/**
	 * Add openFileInfo in initialState if $openfile is set.
	 * @param string $dir - the ?dir= URL param
	 * @param string $openfile - the ?openfile= URL param
	 * @return void
	 */
	private function provideInitialState(string $dir, ?string $openfile): void {
		if ($openfile === null) {
			return;
		}

		$user = $this->userSession->getUser();

		if ($user === null) {
			return;
		}

		$uid = $user->getUID();
		$userFolder = $this->rootFolder->getUserFolder($uid);
		$nodes = $userFolder->getById((int) $openfile);
		$node = array_shift($nodes);

		if ($node === null) {
			return;
		}

		// properly format full path and make sure
		// we're relative to the user home folder
		$isRoot = $node === $userFolder;
		$path = $userFolder->getRelativePath($node->getPath());
		$directory = $userFolder->getRelativePath($node->getParent()->getPath());

		// Prevent opening a file from another folder.
		if ($dir !== $directory) {
			return;
		}

		$this->initialState->provideInitialState(
			'openFileInfo', [
				'id' => $node->getId(),
				'name' => $isRoot ? '' : $node->getName(),
				'path' => $path,
				'directory' => $directory,
				'mime' => $node->getMimetype(),
				'type' => $node->getType(),
				'permissions' => $node->getPermissions(),
			]
		);
	}
}
