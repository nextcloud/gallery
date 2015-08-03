<?php
/**
 * @author Thomas Müller <thomas.mueller@tmit.eu>
 * @author Olivier Paroz <owncloud@interfasys.ch>
 *
 * @copyright Copyright (c) 2015, ownCloud, Inc.
 * @copyright Olivier Paroz 2015
 *
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
namespace OCA\Gallery\Service;

/**
 * Class Base64EncodeTest
 */
class Base64EncodeTest extends \Test\TestCase {

	/**
	 * @dataProvider providesBase64Data
	 * @param $expected
	 * @param $input
	 */
	public function testEncode($expected, $input) {
		$base64Encoder = $this->getMockForTrait('\OCA\Gallery\Service\Base64Encode');
		$result = self::invokePrivate($base64Encoder, 'encode', [$input]);
		$this->assertEquals($expected, $result);
	}

	public function providesBase64Data() {
		return [
			['MTIzNDU2Nzg5MA==', '1234567890'],
			['MTIzNDU2Nzg5MA==', 1234567890],
			[null, null],
			[null, new \OC_Image()],
		];
	}
}
