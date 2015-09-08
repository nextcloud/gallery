<?php


/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = null)
 *
 * @SuppressWarnings(PHPMD)
 */
class ApiTester extends \Codeception\Actor {
	use _generated\ApiTesterActions;

	/**
	 * Makes sure the response we get is the expected file
	 *
	 * @param array $file
	 * @param string $filename
	 */
	public function downloadAFile($file, $filename = null) {
		$I = $this;
		if (!$filename) {
			$filename = $file['name'];
		}
		$filename = urlencode($filename);
		$I->seeResponseCodeIs(200);
		$I->seeHttpHeader('Content-type', $file['mediatype'] . '; charset=utf-8');
		$I->seeHttpHeader(
			'Content-Disposition', 'attachment; filename*=UTF-8\'\'' . $filename . '; filename="'
								   . $filename . '"'
		);
	}

	/**
	 * Compares the dimensions of the downloaded image to the expected dimensions
	 *
	 * @param int $width
	 * @param int $height
	 */
	public function checkImageSize($width, $height) {
		$I = $this;
		$image = imagecreatefromstring($I->grabResponse());
		$I->assertEquals($width, imagesx($image));
		$I->assertEquals($height, imagesy($image));
	}
}
