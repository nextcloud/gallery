<?php
namespace Helper;
// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Api extends \Codeception\Module {

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

	/**
	 * @return mixed
	 * @throws \Codeception\Exception\ModuleException
	 */
	public function getMediaTypes() {
		$mediaTypes = $this->getModule('\Helper\DataSetup')->mediaTypes;
		$extraMediaTypes = $this->getModule('\Helper\DataSetup')->extraMediaTypes;

		return [$mediaTypes, $extraMediaTypes];
	}

	/**
	 * @param string $folderPath
	 *
	 * @return array<string,int|string>
	 * @throws \Codeception\Exception\ModuleException
	 */
	public function getFilesDataForFolder($folderPath) {
		return $this->getModule('\Helper\DataSetup')->getFilesDataForFolder($folderPath);
	}
}
