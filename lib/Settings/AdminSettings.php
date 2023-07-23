<?php

namespace OCA\MyCompany\Settings;

use OCA\MyCompany\AppInfo\Application;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Settings\IIconSection;

class AdminSettings implements IIconSection {
	public function __construct(
		private IL10N $l,
		private IURLGenerator $urlGenerator
	) {
	}

	/**
	 * {@inheritdoc}
	 */
	public function getID(): string {
		return Application::APP_ID;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getName(): string {
		// TRANSLATORS The app name.
		return $this->l->t('My Company');
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPriority(): int {
		return 60;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getIcon(): string {
		return $this->urlGenerator->imagePath(Application::APP_ID, 'app-dark.svg');
	}
}
