<?php

namespace OCA\MyCompany\Controller;

use OCA\MyCompany\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;
use OCP\Util;

class PageController extends Controller {
	public function __construct(
		IRequest $request
	) {
		parent::__construct(Application::APP_ID, $request);
	}

	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function index(string $path): TemplateResponse {
		Util::addScript(Application::APP_ID, 'my_company-main');

		$response = new TemplateResponse(Application::APP_ID, 'main');

		return $response;
	}
}
