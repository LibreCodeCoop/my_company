<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2023, Vitor Mattos <vitor@php.rio>
 *
 * @author Vitor Mattos <vitor@php.rio>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\MyCompany\Service;

use mikehaertl\pdftk\Command;
use OCA\Libresign\Service\RequestSignatureService;
use OCA\Libresign\Service\SignFileService;
use OCA\MyCompany\Db\FormSubmissionMapper;
use OCA\MyCompany\Handler\PdfTk\Pdf;
use OCP\Files\File;
use OCP\Files\IAppData;
use OCP\Files\IMimeTypeDetector;
use OCP\Files\IRootFolder;
use OCP\Files\NotFoundException;
use OCP\IConfig;
use OCP\IL10N;
use OCP\ITempManager;
use OCP\IUserSession;
use Psr\Log\LoggerInterface;

class RegistrationService {
	private string $registrationFormFileName;
	public function __construct(
		private IL10N $l,
		private IRootFolder $rootFolder,
		private IUserSession $userSession,
		private IMimeTypeDetector $mimeTypeDetector,
		private CompanyService $companyService,
		private FormSubmissionMapper $formSubmissionMapper,
		private RequestSignatureService $requestSignatureService,
		private SignFileService $signFileService,
		private IConfig $config,
		private ITempManager $tempManager,
		private IAppData $appData,
		private LoggerInterface $logger,
	) {
		// TRANSLATORS Name of file that will store the registration form as PDF format.
		$this->registrationFormFileName = $l->t('registration-form.pdf');
	}

	public function getRegistrationFile(): File {
		$regiterFolder = $this->companyService->getUserAdminRegistrationFolder();
		return $regiterFolder->get($this->registrationFormFileName);
	}

	public function signForm(): void {
		$registrationFile = $this->fillPdf();
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
		}
		return;
	}

	private function fillPdf(): File {
		$this->logger->info('Started to fill form');
		$templatePdf = $this->companyService->getTemplateFile();
		$content = $templatePdf->getContent();

		$fileName = $this->tempManager->getTemporaryFile('.pdf');
		file_put_contents($fileName, $content);

		$pdftkPath = $this->config->getAppValue('libresign', 'pdftk_path');

		$pdf = new Pdf();
		$command = new Command();
		$command->setCommand($pdftkPath);
		$pdf->setCommand($command);
		$pdf->addFile($fileName);

		$data = $this->getDataFields();

		$filled = $pdf
			->fillForm($data)
			->needAppearances()
			->toString();

		// Delete first to remove previous version if exists
		$userFolder = $this->companyService->getUserAdminRegistrationFolder();
		try {
			$exists = $userFolder->get($this->registrationFormFileName);
			$exists->delete();
		} catch (NotFoundException $e) {
		}
		$filledFile = $userFolder->newFile($this->registrationFormFileName, $filled);
		return $filledFile;
	}

	private function getDataFields(): array {
		$submission = $this->formSubmissionMapper->getAnswersOfNewerstSubmission(
			$this->companyService->getRegistrationFormId(),
			$this->userSession->getUser()->getUID()
		);
		$dataFields = [];
		foreach ($submission as $answer) {
			switch ($answer['type']) {
				case 'multiple_unique':
					$dataFields[$answer['name']] = $answer['order'];
					break;
				case 'date':
					$date = \DateTime::createFromFormat('Y-m-d', $answer['text']);
					$dataFields[$answer['name']] = $date->format('d/m/Y');
					break;
				default:
					$dataFields[$answer['name']] = $answer['text'];
			}
		}
		return $dataFields;
	}
}
