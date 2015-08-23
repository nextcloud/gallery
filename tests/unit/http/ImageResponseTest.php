<?php
/**
 * ownCloud - gallery
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <owncloud@interfasys.ch>
 *
 * @copyright Olivier Paroz 2015
 */
namespace OCA\Gallery\Http;

use OCP\AppFramework\Http;

/**
 * Class ImageResponseTest
 *
 * @package OCA\Gallery\Controller
 */
class ImageResponseTest extends \Test\TestCase {

	public function testRenderWithOcImageInstance() {
		$resource = file_get_contents(\OC::$SERVERROOT . '/tests/data/testimage.jpg');
		$preview = new \OC_Image($resource);

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
