<?php
namespace Helper;
// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Api extends \Codeception\Module {

	/**
	 * @return mixed
	 * @throws \Codeception\Exception\ModuleException
	 */
	public function getUserCredentials() {
		$user = $this->getModule('\Helper\DataSetup')->userId;
		$password = $this->getModule('\Helper\DataSetup')->userPassword;

		return [$user, $password];
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
}
