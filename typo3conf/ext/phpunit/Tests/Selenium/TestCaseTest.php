<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2011 Carsten Koenig (ck@carsten-koenig.de)
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Test case for the Tx_Phpunit_Selenium_TestCase class.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Carsten Koenig <ck@carsten-koenig.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Phpunit_Selenium_TestCaseTest extends Tx_Phpunit_TestCase {
	/**
	 * Temporary backup for the global phpunit extension configuration during tests
	 *
	 * @var array
	 */
	protected $backupGlobalPhpunitConfiguration = array();

	/**
	 * @var Tx_Phpunit_Selenium_TestCase
	 */
	private $fixture = NULL;

	protected function setUp() {
		$this->backupGlobalPhpunitConfiguration = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit'];

		$this->fixture = $this->getMock(
			$this->createAccessibleProxyClass(), array('isSeleniumServerRunning')
		);
		$this->fixture->expects($this->any())->method('isSeleniumServerRunning')->will($this->returnValue(TRUE));
	}

	protected function tearDown() {
		unset($this->fixture);

		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit'] = $this->backupGlobalPhpunitConfiguration;
	}

	/*
	 * Utitlity functions
	 */

	/**
	 * This function creates a subclass of Tx_Phpunit_Selenium_TestCase with
	 * some attributes and methods made public.
	 *
	 * @return string class name, will not be empty
	 */
	private function createAccessibleProxyClass() {
		$className = uniqid('Tx_Phpunit_Selenium_AccessibleTestCase');

		if (!class_exists($className, FALSE)) {
			eval(
				'class ' . $className . ' extends Tx_Phpunit_Selenium_TestCase {' .
				'  ' .
				'  public function getSeleniumBrowser() {' .
				'    return parent::getSeleniumBrowser();' .
				'  }' .
				'  public function getSeleniumBrowserUrl() {' .
				'    return parent::getSeleniumBrowserUrl();' .
				'  }' .
				'  public function getSeleniumHost() {' .
				'    return parent::getSeleniumHost();' .
				'  }' .
				'  public function getSeleniumPort() {' .
				'    return parent::getSeleniumPort();' .
				'  }' .
				'  public function isSeleniumServerRunning() {' .
				'    return parent::isSeleniumServerRunning();' .
				'  }' .
				'  public function runTest() {' .
				'    parent::runTest();' .
				'  }' .
				'}'
			);
		}

		return $className;
	}


	/*
	 * Tests for the utitility functions
	 */

	/**
	 * @test
	 */
	public function createAccessibleProxyClassReturnsFixtureSubclassName() {
		$className = $this->createAccessibleProxyClass();

		$this->assertInstanceOf(
			'Tx_Phpunit_Selenium_TestCase',
			new $className()
		);
	}

	/*
	 * Unit tests
	 */

	/**
	 * @test
	 */
	public function getSeleniumBrowserUrlForConfiguredBrowserUrlReturnsConfiguredUrl() {
		$url = 'http://example.com/';

		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['selenium_browserurl'] = $url;

		$this->assertSame(
			$url,
			$this->fixture->getSeleniumBrowserUrl()
		);
	}

	/**
	 * @test
	 */
	public function getSeleniumBrowserUrlForNoConfiguredBrowserUrlReturnsDefaultUrl() {
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['selenium_browserurl'] = '';

		$expected = rtrim(
			t3lib_div::getIndpEnv('TYPO3_SITE_URL'),
			Tx_Phpunit_Selenium_TestCase::DEFAULT_SELENIUM_BROWSER_URL
		);

		$this->assertSame(
			$expected,
			$this->fixture->getSeleniumBrowserUrl()
		);
	}

	/**
	 * @test
	 */
	public function getSeleniumBrowserForConfiguredBrowserReturnsConfiguredBrowser() {
		$browser = '*firefox';

		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['selenium_browser'] = $browser;

		$this->assertSame(
			$browser,
			$this->fixture->getSeleniumBrowser()
		);
	}

	/**
	 * @test
	 */
	public function getSeleniumBrowserForNoConfiguredBrowserReturnsDefaultBrowser() {
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['selenium_browser'] = '';

		$this->assertSame(
			Tx_Phpunit_Selenium_TestCase::DEFAULT_SELENIUM_BROWSER,
			$this->fixture->getSeleniumBrowser()
		);
	}

	/**
	 * @test
	 */
	public function getSeleniumPortForConfiguredPortReturnsConfiguredPort() {
		$port = 1234;

		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['selenium_port'] = $port;

		$this->assertSame(
			$port,
			$this->fixture->getSeleniumPort()
		);
	}

	/**
	 * @test
	 */
	public function getSeleniumPortForNoConfiguredPortReturnsDefaultPort() {
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['selenium_port'] = 0;

		$this->assertSame(
			Tx_Phpunit_Selenium_TestCase::DEFAULT_SELENIUM_PORT,
			$this->fixture->getSeleniumPort()
		);
	}

	/**
	 * @test
	 */
	public function getSeleniumHostForConfiguredHostReturnsConfiguredHost() {
		$host = 'http://example.com/';

		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['selenium_host'] = $host;

		$this->assertSame(
			$host,
			$this->fixture->getSeleniumHost()
		);
	}

	/**
	 * @test
	 */
	public function getSeleniumHostForNotConfiguredHostReturnsTheDefaultHost() {
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['selenium_host'] = '';

		$this->assertSame(
			Tx_Phpunit_Selenium_TestCase::DEFAULT_SELENIUM_HOST,
			$this->fixture->getSeleniumHost()
		);
	}

	/**
	 * @test
	 */
	public function isSeleniumServerRunningWhenHostIsInvalidReturnsFalse() {
		// We will use 'example.invalid' as an invalid host
		// (according to RFC 2606 the TLD '.invalid' should be used to test for invalid hosts).
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['selenium_host'] = 'http://example.invalid';

		$className = $this->createAccessibleProxyClass();
		$fixture = new $className();

		$this->assertFalse(
			$fixture->isSeleniumServerRunning()
		);
	}

	/**
	 * @test
	 */
	public function runTestWhenServerIsNotRunningMarksTestAsSkipped() {
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['selenium_host'] = 'http://example.invalid';

		$fixture = new Tx_Phpunit_Selenium_TestCase();

		try {
			$fixture->runTest();
		} catch (PHPUnit_Framework_SkippedTestError $e) {
			$this->assertTrue(TRUE);
		}
	}
}
?>