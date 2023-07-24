<?php

declare(strict_types=1);

namespace OCA\MyCompany\Middleware;

use OC\NavigationManager;
use OCA\MyCompany\AppInfo\Application;
use OCA\MyCompany\Backend\SystemGroupBackend;
use OCA\Theming\Controller\ThemingController;
use OCP\App\IAppManager;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\AppFramework\Http\FileDisplayResponse;
use OCP\AppFramework\Http\Response;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Middleware;
use OCP\Files\IAppData;
use OCP\Files\NotFoundException;
use OCP\Files\SimpleFS\ISimpleFolder;
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IRequest;
use OCP\IUserSession;
use OCP\Server;

class InjectionMiddleware extends Middleware {
	public function __construct(
		private IAppData $appData,
		private IRequest $request,
		private NavigationManager $navigationManager,
		private IUserSession $userSession,
		private IGroupManager $groupManager,
		private IAppManager $appManager,
		private IConfig $config,
	) {
	}

	public function beforeController(Controller $controller, string $methodName) {
		Server::get(\OCP\IGroupManager::class)->addBackend(new SystemGroupBackend());
	}

	public function afterController(Controller $controller, string $methodName, Response $response): Response {
		if ($controller instanceof ThemingController) {
			if ($methodName === 'getImage') {
				return $this->getImageFromDomain($response);
			}
		} else {
			$this->hideNotAllowedMenuItems($response);
		}
		return $response;
	}

	public function beforeOutput(Controller $controller, string $methodName, string $output): string {
		$output = $this->removeUnifiedSearch($output);
		return $output;
	}

	private function hideNotAllowedMenuItems(Response $response): void {
		if ($this->isAdmin()) {
			return;
		}
		if (!$response instanceof TemplateResponse) {
			return;
		}
		$renderAs = $response->getRenderAs();
		if ($renderAs !== 'user') {
			return;
		}
		$allowedApps = $this->config->getAppValue(Application::APP_ID, 'allowed_apps_to_all', '["' . Application::APP_ID . '"]');
		$allowedApps = json_decode($allowedApps, true);
		$navigation = $this->navigationManager->getAll();
		foreach ($navigation as $item) {
			if (!in_array($item['id'], $allowedApps)) {
				$item['type'] = 'hide';
			}
			$this->navigationManager->add($item);
		}
	}

	private function removeUnifiedSearch(string $output): string {
		if ($this->isAdmin()) {
			return $output;
		}
		if (str_starts_with($output, '<!DOCTYPE html>')) {
			if (str_contains($output, 'src="/dist/core-unified-search.js"')) {
				$output = str_replace('src="/dist/core-unified-search.js"', '', $output);
			}
		}
	}

	private function isAdmin() {
		$user = $this->userSession->getUser();
		if ($user !== null) {
			return $this->groupManager->isAdmin($user->getUID());
		}
		return false;
	}

	private function getImageFromDomain(Response $response): Response {
		return $this->getBackgroundOfDomain($response);
	}

	private function getBackgroundOfDomain(Response $response): Response {
		try {
			$folder = $this->getRootFolder('themes')->getFolder($this->request->getServerHost());
			$headers = $response->getHeaders();
			if (isset($headers['Content-Disposition'])) {
				if (str_contains($headers['Content-Disposition'], '"logo')) {
					$file = $folder->getFile('logo');
				} else if (str_contains($headers['Content-Disposition'], '"background')) {
					$file = $folder->getFile('background');
				} else {
					throw new NotFoundException();
				}
			}
		} catch (NotFoundException $e) {
			return $response;
		}

		$response = new FileDisplayResponse($file);
		$csp = new ContentSecurityPolicy();
		$csp->allowInlineStyle();
		$response->setContentSecurityPolicy($csp);
		$response->cacheFor(3600);
		return $response;
	}

	private function getRootFolder(string $folder): ISimpleFolder {
		try {
			return $this->appData->getFolder($folder);
		} catch (NotFoundException $e) {
			return $this->appData->newFolder($folder);
		}
	}
}
