<?php
/**
 * Nextcloud - Gallery
 *
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <galleryapps@oparoz.com>
 *
 * @copyright Olivier Paroz 2017
 */

namespace OCA\Gallery\Tests\Http;

use OCP\AppFramework\Http;

use OCA\Gallery\Http\ImageResponse;

/**
 * Class ImageResponseTest
 *
 * @package OCA\Gallery\Tests\Http
 */
class ImageResponseTest extends \Test\TestCase {

	public function testRenderWithOcImageInstance() {
		$resource = file_get_contents(\OC::$SERVERROOT . '/tests/data/testimage.jpg');
		$preview = new \OC_Image();
		$preview->loadFromData($resource);

		$data = [
			'name'     => 'testimage.jpg',
			'mimetype' => 'image/jpeg',
			'preview'  => $preview,
		];

		$imageResponse = new ImageResponse ($data);
		$response = $imageResponse->render();

		$this->assertSame($preview->data(), $response);
	}

	public function testRenderWithString() {
		$preview = file_get_contents(\OC::$SERVERROOT . '/tests/data/testimage.jpg');

		$data = [
			'name'     => 'testimage.jpg',
			'mimetype' => 'image/jpeg',
			'preview'  => $preview,
		];

		$imageResponse = new ImageResponse ($data);
		$response = $imageResponse->render();

		$this->assertSame($preview, $response);
	}

}
