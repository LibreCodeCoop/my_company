<?php

namespace OCA\MyCompany\Controller;

use OCA\MyCompany\AppInfo\Application;
use OCA\MyCompany\Service\RegistrationService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IInitialState;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\Util;

class PageController extends Controller {
	public function __construct(
		IRequest $request,
		private IInitialState $initialState,
		private IURLGenerator $url,
		private RegistrationService $registrationService,
	) {
		parent::__construct(Application::APP_ID, $request);
	}

	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function index(string $path): TemplateResponse {
		try {
			$this->registrationService->getRegistrationFile();
			$this->initialState->provideInitialState('registration-form-file-exists', true);
		} catch (\Throwable $th) {
			$this->initialState->provideInitialState('registration-form-file-exists', false);
		}

		$this->initialState->provideInitialState('registration-form-signed', false);

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
