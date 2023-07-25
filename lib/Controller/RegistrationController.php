<?php

declare(strict_types=1);

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
			'file' => ['fileNode' => $registrationFile],
			'name' => $registrationFile->getName(),
			'users' => [[
				'displayName' => $this->userSession->getUser()->getDisplayName(),
				'notify' => false,
				'identify' => ['account' => $this->userSession->getUser()->getUID()],
			]],
			'userManager' => $this->userSession->getUser(),
		]);
		try {
			$this->signFileService
				->setLibreSignFileFromNode($registrationFile)
				->setFileUser(current($response['users']))
				->setSignWithoutPassword(true)
				->setUserUniqueIdentifier($this->userSession->getUser()->getEMailAddress())
				->setFriendlyName($this->userSession->getUser()->getDisplayName())
				->sign();
		} catch (\Throwable $e) {
			return new DataResponse(['message' => $e->getMessage()], Http::STATUS_BAD_REQUEST);
		}
		return new DataResponse(['uuid' => $response['uuid']]);
	}
}
