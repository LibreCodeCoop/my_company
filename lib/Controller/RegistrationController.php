<?php

declare(strict_types=1);

namespace OCA\MyCompany\Controller;

use InvalidArgumentException;
use OCA\Forms\Service\FormsService;
use OCA\Libresign\Service\RequestSignatureService;
use OCA\Libresign\Service\SignFileService;
use OCA\MyCompany\Service\CompanyService;
use OCA\MyCompany\Service\RegistrationService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\DataDownloadResponse;
use OCP\AppFramework\Http\Response;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IInitialStateService;
use OCP\IRequest;
use OCP\IUserSession;
use OCP\Util;

class RegistrationController extends Controller {
	public function __construct(
		string $appName,
		IRequest $request,
		private RegistrationService $registrationService,
		private CompanyService $companyService,
		private SignFileService $signFileService,
		private RequestSignatureService $requestSignatureService,
		private IUserSession $userSession,
		private FormsService $formsService,
		private IInitialStateService $initialStateService,
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
	public function downloadForm(): DataDownloadResponse {
		return new DataDownloadResponse(
			$this->companyService->getTemplateFile()->getContent(),
			'formulario.docx',
			'application/msword'
		);
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

	/**
	 * Insert the extended viewport Header on iPhones to prevent automatic zooming.
	 */
	public function insertHeaderOnIos(): void {
		$USER_AGENT_IPHONE_SAFARI = '/^Mozilla\/5\.0 \(iPhone[^)]+\) AppleWebKit\/[0-9.]+ \(KHTML, like Gecko\) Version\/[0-9.]+ Mobile\/[0-9.A-Z]+ Safari\/[0-9.A-Z]+$/';
		if (preg_match($USER_AGENT_IPHONE_SAFARI, $this->request->getHeader('User-Agent'))) {
			Util::addHeader('meta', [
				'name' => 'viewport',
				'content' => 'width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1'
			]);
		}
	}

	/**
	 * @NoAdminRequired
	 * @PublicPage
	 * @NoCSRFRequired
	 *
	 * @param string $hash
	 * @return Response
	 */
	public function embeddedFormView(): Response {
		$form = $this->formsService->getPublicForm(1);
		if (!$form['canSubmit']) {
			$form['description'] = '';
		}

		Util::addStyle('forms', 'embedded');

		// Inject style on all templates
		Util::addStyle('forms', 'forms');

		// Main Template to fill the form
		Util::addScript('forms', 'forms-submit');
		$this->insertHeaderOnIos();
		$this->initialStateService->provideInitialState('forms', 'form', $form);
		$this->initialStateService->provideInitialState('forms', 'isLoggedIn', false);
		/** @todo  check if is necessary */
		$this->initialStateService->provideInitialState('forms', 'shareHash', '');
		$this->initialStateService->provideInitialState('forms', 'maxStringLengths', [
			'formTitle' => 256,
			'formDescription' => 8192,
			'questionText' => 2048,
			'questionDescription' => 4096,
			'optionText' => 1024,
			'answerText' => 4096,
		]);

		$response = new TemplateResponse('forms', 'main', [
			'id-app-content' => '#app-content-vue',
			'id-app-navigation' => null,
		]);
		$response->renderAs(TemplateResponse::RENDER_AS_BASE);

		$this->initialStateService->provideInitialState('forms', 'isEmbedded', true);

		return $this->setEmbeddedCSP($response);
	}

	protected function setEmbeddedCSP(TemplateResponse $response) {
		$policy = new ContentSecurityPolicy();
		$policy->addAllowedFrameAncestorDomain('*');

		$response->addHeader('X-Frame-Options', 'ALLOW');
		$response->setContentSecurityPolicy($policy);

		return $response;
	}
}
