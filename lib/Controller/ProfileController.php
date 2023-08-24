<?php

namespace OCA\MyCompany\Controller;

use OCA\Libresign\Exception\LibresignException;
use OCA\MyCompany\AppInfo\Application;
use OCA\MyCompany\Service\RegistrationService;
use OCP\App\IAppManager;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\Files\NotFoundException;
use OCP\IRequest;
use OCP\Server;

class ProfileController extends Controller {
	public function __construct(
		IRequest $request,
		private RegistrationService $registrationService,
		private IAppManager $appManager,
	) {
		parent::__construct(Application::APP_ID, $request);
	}
	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function index(): DataResponse {
		if (!$this->appManager->isEnabledForUser('libresign')) {
			return new DataResponse(['message' => 'App LibreSign not found'], Http::STATUS_BAD_REQUEST);
		}
		try {
			$file = $this->registrationService->getRegistrationFile();
			/** @var \OCA\Libresign\Service\SignFileService */
			$signFileService = Server::get(\OCA\Libresign\Service\SignFileService::class);
			$libreSignFile = $signFileService->getLibresignFile($file->getId());
			$signUuid = $libreSignFile->getUuid();
		} catch (LibresignException | NotFoundException $th) {
			$signUuid = null;
		}
		return new DataResponse([
			'signUuid' => $signUuid,
		]);
	}
}
