<?php
/**
 * @author Thomas Müller <thomas.mueller@tmit.eu>
 *
 * @copyright Copyright (c) 2015, ownCloud, Inc.
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

use Test\TestCase;

class Base64 extends TestCase {
	use \OCA\GalleryPlus\Service\Base64Encode;

	/**
	 * @dataProvider providesBase64Data
	 * @param $expected
	 * @param $input
	 */
	public function testBase64($expected, $input) {
		$result = $this->encode($input);
		$this->assertEquals($expected, $result);
	}

	public function providesBase64Data() {
		return [
			['MTIzNDU2Nzg5MA==', '1234567890'],
			['MTIzNDU2Nzg5MA==', 1234567890],
			[null, null],
			[null, new OC_Image()],
		];
	}
}
