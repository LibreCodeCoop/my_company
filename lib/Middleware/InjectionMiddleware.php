<?php

declare(strict_types=1);

namespace OCA\MyCompany\Middleware;

use OC\NavigationManager;
use OCA\Forms\Controller\ApiController;
use OCA\MyCompany\AppInfo\Application;
use OCA\MyCompany\Backend\SystemGroupBackend;
use OCA\MyCompany\Service\CompanyService;
use OCA\MyCompany\Service\RegistrationService;
use OCA\Theming\Controller\ThemingController;
use OCP\App\IAppManager;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\AppFramework\Http\FileDisplayResponse;
use OCP\AppFramework\Http\NotFoundResponse;
use OCP\AppFramework\Http\Response;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Middleware;
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IRequest;
use OCP\IUserSession;
use OCP\Server;
use OCP\Util;

class InjectionMiddleware extends Middleware {
	public function __construct(
		private IRequest $request,
		private NavigationManager $navigationManager,
		private IUserSession $userSession,
		private IGroupManager $groupManager,
		private IAppManager $appManager,
		private IConfig $config,
		private CompanyService $companyService,
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
			$this->hideSettingsItems($response);
			$this->hideNotAllowedMenuItems($response);
		}
		$this->signRegistrationForm($controller, $methodName, $response);
		return $response;
	}

	public function beforeOutput(Controller $controller, string $methodName, string $output): string {
		$output = $this->removeUnifiedSearch($output);
		return $output;
	}

	private function hideSettingsItems(Response $response): void {
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
		Util::addStyle(Application::APP_ID, 'main');
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
		return $output;
	}

	private function isAdmin() {
		$user = $this->userSession->getUser();
		if ($user !== null) {
			if ($this->groupManager->isAdmin($user->getUID())) {
				return true;
			}
			if ($this->groupManager->getSubAdmin()->isSubAdmin($user)) {
				return true;
			}
		}
		return false;
	}

	private function getImageFromDomain(Response $response): Response {
		if (!$response instanceof NotFoundResponse && !$response instanceof FileDisplayResponse) {
			return $response;
		}

		$type = $this->request->getParam('key');
		if (!in_array($type, ['logo', 'background'])) {
			return $response;
		}

		if ($type === 'logo') {
			$file = $this->companyService->getThemeFile('core/img/logo.svg');
			$mime = 'image/svg+xml';
		} elseif ($type === 'background') {
			$file = $this->companyService->getThemeFile('core/img/background.jpg');
			$mime = 'image/jpg';
		} else {
			return new NotFoundResponse();
		}

		if ($response instanceof NotFoundResponse) {
			$response = new FileDisplayResponse($file);
			$csp = new ContentSecurityPolicy();
			$csp->allowInlineStyle();
			$response->cacheFor(3600);
			$response->addHeader('Content-Type', $mime);
			$response->addHeader('Content-Disposition', 'attachment; filename="' . $type . '"');
			$response->setContentSecurityPolicy($csp);
		} else {
			try {
				$class = new \ReflectionClass($response);
				$property = $class->getProperty('file');
				$property->setAccessible(true);
				$property->setValue($response, $file);
			} catch(\ReflectionException $e) {
			}
		}

		return $response;
	}

	private function signRegistrationForm(Controller $controller, string $methodName, Response $response): void {
		if (!$controller instanceof ApiController) {
			return;
		}
		if ($methodName !== 'insertSubmission') {
			return;
		}
		if ($response->getStatus() !== 200) {
			return;
		}
		$userGroups = $this->groupManager->getUserGroupIds($this->userSession->getUser());
		if (!in_array('waiting-approval', $userGroups)) {
			return;
		}
		$id = $this->request->getParam('formId');
		$registrationFormId = $this->companyService->getRegistrationFormId();
		if ($id !== $registrationFormId) {
			return;
		}
		Server::get(RegistrationService::class)->signForm();
	}
}
