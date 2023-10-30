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

namespace OCA\MyCompany\Listener;

use OCA\MyCompany\Service\CompanyService;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\IGroupManager;
use OCP\User\Events\UserCreatedEvent;
use Psr\Log\LoggerInterface;

class UserCreatedEventListener implements IEventListener {
	public function __construct(
		private IGroupManager $groupManager,
		private CompanyService $companyService,
		private LoggerInterface $logger,
	) {
	}

	public function handle(Event $event): void {
		if (!($event instanceof UserCreatedEvent)) {
			// Unrelated
			return;
		}
		$user = $event->getUser();
		$group = $this->groupManager->get($this->companyService->getCompanyCode());
		if ($group === null) {
			return;
		}
		$group->addUser($user);
	}
}
