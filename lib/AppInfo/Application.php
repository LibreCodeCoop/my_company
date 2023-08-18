<?php

namespace OCA\MyCompany\AppInfo;

use OCA\MyCompany\Middleware\InjectionMiddleware;
use OCA\MyCompany\Provider\PublicShareTemplateProvider;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;

/**
 * @codeCoverageIgnore
 */
class Application extends App implements IBootstrap {
	public const APP_ID = 'my_company';

	public function __construct() {
		parent::__construct(self::APP_ID);
	}

	public function boot(IBootContext $context): void {
	}

	public function register(IRegistrationContext $context): void {
		$context->registerMiddleWare(InjectionMiddleware::class, true);
		$context->registerPublicShareTemplateProvider(PublicShareTemplateProvider::class);
	}
}
