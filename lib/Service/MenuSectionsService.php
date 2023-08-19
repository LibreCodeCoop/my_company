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

class MenuSectionsService {
	public function __construct(
	) {
	}

	public function getList(): array {
		$list = [
			[
				'name' => 'Pagamentos',
				'id' => 80,
				'url' => 'http://localhost/s/XFeNfHHYDs8eGNn',
				'icon' => <<<XML
					<svg id="emoji" viewBox="0 0 72 72" xmlns="http://www.w3.org/2000/svg">
						<g id="color">
							<path fill="#D0CFCE" stroke="none" d="M58.831,59.603c0.5559-0.378,0.9222-1.0177,0.9222-1.7415V24.6081H16.8349v0.4927v31.4261 c0,0.0451-0.003,0.0893-0.0082,0.1344c-0.0073,0.0558-0.0867,0.5933-0.3357,1.3202c-0.0785,0.2418-0.1719,0.4732-0.2754,0.6998 c-0.0305,0.0683-0.0611,0.1355-0.0946,0.2052c-0.0605,0.1211-0.1177,0.2432-0.1852,0.359 c-0.1271,0.2341-0.2663,0.4705-0.4309,0.7066l42.1589,0.0103c0.1439,0,0.2845-0.0148,0.4205-0.0429 C58.3559,59.8634,58.6087,59.7542,58.831,59.603z"/>
							<path fill="#9B9B9A" stroke="none" d="M12.1338,60.0371c-0.04-0.5215,2.2655-0.2523,2.7831-0.3314c1.3427-0.205,1.7472-2.5912,1.8829-4.7953 l0.5022-29.349c0,0-0.3879-1.318-0.0754-1.318l2.9082,0.0717h37.8037L58,20.3664l-0.0038-0.0046l0,0 c-0.0169,0.2818-0.6078-1.9364-0.8105-1.7651c-0.0039,0.0025,0.0925-0.0379,0,0c0,0,0.135,0,0,0 c-0.5527,0-0.1779-1.6485-0.1779-1.6485l-27.3383-0.0025c-0.75,0-0.4256-3.6514-0.8223-3.8284 c-0.0001-0.0002-1.8003-1.0422-2.3372-1.0576c-0.0195-0.0005-11.3428-0.0052-11.3428-0.0052c-1.1026,0-3.2083,1.8996-3.2083,3.0026 c0,0-0.9313,41.2693-0.9563,41.1593c0-0.0001,0-0.0002,0-0.0002l-0.0241-0.0167l1.1915,4.0249l-0.0311-0.1686 C12.1382,60.0492,12.1343,60.0438,12.1338,60.0371z"/>
						</g>
						<g id="hair"/>
						<g id="skin"/>
						<g id="skin-shadow"/>
						<g id="line">
							<path fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="2" d="M57.0078,20.3044v-3.3562l-27.3383-0.0025c-0.198,0-0.3586-0.165-0.3586-0.3684l-0.0687-1.5169 c-0.116-1.7878-1.3398-3.0033-2.9967-3.0033H14.9589c-1.6569,0-3,1.3432-3,3V56"/>
							<path fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="2" d="M16.9611,55.3694c-0.1472,2.6185-1.9172,4.3009-3.8299,4.5924l43.9148,0.0108c1.6569,0,3-1.3431,3-3V25.3151 c0-0.5523-0.4477-1-1-1H17.9649c-0.5523,0-1,0.4477-1,1L16.9611,55.3694z"/>
						</g>
					</svg>
					XML
			],
			[
				'name' => 'Documentos 2',
				'id' => $this->slugify('Documentos 2'),
				'url' => 'http://localhost/s/xStw2nj56oKG9aX',
				'icon' => <<<XML
					<svg id="emoji" viewBox="0 0 72 72" xmlns="http://www.w3.org/2000/svg">
						<g id="color">
							<polyline fill="#9B9B9A" stroke="none" points="56,48.8213 56,10.9583 15.0372,10.9583 15.0372,41.1106 15.0372,52.2652 54.4639,50.9166"/>
							<polyline fill="#9B9B9A" stroke="none" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="2" points="56,48.8213 56,10.9583 16,10.9583 16,41.1106"/>
							<polygon fill="#D0CFCE" stroke="none" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="2" points="47.9719,38.1494 13.8387,45.6521 14.6533,56.4415 45.8422,52.277 49.1154,51.84 53,51.3213"/>
						</g>
						<g id="hair"/>
						<g id="skin"/>
						<g id="skin-shadow"/>
						<g id="line">
							<polyline fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="2" points="56,48.8213 56,10.9583 16,10.9583 16,41.1106"/>
							<polygon fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="2" points="47.9719,38.1494 13.8387,45.6521 14.6533,56.4415 45.8422,52.277 49.1154,51.84 53,51.3213"/>
							<line x1="20.6218" x2="36" y1="16" y2="16" fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="2"/>
							<line x1="20.6218" x2="51.6602" y1="21.328" y2="21.328" fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="2"/>
							<line x1="20.6218" x2="51.6602" y1="32.2205" y2="32.2205" fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="2"/>
							<line x1="20.6218" x2="33.3927" y1="37.6274" y2="37.6274" fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="2"/>
							<line x1="20.6218" x2="51.6602" y1="26.8137" y2="26.8137" fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="2"/>
							<path fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" stroke-width="2" d="M56,48.3213c0,1.6569-1.3431,3-3,3"/>
						</g>
					</svg>
					XML
			],
			// [
			// 	'name' => 'Documentos 3',
			// 	'id' => $this->slugify('Documentos 3'),
			// 	'url' => 'http://localhost/u/admin',
			// ],
		];
		return $list;
	}

	private function slugify(string $text): string {
		// replace everything except alphanumeric with a single '-'
		$text = preg_replace('/[^A-Za-z0-9]+/', '-', $text);
		$text = strtolower($text);
		return trim($text, '-');
	}
}
