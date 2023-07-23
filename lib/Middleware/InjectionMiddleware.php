<?php

declare(strict_types=1);

namespace OCA\MyCompany\Middleware;

use OCA\MyCompany\Backend\SystemGroupBackend;
use OCA\Theming\Controller\ThemingController;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\AppFramework\Http\FileDisplayResponse;
use OCP\AppFramework\Http\Response;
use OCP\AppFramework\Middleware;
use OCP\Files\IAppData;
use OCP\Files\NotFoundException;
use OCP\Files\SimpleFS\ISimpleFolder;
use OCP\IRequest;
use OCP\Server;

class InjectionMiddleware extends Middleware {
	public function __construct(
		private IAppData $appData,
		private IRequest $request
	) {
	}

	public function beforeController(Controller $controller, string $methodName) {
		Server::get(\OCP\IGroupManager::class)->addBackend(new SystemGroupBackend());
	}

	public function afterController(Controller $controller, string $methodName, Response $response): Response {
		if ($controller instanceof ThemingController && $methodName === 'getImage') {
			return $this->getImageFromDomain($response);
		}
		return $response;
	}

	private function getImageFromDomain(Response $response): Response {
		return $this->getBackgroundOfDomain($response);
	}

	private function getBackgroundOfDomain(Response $response): Response {
		try {
			$folder = $this->getRootFolder('themes')->getFolder($this->request->getServerHost());
			$file = $folder->getFile('background.jpg');
		} catch (NotFoundException $e) {
			return $response;
		}

		$response = new FileDisplayResponse($file);
		$csp = new ContentSecurityPolicy();
		$csp->allowInlineStyle();
		$response->setContentSecurityPolicy($csp);
		$response->cacheFor(3600);
		$response->addHeader('Content-Disposition', 'attachment; filename="background.jpg"');
		$response->addHeader('Content-Type', 'image/jpeg');
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
