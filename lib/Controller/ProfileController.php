<?php

namespace OCA\MyCompany\Controller;

use OCA\Libresign\Exception\LibresignException;
use OCA\Libresign\Service\SignFileService;
use OCA\MyCompany\AppInfo\Application;
use OCA\MyCompany\Service\RegistrationService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\Files\NotFoundException;
use OCP\IRequest;

class ProfileController extends Controller {
	public function __construct(
		IRequest $request,
		private RegistrationService $registrationService,
		private SignFileService $signFileService,
	) {
		parent::__construct(Application::APP_ID, $request);
	}
	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function index(): DataResponse {
		try {
			$file = $this->registrationService->getRegistrationFile();
			$libreSignFile = $this->signFileService->getLibresignFile($file->getId());
			$signUuid = $libreSignFile->getUuid();
		} catch (LibresignException | NotFoundException $th) {
			$signUuid = null;
		}
		return new DataResponse([
			'signUuid' => $signUuid,
		]);
	}
}
