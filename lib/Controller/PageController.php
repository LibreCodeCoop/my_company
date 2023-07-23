<?php

namespace OCA\MyCompany\Controller;

use OCA\Libresign\Db\File as FileEntity;
use OCA\Libresign\Exception\LibresignException;
use OCA\Libresign\Service\SignFileService;
use OCA\MyCompany\AppInfo\Application;
use OCA\MyCompany\Service\RegistrationService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IInitialState;
use OCP\IGroupManager;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\IUserSession;
use OCP\Util;

class PageController extends Controller {
	public function __construct(
		IRequest $request,
		private IInitialState $initialState,
		private IURLGenerator $url,
		private RegistrationService $registrationService,
		private IGroupManager $groupManager,
		private IUserSession $userSession,
		private SignFileService $signFileService,
	) {
		parent::__construct(Application::APP_ID, $request);
	}

	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function index(string $path): TemplateResponse {
		try {
			$file = $this->registrationService->getRegistrationFile();
			try {
				$libreSignFile = $this->signFileService->getLibresignFile($file->getId());
				$signed = $libreSignFile->getUuid();
				$this->initialState->provideInitialState('registration-form-signed', $signed);
			} catch (LibresignException $th) {
			}
			$this->initialState->provideInitialState('registration-form-file-exists', true);
		} catch (\Throwable $th) {
			$this->initialState->provideInitialState('registration-form-file-exists', false);
			$this->initialState->provideInitialState('registration-form-signed', '');
		}

		$userGroups = $this->groupManager->getUserGroupIds($this->userSession->getUser());
		$this->initialState->provideInitialState('approved', !in_array('waiting-approval', $userGroups));

		$this->initialState->provideInitialState('registration-form-file-empty', [
			'url' => $this->url->linkToRoute('Share#downloadShare', [
				'token' => 'y5TbiaA8M5ps9iw',
				'filename' => 'formulario_adesao.docx',
			]),
			'name' => 'formulario_adesao.docx',
		]);

		Util::addScript(Application::APP_ID, 'my_company-main');

		$response = new TemplateResponse(Application::APP_ID, 'main');

		return $response;
	}
}
