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
namespace OCA\Gallery\Service;

use OCP\ILogger;

use OCA\Gallery\Environment\Environment;

/**
 * Class FilesServiceTest
 *
 * @package OCA\Gallery\Controller
 */
abstract class ServiceTest extends \Test\TestCase {

	/** @var string */
	protected $appName = 'gallery';
	/** @var Environment */
	protected $environment;
	/** @var ILogger */
	protected $logger;

	/**
	 * Test set up
	 */
	protected function setUp() {
		parent::setUp();

		$this->environment = $this->getMockBuilder('\OCA\Gallery\Environment\Environment')
								  ->disableOriginalConstructor()
								  ->getMock();
		$this->logger = $this->getMockBuilder('\OCP\ILogger')
							 ->disableOriginalConstructor()
							 ->getMock();
	}

	/**
	 * Mocks Environment->getResourceFromId
	 *
	 * Needs to pass a mock of a File or Folder
	 *
	 * @param int $fileId
	 * @param object|\PHPUnit_Framework_MockObject_MockObject|bool $answer
	 */
	protected function mockGetResourceFromId($fileId, $answer) {
		$this->environment->expects($this->once())
					  ->method('getResourceFromId')
					  ->with($this->equalTo($fileId))
					  ->willReturn($answer);
	}

}
