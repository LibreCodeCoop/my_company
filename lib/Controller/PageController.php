<?php

namespace OCA\MyCompany\Controller;

use OCA\Forms\Db\SubmissionMapper;
use OCA\Libresign\Exception\LibresignException;
use OCA\Libresign\Service\SignFileService;
use OCA\MyCompany\AppInfo\Application;
use OCA\MyCompany\Service\RegistrationService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IInitialState;
use OCP\Files\NotFoundException;
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\IUserSession;
use OCP\Util;

class PageController extends Controller {
	public function __construct(
		IRequest $request,
		private IInitialState $initialState,
		private IURLGenerator $urlGenerator,
		private RegistrationService $registrationService,
		private IGroupManager $groupManager,
		private IUserSession $userSession,
		private SignFileService $signFileService,
		private SubmissionMapper $submissionMapper,
		private IConfig $config,
	) {
		parent::__construct(Application::APP_ID, $request);
	}

	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function index(string $path): TemplateResponse {
		try {
			$participants = $this->submissionMapper->findParticipantsByForm(1);
			$filled = in_array($this->userSession->getUser()->getUID(), $participants);
			$this->initialState->provideInitialState('registration-form-filled', $filled);
		} catch (\Throwable $th) {
			$filled = false;
			$this->initialState->provideInitialState('registration-form-filled', $filled);
		}

		if ($filled) {
			try {
				$file = $this->registrationService->getRegistrationFile();
				$libreSignFile = $this->signFileService->getLibresignFile($file->getId());
				$signUuid = $libreSignFile->getUuid();
				$this->initialState->provideInitialState('registration-form-sign-uuid', $signUuid);
			} catch (LibresignException | NotFoundException $th) {
			}
		}

		$userGroups = $this->groupManager->getUserGroupIds($this->userSession->getUser());
		$this->initialState->provideInitialState('registration-approved', !in_array('waiting-approval', $userGroups));

		Util::addScript(Application::APP_ID, 'my_company-main');

		$response = new TemplateResponse(Application::APP_ID, 'main');

		$policy = new ContentSecurityPolicy();
		$policy->addAllowedWorkerSrcDomain('*');
		$policy->addAllowedFrameDomain('*');
		$response->setContentSecurityPolicy($policy);

		return $response;
	}
}
