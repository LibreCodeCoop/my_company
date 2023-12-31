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

namespace OCA\MyCompany\AppInfo;

use OCA\Analytics\Datasource\DatasourceEvent;
use OCA\MyCompany\Listener\AnalyticsDatasourceListener;
use OCA\MyCompany\Listener\UserCreatedEventListener;
use OCA\MyCompany\Middleware\InjectionMiddleware;
use OCA\MyCompany\Provider\PublicShareTemplateProvider;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\User\Events\UserCreatedEvent;

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
		$context->registerEventListener(DatasourceEvent::class, AnalyticsDatasourceListener::class);
		$context->registerEventListener(UserCreatedEvent::class, UserCreatedEventListener::class);
	}
}
