<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2010-2011 Bastian Waidelich (bastian@typo3.org)
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
 * This class provides helper functions that might be convenient when testing in
 * TYPO3. It extends PHPUnit_Extensions_SeleniumTestCase, so you have access to
 * all of that class too.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Bastian Waidelich <bastian@typo3.org>
 * @author Carsten Koenig <ck@carsten-koenig.de>
 */
class Tx_Phpunit_Selenium_TestCase extends PHPUnit_Extensions_SeleniumTestCase {
	/**
	 * the default Selenium server host address
	 *
	 * @var string
	 */
	const DEFAULT_SELENIUM_HOST = 'localhost';

	/**
	 * the default Selenium server port
	 *
	 * @var integer
	 */
	const DEFAULT_SELENIUM_PORT = 4444;

	/**
	 * the default Selenium browser
	 *
	 * @var string
	 */
	const DEFAULT_SELENIUM_BROWSER = '*chrome';

	/**
	 * the default Selenium browser URL
	 *
	 * @var string
	 */
	const DEFAULT_SELENIUM_BROWSER_URL = '/';

	/**
	 * The constructor.
	 *
	 * @param string $name
	 * @param array  $data
	 * @param string $dataName
	 */
	public function __construct($name = NULL, array $data = array(), $dataName = '') {
		$browser = array(
			'browser' => $this->getSeleniumBrowser(),
			'host' => $this->getSeleniumHost(),
			'port' => $this->getSeleniumPort(),
		);
		parent::__construct($name, $data, $dataName, $browser);

		$this->setBrowserUrl($this->getSeleniumBrowserUrl());
	}

	/**
	 * Runs the test if the Selenium RC Server is reachable.
	 *
	 * If the server is not reachable, the tests will be marked as skipped, and
	 * a message will be displayed giving a hint on wich host/port the client
	 * was looking for the Selenium server.
	 *
	 * @see PHPUnit_Extensions_SeleniumTestCase::runTest()
	 *
	 * @return void
	 */
	protected function runTest() {
		if ($this->isSeleniumServerRunning()) {
			parent::runTest();
		} else {
			$this->markTestSkipped(
				'Selenium RC server not reachable (host=' .
				$this->getSeleniumHost() . ', port=' .
				$this->getSeleniumPort() . ').'
			);
		}
	}

	/**
	 * Tests if the Selenium RC server is running.
	 *
	 * @return boolean TRUE if the server is reachable by opening a socket, FALSE otherwise
	 */
	protected function isSeleniumServerRunning() {
		$seleniumServerIsRunning = FALSE;

		$errorLevel = 0;
		$errorMessage = '';
		$timeout = 1;
		$socket = @fsockopen(
			$this->getSeleniumHost(), $this->getSeleniumPort(),
			$errorLevel, $errorMessage, $timeout
		);

		if ($socket !== FALSE) {
			$seleniumServerIsRunning = TRUE;
			fclose($socket);
		}

		return $seleniumServerIsRunning;
	}

	/**
	 * Returns the configured host name of the Selenium RC server.
	 *
	 * This function returns "localhost" if no host is configured.
	 *
	 * @return string host of the Selenium RC server, will not be empty
	 */
	protected function getSeleniumHost() {
		return isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['selenium_host'])
			&& (strlen($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['selenium_host']) > 0)
			? $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['selenium_host']
			: self::DEFAULT_SELENIUM_HOST;
	}

	/**
	 * Returns the configured port number of the Selenium RC server.
	 *
	 * This functions returns 4444 (the standard Selenium RC port) if no port is
	 * is configured
	 *
	 * @return integer the elenium RC server port, will be > 0
	 */
	protected function getSeleniumPort() {
		return isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['selenium_port'])
			&& (intval($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['selenium_port']) > 0)
			? intval($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['selenium_port'])
			: self::DEFAULT_SELENIUM_PORT;
	}

	/**
	 * Returns the configured browser that should run the Selenium tests.
	 *
	 * This functions returns Firefox in chrome mode if no browser is configured.
	 *
	 * @return string Selenium RC browser, will not be empty
	 */
	protected function getSeleniumBrowser() {
		return isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['selenium_browser'])
			&& (strlen($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['selenium_browser']) > 0)
			? $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['selenium_browser']
			: self::DEFAULT_SELENIUM_BROWSER;
	}

	/**
	 * Returns the configured Selenium RC browser starting URL.
	 *
	 * This functions returns the TYPO3_SITE_URL if no URL is configured.
	 *
	 * @return string Selenium RC Browser URL, will not be empty
	 */
	protected function getSeleniumBrowserUrl() {
		return isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['selenium_browserurl'])
			&& (strlen($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['selenium_browserurl']) > 0)
			? $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['selenium_browserurl']
			: rtrim(t3lib_div::getIndpEnv('TYPO3_SITE_URL'), self::DEFAULT_SELENIUM_BROWSER_URL);
	}
}
?>