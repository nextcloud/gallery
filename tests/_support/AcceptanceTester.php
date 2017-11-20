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
class AcceptanceTester extends \Codeception\Actor
{
    use _generated\AcceptanceTesterActions;

	/**
	 * Checks that the selection download url contains correct files
	 * As PhantomJS does not have a way to handle/simulate file download, check the logs
	 *
	 * @param string $dir Album to use
	 * @param array $files File list
	 *
	 * @throws \Codeception\Exception\ModuleException
	 */
	public function checkSelectionDownloadUrlMatches($dir, $files) {
		$dir = urlencode($dir);
		$this->openFile('tests/_output/phpbuiltinserver.access_log.txt');
		if (count($files) == 1) {
			$downloadRegex = '/\n.*"GET \/index\.php\/apps\/files\/ajax\/download\.php\?' .
				'dir=' . $dir . '&files=' . $files[0] . '&downloadStartSecret=.*\n\z/';
		} else {
			$downloadRegex = '/\n.*"GET \/index\.php\/apps\/files\/ajax\/download\.php\?' .
				'dir=' . $dir . '&' .
				'files=%5B%22(' . join('%22%2C%22', $files) . '|' . join('%22%2C%22', array_reverse($files)) . ')%22%5D&' .
				'downloadStartSecret=.*\n\z/';
		}
		$this->seeThisFileMatches($downloadRegex);
	}

	/**
	 * Checks that the selection download url contains correct files
	 * For public albums
	 * As PhantomJS does not have a way to handle/simulate file download, check the logs
	 *
	 * @param string $dir Album to use
	 * @param array $files File list
	 * @param string $baseurl Pathname of shared link
	 *
	 * @throws \Codeception\Exception\ModuleException
	 */
	public function checkSelectionPublicDownloadUrlMatches($dir, $files, $baseurl) {
		$dir = urlencode($dir);
		$baseurl = addcslashes($baseurl, '/');
		$this->openFile('tests/_output/phpbuiltinserver.access_log.txt');
		if (count($files) == 1) {
			$downloadRegex = '/\n.*"GET ' . $baseurl . '\/download\?' .
				'path=%2F&files=' . $files[0] . '&downloadStartSecret=.*\n\z/';
		} else {
			$downloadRegex = '/\n.*"GET ' . $baseurl . '\/download\?' .
				'path=%2F&' .
				'files=%5B%22(' . join('%22%2C%22', $files) . '|' . join('%22%2C%22', array_reverse($files)) . ')%22%5D&' .
				'downloadStartSecret=.*\n\z/';
		}
		$this->seeThisFileMatches($downloadRegex);
	}
}
