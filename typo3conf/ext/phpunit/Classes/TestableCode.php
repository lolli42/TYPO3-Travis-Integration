<?php
/***************************************************************
* Copyright notice
*
* (c) 2011 Oliver Klee <typo3-coding@oliverklee.de>
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
 * This class represents some code that can be tested.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Phpunit_TestableCode {
	/**
	 * @var integer
	 */
	const TYPE_UNDEFINED = 0;
	/**
	 * @var integer
	 */
	const TYPE_EXTENSION = 1;
	/**
	 * @var integer
	 */
	const TYPE_CORE = 2;

	/**
	 * @var string
	 */
	const CORE_KEY = 'typo3';

	/**
	 * @var string
	 */
	protected $key = '';

	/**
	 * @var string
	 */
	protected $title = '';

	/**
	 * @var integer
	 */
	protected $type = self::TYPE_UNDEFINED;

	/**
	 * @var string
	 */
	protected $codePath = '';

	/**
	 * @var string
	 */
	protected $testsPath = '';

	/**
	 * files that should be excluded from code coverage
	 *
	 * @var array<string>
	 */
	protected $blacklist = array();

	/**
	 * files that should be included in code coverage
	 *
	 * @var array<string>
	 */
	protected $whitelist = array();

	/**
	 * @var string
	 */
	protected $iconPath = '';

	/**
	 * Returns the key.
	 *
	 * The key is intended to be used e.g., for drop-downs.
	 *
	 * For extensions, this will be the extension key. For the TYPO3 core, this
	 * will be "typo3". For out-of-line tests, this will be full path to the
	 * tested code.
	 *
	 * @return string the key, will not be empty
	 */
	public function getKey() {
		return $this->key;
	}

	/**
	 * Sets the key.
	 *
	 * The key is intended to be used e.g., for drop-downs.
	 *
	 * For extensions, this must be the extension key. For the TYPO3 core, this
	 * must be "typo3". For out-of-line tests, this must be full path to the
	 * tested code.
	 *
	 * @param string $key the key, must not be empty
	 *
	 * @return void
	 */
	public function setKey($key) {
		if ($key === '') {
			throw new InvalidArgumentException('$key must not be empty.');
		}

		$this->key = $key;
	}

	/**
	 * Returns the display title.
	 *
	 * @return string the title, might be empty
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Sets the display title.
	 *
	 * @param string $title the title, may be empty
	 *
	 * @return void
	 */
	public function setTitle($title) {
		$this->title = $title;
	}

	/**
	 * Returns the type of this testable code.
	 *
	 * @return integer
	 *         the type, will be either TYPE_UNDEFINED, TYPE_EXTENSION or TYPE_CORE
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Sets the type of this testable code.
	 *
	 * @param integer $type
	 *        the type, must be either TYPE_EXTENSION or TYPE_CORE
	 *
	 * @return void
	 */
	public function setType($type) {
		$allowedTypes = array(self::TYPE_EXTENSION, self::TYPE_CORE);
		if (!in_array($type, $allowedTypes, TRUE)) {
			throw new InvalidArgumentException(
				'$type must be one of TYPE_EXTENSION, TYPE_CORE, but actually was ' . $type . '.'
			);
		}

		$this->type = $type;
	}

	/**
	 * Returns the code path.
	 *
	 * This is the absolute path of the code that is tested.
	 *
	 * @return string the code path, will not be empty
	 */
	public function getCodePath() {
		return $this->codePath;
	}

	/**
	 * Sets the code path.
	 *
	 * This is the absolute path of the code that is tested.
	 *
	 * @param string $codePath the code path, must not be empty
	 *
	 * @return void
	 */
	public function setCodePath($codePath) {
		if ($codePath === '') {
			throw new InvalidArgumentException('$codePath must not be empty.');
		}

		$this->codePath = $codePath;
	}

	/**
	 * Returns the tests path.
	 *
	 * This is the absolute path of the unit tests. Usually, this path is
	 * located within the code path.
	 *
	 * @return string the tests path, will not be empty
	 */
	public function getTestsPath() {
		return $this->testsPath;
	}

	/**
	 * Sets the tests path.
	 *
	 * This is the absolute path of the unit tests. Usually, this path is
	 * located within the code path.
	 *
	 * @param string $testsPath the tests path, must not be empty
	 *
	 * @return void
	 */
	public function setTestsPath($testsPath) {
		if ($testsPath === '') {
			throw new InvalidArgumentException('$testsPath must not be empty.');
		}

		$this->testsPath = $testsPath;
	}

	/**
	 * Returns the blacklist, i.e., the absolute paths to the files that should
	 * be excluded from the code coverage report.
	 *
	 * @return array<string>
	 *         the absolute paths to the blacklisted files, might be empty
	 */
	public function getBlacklist() {
		return $this->blacklist;
	}

	/**
	 * Sets the blacklist, i.e., the absolute paths to the files that should
	 * be excluded from the code coverage report.
	 *
	 * @param array<string> $files
	 *         the absolute paths to the blacklisted files, may be empty
	 *
	 * @return void
	 */
	public function setBlacklist(array $files) {
		$this->blacklist = $files;
	}

	/**
	 * Returns the whitelist, i.e., the absolute paths to the files that should
	 * be included in the code coverage report.
	 *
	 * @return array<string>
	 *         the absolute paths to the whitelisted files, might be empty
	 */
	public function getWhitelist() {
		return $this->whitelist;
	}

	/**
	 * Sets the whitelist, i.e., the absolute paths to the files that should
	 * be included in the code coverage report.
	 *
	 * @param array<string> $files
	 *         the absolute paths to the whitelisted files, may be empty
	 *
	 * @return void
	 */
	public function setWhitelist(array $files) {
		$this->whitelist = $files;
	}

	/**
	 * Returns the relative path to the icon associated with this testable code.
	 *
	 * @return string the relative icon path, will not be empty
	 */
	public function getIconPath() {
		return $this->iconPath;
	}

	/**
	 * Sets the relative path to the icon associated with this testable code.
	 *
	 * @param string $iconPath the icon path, must not be empty
	 *
	 * @return void
	 */
	public function setIconPath($iconPath) {
		if ($iconPath === '') {
			throw new InvalidArgumentException('$iconPath must not be empty.');
		}

		$this->iconPath = $iconPath;
	}
}
?>