<?php
namespace Helper;
// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Acceptance extends \Codeception\Module\Filesystem
{
	/**
	 * Retrieves the user's credentials from the test data
	 *
	 * @return mixed
	 * @throws \Codeception\Exception\ModuleException
	 */
	public function getUserCredentials() {
		$userId = $this->getModule('\Helper\DataSetup')->userId;
		$password = $this->getModule('\Helper\DataSetup')->userPassword;

		return [$userId, $password];
	}
}
