<?php
/***************************************************************
* Copyright notice
*
* (c) 2010-2011 Oliver Klee <typo3-coding@oliverklee.de>
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
 * This class provides functions for finding testcases.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Phpunit_Service_TestFinder implements t3lib_Singleton {
	/**
	 * suffixes that indicate that a file is a testcase
	 *
	 * @var array<string>
	 */
	static protected $testcaseFileSuffixes = array(
		'Test.php', '_testcase.php'
	);

	/**
	 * allowed test directory names
	 *
	 * @var array<string>
	 */
	static protected $allowedTestDirectoryNames = array('Tests/', 'tests/');

	/**
	 * keys of the dummy extensions of the phpunit extension
	 *
	 * @var array<string>
	 */
	static protected $dummyExtensionKeys = array('aaa', 'bbb', 'ccc', 'ddd');

	/**
	 * a cache for the result of findTestableCodeForEverything
	 *
	 * @var array
	 */
	protected $allTestableCodeCache = array();

	/**
	 * indicates whether $allTestableCodeCache already has been filled
	 *
	 * @var boolean
	 */
	protected $allTestableCodeIsCached = FALSE;

	/**
	 * The destructor.
	 */
	public function __destruct() {
	}

	/**
	 * Gets the path of the TYPO3 Core unit tests relative to PATH_site.
	 *
	 * If there is no tests directory for the Core, this function will return an empty string.
	 *
	 * @return string
	 *         the path of the TYPO3 Core unit tests relative to PATH_site,
	 *         will be empty if there is no Core tests directory
	 */
	public function getRelativeCoreTestsPath() {
		$possibleTestsPath1 = 'tests/';
		$possibleTestsPath2 = 'typo3_src/tests/';

		if (file_exists(PATH_site .  $possibleTestsPath1)) {
			$testsPath = $possibleTestsPath1;
		} elseif (file_exists(PATH_site . $possibleTestsPath2)) {
			$testsPath = $possibleTestsPath2;
		} else {
			$testsPath = '';
		}

		return $testsPath;
	}

	/**
	 * Gets the absolute path of the TYPO3 Core unit tests.
	 *
	 * If there is no tests directory for the Core, this function will return an empty string.
	 *
	 * @return string
	 *         the absolute path of the TYPO3 Core unit tests,
	 *         will be empty if there is no Core tests directory
	 */
	public function getAbsoluteCoreTestsPath() {
		if (!$this->hasCoreTests()) {
			return '';
		}

		return PATH_site . $this->getRelativeCoreTestsPath();
	}

	/**
	 * Checks whether the TYPO3 Core has a tests directory.
	 *
	 * @return boolean TRUE if the TYPO3 Core has a tests directory, FALSE otherwise
	 */
	public function hasCoreTests() {
		return ($this->getRelativeCoreTestsPath() !== '');
	}

	/**
	 * Finds all files that are named like test files in the directory $directory
	 * and recursively all its subdirectories.
	 *
	 * @param string $directory
	 *        the absolute path of the directory in which to look for test cases
	 *
	 * @return array
	 *         sorted file names of the testcases in the directory $directory relative
	 *         to $directory, will be empty if no testcases have been found
	 */
	public function findTestCasesInDirectory($directory) {
		if ($directory === '') {
			throw new InvalidArgumentException('$directory must not be empty.');
		}
		if (!is_dir($directory)) {
			throw new InvalidArgumentException('The directory '. $directory . ' does not exist.');
		}
		if (!is_readable($directory)) {
			throw new InvalidArgumentException('The directory '. $directory . ' exists, but is not readable.');
		}

		$directoryLength = strlen($directory);

		$testFiles = array();
		$allPhpFiles = t3lib_div::getAllFilesAndFoldersInPath(array(), $directory, 'php');
		foreach ($allPhpFiles as $filePath) {
			if ($this->isTestCaseFileName($filePath)) {
				$testFiles[] = substr($filePath, $directoryLength);
			}
		}

		sort($testFiles, SORT_STRING);

		return $testFiles;
	}

	/**
	 * Checks whether a file name is named like a testcase file name should be.
	 *
	 * @param string $path the absolute path of a file to check
	 *
	 * @return boolean TRUE if $fileName is names like a proper testcase, FALSE otherwise
	 */
	protected function isTestCaseFileName($path) {
		$fileName = basename($path);
		if ($this->isHiddenMacFile($fileName)) {
			return FALSE;
		}

		$isTestCase = FALSE;
		foreach (self::$testcaseFileSuffixes as $suffix) {
			if (substr($fileName, - strlen($suffix)) === $suffix) {
				$isTestCase = TRUE;
				break;
			}
		}

		return $isTestCase;
	}

	/**
	 * Checks whether $fileName is a hidden Mac file.
	 *
	 * @param string $fileName base name of a file to check
	 *
	 * @return TRUE if $fileName is a hidden Mac file, FALSE otherwise
	 */
	protected function isHiddenMacFile($fileName) {
		return (substr($fileName, 0, 2) === '._');
	}

	/**
	 * Checks whether there is testable code for a key.
	 *
	 * @param string $key
	 *        the key to check, might be an extension key, the core key or
	 *        any other string (even an empty string)
	 *
	 * @return boolean TRUE if there is testable code with the given key, FALSE otherwise
	 */
	public function existsTestableCodeForKey($key) {
		if ($key === '') {
			return FALSE;
		}

		$allTestableCode = $this->getTestableCodeForEverything();

		return isset($allTestableCode[$key]);
	}

	/**
	 * Returns the testable code instance for everything, i.e., the core and
	 * all installed extensions.
	 *
	 * @return array<Tx_Phpunit_TestableCode>
	 *         testable code for everything using the extension keys or the core key
	 *         as array keys, might be empty
	 */
	public function getTestableCodeForEverything() {
		if (!$this->allTestableCodeIsCached) {
			$this->allTestableCodeCache = array_merge(
				$this->getTestableCodeForCore(), $this->getTestableCodeForExtensions()
			);

			$this->allTestableCodeIsCached = TRUE;
		}

		return $this->allTestableCodeCache;
	}

	/**
	 * Returns the testable code for the TYPO3 Core.
	 *
	 * @return array<Tx_Phpunit_TestableCode>
	 *         testable code for the TYPO3 core, will have exactly one element if
	 *         there are Core tests (using the core key as array key),
	 *         will be empty if there are no Core tests
	 */
	public function getTestableCodeForCore() {
		if (!$this->hasCoreTests()) {
			return array();
		}

		$coreTests = t3lib_div::makeInstance('Tx_Phpunit_TestableCode');
		$coreTests->setType(Tx_Phpunit_TestableCode::TYPE_CORE);
		$coreTests->setKey(Tx_Phpunit_TestableCode::CORE_KEY);
		$coreTests->setTitle('TYPO3 Core');
		$coreTests->setCodePath(PATH_site);
		$coreTests->setTestsPath($this->getAbsoluteCoreTestsPath());
		$coreTests->setIconPath(t3lib_extMgm::extRelPath('phpunit') . 'Resources/Public/Icons/Typo3.png');

		return array(Tx_Phpunit_TestableCode::CORE_KEY => $coreTests);
	}

	/**
	 * Returns the testable code for all installed extensions.
	 *
	 * Extensions without a test directory and extensions in the "exclude list"
	 * will be skipped.
	 *
	 * @return array<Tx_Phpunit_TestableCode>
	 *         testable code for the installed extensions using the extension keys
	 *         as array keys, might be empty
	 */
	public function getTestableCodeForExtensions() {
		$result = array();

		$extensionKeysToExamine = array_diff(
			$this->getLoadedExtensionKeys(),
			$this->getExcludedExtensionKeys(), $this->getDummyExtensionKeys()
		);

		foreach ($extensionKeysToExamine as $extensionKey) {
			try {
				$result[$extensionKey] = $this->createTestableCodeForSingleExtension($extensionKey);
			} catch (Tx_Phpunit_Exception_NoTestsDirectory $exception) {
				// Just skip extensions without a tests directory.
			}
		}

		return $result;
	}

	/**
	 * Returns the keys of the loaded extensions.
	 *
	 * @return array<string> the keys of the loaded extensions, might be empty
	 */
	protected function getLoadedExtensionKeys() {
		if (!isset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extList'])) {
			return array();
		}

		return t3lib_div::trimExplode(',', $GLOBALS['TYPO3_CONF_VARS']['EXT']['extList'], TRUE);
	}

	/**
	 * Returns the keys of the extensions excluded from unit testing via the
	 * phpunit configuration.
	 *
	 * @return array<string> the keys of the excluded extensions, might be empty
	 */
	protected function getExcludedExtensionKeys() {
		if (!isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['excludeextensions'])) {
			return array();
		}

		return t3lib_div::trimExplode(',', $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['excludeextensions'], TRUE);
	}

	/**
	 * Returns the keys of the extensions excluded from unit testing because
	 * they are the dummy extensions of phpunit.
	 *
	 * @return array<string> the keys of the dummy extensions, will not be empty
	 */
	public function getDummyExtensionKeys() {
		return self::$dummyExtensionKeys;
	}

	/**
	 * Creates the testable code instance for the extension with the given key.
	 *
	 * @param string $extensionKey the key of an installed extension, must not be empty
	 *
	 * @return Tx_Phpunit_TestableCode the test-relevant data of the installed extension
	 *
	 * @throws Tx_Phpunit_Exception_NoTestsDirectory if the given extension has no tests directory
	 */
	protected function createTestableCodeForSingleExtension($extensionKey) {
		$testsPath = $this->findTestsPathForExtension($extensionKey);

		$testableCode = t3lib_div::makeInstance('Tx_Phpunit_TestableCode');
		$testableCode->setType(Tx_Phpunit_TestableCode::TYPE_EXTENSION);
		$testableCode->setKey($extensionKey);
		$testableCode->setTitle($this->retrieveExtensionTitle($extensionKey));
		$testableCode->setCodePath(t3lib_extMgm::extPath($extensionKey));
		$testableCode->setTestsPath($testsPath);
		$testableCode->setIconPath(t3lib_extMgm::extRelPath($extensionKey) . 'ext_icon.gif');

		return $testableCode;
	}

	/**
	 * Finds the absolute path to the tests of the extension with the key $extensionKey.
	 *
	 * @param string $extensionKey the key of an installed extension, must not be empty
	 *
	 * @return string
	 *         the absolute path of the tests directory of the given extension
	 *         (might differ in case from the actual tests directory on case-insensitive
	 *         file systems)
	 *
	 * @throws Tx_Phpunit_Exception_NoTestsDirectory if the given extension has no tests directory
	 */
	protected function findTestsPathForExtension($extensionKey) {
		if ($extensionKey === '') {
			throw new InvalidArgumentException('$extensionKey must not be empty.');
		}

		$testsPath = '';
		$extensionPath = t3lib_extMgm::extPath($extensionKey);
		foreach (self::$allowedTestDirectoryNames as $testDirectoryName) {
			if (is_dir($extensionPath . $testDirectoryName)) {
				$testsPath = $extensionPath . $testDirectoryName;
				break;
			}
		}

		if ($testsPath === '') {
			throw new Tx_Phpunit_Exception_NoTestsDirectory(
				'The extension "' . $extensionKey . '" does not have a tests directory.'
			);
		}

		return $testsPath;
	}

	/**
	 * Retrieves the title of an installed extension.
	 *
	 * @param string $extensionKey the key of the extension to retrieve, must not be empty
	 *
	 * @return string the title of the extension with the given key, might be empty
	 */
	protected function retrieveExtensionTitle($extensionKey) {
		if ($extensionKey === '') {
			throw new InvalidArgumentException('$extensionKey must not be empty.');
		}

		$EM_CONF = array();
		$_EXTKEY = $extensionKey;
		include(t3lib_extMgm::extPath($extensionKey) . 'ext_emconf.php');

		return $EM_CONF[$extensionKey]['title'];
	}
}
?>