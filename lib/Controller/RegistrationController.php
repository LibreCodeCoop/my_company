<?php

declare(strict_types=1);

namespace OCA\MyCompany\Controller;

use OCA\Forms\Service\FormsService;
use OCA\MyCompany\Service\CompanyService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\Attribute\PublicPage;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\AppFramework\Http\Response;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IGroupManager;
use OCP\IInitialStateService;
use OCP\IRequest;
use OCP\IUserSession;
use OCP\Util;

class RegistrationController extends Controller {
	public function __construct(
		string $appName,
		IRequest $request,
		private IUserSession $userSession,
		private FormsService $formsService,
		private IGroupManager $groupManager,
		private CompanyService $companyService,
		private IInitialStateService $initialStateService,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * Insert the extended viewport Header on iPhones to prevent automatic zooming.
	 */
	private function insertHeaderOnIos(): void {
		$USER_AGENT_IPHONE_SAFARI = '/^Mozilla\/5\.0 \(iPhone[^)]+\) AppleWebKit\/[0-9.]+ \(KHTML, like Gecko\) Version\/[0-9.]+ Mobile\/[0-9.A-Z]+ Safari\/[0-9.A-Z]+$/';
		if (preg_match($USER_AGENT_IPHONE_SAFARI, $this->request->getHeader('User-Agent'))) {
			Util::addHeader('meta', [
				'name' => 'viewport',
				'content' => 'width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1'
			]);
		}
	}

	#[NoAdminRequired]
	#[PublicPage]
	#[NoCSRFRequired]
	public function embeddedFormView(): Response {
		$userGroups = $this->groupManager->getUserGroupIds($this->userSession->getUser());
		if (!in_array('waiting-approval', $userGroups)) {
			throw new \Exception('STOP!');
		}
		$registrationFormId = $this->companyService->getRegistrationFormId();
		$form = $this->formsService->getPublicForm($registrationFormId);
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
		$this->initialStateService->provideInitialState('forms', 'shareHash', '');
		$this->initialStateService->provideInitialState('forms', 'isLoggedIn', false);
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

	private function setEmbeddedCSP(TemplateResponse $response) {
		$policy = new ContentSecurityPolicy();
		$policy->addAllowedFrameAncestorDomain('*');

		$response->addHeader('X-Frame-Options', 'ALLOW');
		$response->setContentSecurityPolicy($policy);

		return $response;
	}
}
