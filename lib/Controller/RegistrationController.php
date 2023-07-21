<?php

namespace OCA\MyCompany\Controller;

use InvalidArgumentException;
use OCA\Libresign\Service\RequestSignatureService;
use OCA\Libresign\Service\SignFileService;
use OCA\MyCompany\Service\RegistrationService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use OCP\IUserSession;

class RegistrationController extends Controller {
	public function __construct(
		string $appName,
		IRequest $request,
		private RegistrationService $registrationService,
		private SignFileService $signFileService,
		private RequestSignatureService $requestSignatureService,
		private IUserSession $userSession,
	) {
		parent::__construct($appName, $request);
	}

	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function uploadPdf(): DataResponse {
		$file = $this->request->getUploadedFile('file');
		try {
			$this->registrationService->uploadPdf($file);
		} catch (InvalidArgumentException $e) {
			return new DataResponse(['message' => $e->getMessage()], Http::STATUS_BAD_REQUEST);
		}
		return new DataResponse();
	}

	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function sign(): DataResponse {
		$registrationFile = $this->registrationService->getRegistrationFile();
		$response = $this->requestSignatureService->save([
			'file' => ['fileId' => $registrationFile->getId()],
			'name' => $registrationFile->getName(),
			'users' => [['']],
			'status' => 1,
			'userManager' => $this->userSession->getUser(),
		]);
		$this->signFileService
			->setLibreSignFile($libreSignFile)
			->setFileUser($fileUser)
			->setSignWithoutPassword(true)
			->sign();
		return new DataResponse();
	}

}
