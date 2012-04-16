<?php
/***************************************************************
* Copyright notice
*
* (c) 2010-2011 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Testcase for the Tx_Phpunit_Service_TestFinder class.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Phpunit_Service_TestFinderTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Phpunit_Service_TestFinder
	 */
	private $fixture = NULL;

	/**
	 * the absolute path to the fixtures directory for this testcase
	 *
	 * @var string
	 */
	private $fixturesPath = '';

	/**
	 * backup of $GLOBALS['TYPO3_CONF_VARS']
	 *
	 * @var array
	 */
	private $typo3ConfigurationVariablesBackup = array();

	public function setUp() {
		$this->typo3ConfigurationVariablesBackup = $GLOBALS['TYPO3_CONF_VARS'];

		$this->fixture = $this->createAccessibleProxy();

		$this->fixturesPath = t3lib_extMgm::extPath('phpunit') . 'Tests/Service/Fixtures/';
	}

	public function tearDown() {
		$this->fixture->__destruct();
		unset($this->fixture);

		$GLOBALS['TYPO3_CONF_VARS'] = $this->typo3ConfigurationVariablesBackup;
	}


	/*
	 * Utility functions
	 */

	/**
	 * Creates a subclass Tx_Phpunit_Service_TestFinder with the protected
	 * functions made public.
	 *
	 * @return Tx_Phpunit_Service_TestFinder an accessible proxy
	 */
	private function createAccessibleProxy() {
		$className = 'Tx_Phpunit_Service_TestFinderAccessibleProxy';
		if (!class_exists($className, FALSE)) {
			eval(
				'class ' . $className . ' extends Tx_Phpunit_Service_TestFinder {' .
				'  public function isTestCaseFileName($path) {' .
				'    return parent::isTestCaseFileName($path);' .
				'  }' .
				'  public function getLoadedExtensionKeys() {' .
				'    return parent::getLoadedExtensionKeys();' .
				'  }' .
				'  public function getExcludedExtensionKeys() {' .
				'    return parent::getExcludedExtensionKeys();' .
				'  }' .
				'  public function getDummyExtensionKeys() {' .
				'    return parent::getDummyExtensionKeys();' .
				'  }' .
				'  public function findTestsPathForExtension($extensionKey) {' .
				'    return parent::findTestsPathForExtension($extensionKey);' .
				'  }' .
				'  public function retrieveExtensionTitle($extensionKey) {' .
				'    return parent::retrieveExtensionTitle($extensionKey);' .
				'  }' .
				'}'
			);
		}

		return new $className();
	}

	/**
	 * @test
	 */
	public function createAccessibleProxyCreatesTestFinderSubclass() {
		$this->assertTrue(
			$this->createAccessibleProxy() instanceof Tx_Phpunit_Service_TestFinder
		);
	}


	/*
	 * Unit tests
	 */

	/**
	 * @test
	 */
	public function classIsSingleton() {
		$this->assertTrue(
			$this->fixture instanceof t3lib_Singleton
		);
	}

	/**
	 * @test
	 */
	public function getRelativeCoreTestsPathCanFindTestsInCoreSourceInSitePath() {
		if (!file_exists(PATH_site . 'typo3_src/tests/')) {
			$this->markTestSkipped('This test can only be run if the Core tests are located in typo3_src/tests/.');
		}

		$this->assertSame(
			'typo3_src/tests/',
			$this->fixture->getRelativeCoreTestsPath()
		);
	}

	/**
	 * @test
	 */
	public function getRelativeCoreTestsPathCanFindTestsDirectlyInSitePath() {
		if (!file_exists(PATH_site . 'tests/')) {
			$this->markTestSkipped('This test can only be run if the Core tests are located in tests/.');
		}

		$this->assertSame(
			'typo3_src/tests/',
			$this->fixture->getRelativeCoreTestsPath()
		);
	}

	/**
	 * @test
	 */
	public function getRelativeCoreTestsPathForNoCoreTestsReturnsEmptyString() {
		if (file_exists(PATH_site . 'tests/') || file_exists(PATH_site . 'typo3_src/tests/')) {
			$this->markTestSkipped('This test can only be run if there are no Core tests.');
		}

		$this->assertSame(
			'',
			$this->fixture->getRelativeCoreTestsPath()
		);
	}

	/**
	 * @test
	 */
	public function getAbsoluteCoreTestsPathCanFindTestsInCoreSourceInSitePath() {
		if (!file_exists(PATH_site . 'typo3_src/tests/')) {
			$this->markTestSkipped('This test can only be run if the Core tests are located in typo3_src/tests/.');
		}

		$this->assertSame(
			PATH_site . 'typo3_src/tests/',
			$this->fixture->getAbsoluteCoreTestsPath()
		);
	}

	/**
	 * @test
	 */
	public function getAbsoluteCoreTestsPathCanFindTestsDirectlyInSitePath() {
		if (!file_exists(PATH_site . 'tests/')) {
			$this->markTestSkipped('This test can only be run if the Core tests are located in tests/.');
		}

		$this->assertSame(
			PATH_site . 'typo3_src/tests/',
			$this->fixture->getAbsoluteCoreTestsPath()
		);
	}

	/**
	 * @test
	 */
	public function getAbsoluteCoreTestsPathForNoCoreTestsReturnsEmptyString() {
		if (file_exists(PATH_site . 'tests/') || file_exists(PATH_site . 'typo3_src/tests/')) {
			$this->markTestSkipped('This test can only be run if there are no Core tests.');
		}

		$this->assertSame(
			'',
			$this->fixture->getAbsoluteCoreTestsPath()
		);
	}

	/**
	 * @test
	 */
	public function hasCoreTestsForCoreTestsInCoreSourceInSitePathReturnsTrue() {
		if (!file_exists(PATH_site . 'typo3_src/tests/')) {
			$this->markTestSkipped('This test can only be run if the Core tests are located in typo3_src/tests/.');
		}

		$this->assertTrue(
			$this->fixture->hasCoreTests()
		);
	}

	/**
	 * @test
	 */
	public function hasCoreTestsForCoreTestsDirectlyInSitePathReturnsTrue() {
		if (!file_exists(PATH_site . 'tests/')) {
			$this->markTestSkipped('This test can only be run if the Core tests are located in typo3_src/tests/.');
		}

		$this->assertTrue(
			$this->fixture->hasCoreTests()
		);
	}

	/**
	 * @test
	 */
	public function hasCoreTestsForNoCoreTestsReturnsFalse() {
		if (file_exists(PATH_site . 'tests/') || file_exists(PATH_site . 'typo3_src/tests/')) {
			$this->markTestSkipped('This test can only be run if there are no Core tests.');
		}

		$this->assertFalse(
			$this->fixture->hasCoreTests()
		);
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function findTestCasesInDirectoryForEmptyPathThrowsException() {
		$this->fixture->findTestCasesInDirectory('');
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function findTestCasesInDirectoryForInexistentPathThrowsException() {
		$this->fixture->findTestCasesInDirectory(
			$this->fixturesPath . 'DoesNotExist/'
		);
	}

	/**
	 * @test
	 */
	public function findTestCasesInDirectoryForEmptyDirectoryReturnsEmptyArray() {
		$this->assertSame(
			array(),
			$this->fixture->findTestCasesInDirectory($this->fixturesPath . 'Empty/')
		);
	}

	/**
	 * @test
	 */
	public function findTestCasesInDirectoryFindsFileWithProperTestcaseFileName() {
		$path = 'OneTest.php';

		$fixture = $this->getMock(
			'Tx_Phpunit_Service_TestFinder', array('isTestCaseFileName')
		);
		$fixture->expects($this->at(0))->method('isTestCaseFileName')
			->with($this->fixturesPath . $path)->will($this->returnValue(TRUE));

		$this->assertContains(
			$path,
			$fixture->findTestCasesInDirectory($this->fixturesPath)
		);
	}

	/**
	 * @test
	 */
	public function findTestCasesInDirectoryNotFindsFileWithNonProperTestcaseFileName() {
		$path = 'OneTest.php';

		$fixture = $this->getMock(
			'Tx_Phpunit_Service_TestFinder', array('isTestCaseFileName')
		);
		$fixture->expects($this->at(0))->method('isTestCaseFileName')
			->with($this->fixturesPath . $path)->will($this->returnValue(FALSE));

		$this->assertNotContains(
			$path,
			$fixture->findTestCasesInDirectory($this->fixturesPath)
		);
	}

	/**
	 * @test
	 */
	public function findTestCasesInDirectoryFindsTestcaseInSubfolder() {
		$path = 'Subfolder/AnotherTest.php';

		$this->assertContains(
			$path,
			$this->fixture->findTestCasesInDirectory($this->fixturesPath)
		);
	}

	/**
	 * @test
	 */
	public function findTestCasesInDirectoryAcceptsPathWithTrailingSlash() {
		$result = $this->fixture->findTestCasesInDirectory($this->fixturesPath);

		$this->assertFalse(
			empty($result)
		);
	}

	/**
	 * @test
	 */
	public function findTestCasesInDirectoryAcceptsPathWithoutTrailingSlash() {
		$result = $this->fixture->findTestCasesInDirectory(
			t3lib_extMgm::extPath('phpunit') . 'Tests/Service/Fixtures'
		);

		$this->assertFalse(
			empty($result)
		);
	}

	/**
	 * @test
	 */
	public function findTestCasesInDirectorySortsFileNamesInAscendingOrder() {
		$result = $this->fixture->findTestCasesInDirectory($this->fixturesPath);

		$fileName1 = 'OneTest.php';
		$fileName2 = 'XTest.php';

		$this->assertTrue(
			array_search($fileName1, $result) < array_search($fileName2, $result)
		);
	}


	/**
	 * @test
	 */
	public function isTestCaseFileNameForTestSuffixReturnsTrue() {
		$this->assertTrue(
			$this->fixture->isTestCaseFileName(
				$this->fixturesPath . 'OneTest.php'
			)
		);
	}

	/**
	 * @test
	 */
	public function isTestCaseFileNameForTestcaseSuffixReturnsTrue() {
		$this->assertTrue(
			$this->fixture->isTestCaseFileName(
				$this->fixturesPath . 'Another_testcase.php'
			)
		);
	}

	/**
	 * @test
	 */
	public function isTestCaseFileNameForOtherPhpFileReturnsFalse() {
		$this->assertFalse(
			$this->fixture->isTestCaseFileName(
				$this->fixturesPath . 'SomethingDifferent.php'
			)
		);
	}

	/**
	 * @test
	 *
	 * @see http://forge.typo3.org/issues/9094
	 */
	public function isTestCaseFileNameForHiddenMacFileReturnsFalse() {
		$this->assertFalse(
			$this->fixture->isTestCaseFileName(
				$this->fixturesPath . '._tx_tendbook_testTest.php'
			)
		);
	}

	/**
	 * @test
	 */
	public function existsTestableCodeForKeyForEmptyKeyReturnsFalse() {
		$fixture = $this->getMock('Tx_Phpunit_Service_TestFinder', array('getTestableCodeForEverything'));
		$fixture->expects($this->any())->method('getTestableCodeForEverything')
			->will($this->returnValue(array('foo' => new Tx_Phpunit_TestableCode())));

		$this->assertFalse(
			$fixture->existsTestableCodeForKey('')
		);
	}

	/**
	 * @test
	 */
	public function existsTestableCodeForKeyForExistingKeyReturnsTrue() {
		$fixture = $this->getMock('Tx_Phpunit_Service_TestFinder', array('getTestableCodeForEverything'));
		$fixture->expects($this->any())->method('getTestableCodeForEverything')
			->will($this->returnValue(array('foo' => new Tx_Phpunit_TestableCode())));

		$this->assertTrue(
			$fixture->existsTestableCodeForKey('foo')
		);
	}

	/**
	 * @test
	 */
	public function existsTestableCodeForKeyForInexistentKeyReturnsFalse() {
		$fixture = $this->getMock('Tx_Phpunit_Service_TestFinder', array('getTestableCodeForEverything'));
		$fixture->expects($this->any())->method('getTestableCodeForEverything')
			->will($this->returnValue(array('foo' => new Tx_Phpunit_TestableCode())));

		$this->assertFalse(
			$fixture->existsTestableCodeForKey('bar')
		);
	}

	/**
	 * @test
	 */
	public function getTestableCodeForEverythingForNoCoreTestsAndNoExtensionTestsReturnsEmptyArray() {
		$testFinder = $this->getMock('Tx_Phpunit_Service_TestFinder', array('getTestableCodeForCore', 'getTestableCodeForExtensions'));
		$testFinder->expects($this->once())->method('getTestableCodeForCore')->will($this->returnValue(array()));
		$testFinder->expects($this->once())->method('getTestableCodeForExtensions')->will($this->returnValue(array()));

		$this->assertSame(
			array(),
			$testFinder->getTestableCodeForEverything()
		);
	}

	/**
	 * @test
	 */
	public function getTestableCodeForEverythingForCoreTestsAndNoExtensionTestsReturnsCoreTests() {
		$coreTests = new Tx_Phpunit_TestableCode();

		$testFinder = $this->getMock('Tx_Phpunit_Service_TestFinder', array('getTestableCodeForCore', 'getTestableCodeForExtensions'));
		$testFinder->expects($this->once())->method('getTestableCodeForCore')
			->will($this->returnValue(array(Tx_Phpunit_TestableCode::CORE_KEY => $coreTests)));
		$testFinder->expects($this->once())->method('getTestableCodeForExtensions')->will($this->returnValue(array()));

		$this->assertSame(
			array(Tx_Phpunit_TestableCode::CORE_KEY => $coreTests),
			$testFinder->getTestableCodeForEverything()
		);
	}

	/**
	 * @test
	 */
	public function getTestableCodeForEverythingForCoreTestsAndNoExtensionTestsReturnsExtensionTests() {
		$extensionTests = new Tx_Phpunit_TestableCode();

		$testFinder = $this->getMock('Tx_Phpunit_Service_TestFinder', array('getTestableCodeForCore', 'getTestableCodeForExtensions'));
		$testFinder->expects($this->once())->method('getTestableCodeForCore')
			->will($this->returnValue(array()));
		$testFinder->expects($this->once())->method('getTestableCodeForExtensions')
			->will($this->returnValue(array('foo' => $extensionTests)));

		$this->assertSame(
			array('foo' => $extensionTests),
			$testFinder->getTestableCodeForEverything()
		);
	}

	/**
	 * @test
	 */
	public function getTestableCodeForEverythingForCoreTestsAndExtensionTestsReturnsCoreAndExtensionTests() {
		$coreTests = new Tx_Phpunit_TestableCode();
		$extensionTests = new Tx_Phpunit_TestableCode();

		$testFinder = $this->getMock('Tx_Phpunit_Service_TestFinder', array('getTestableCodeForCore', 'getTestableCodeForExtensions'));
		$testFinder->expects($this->once())->method('getTestableCodeForCore')
			->will($this->returnValue(array(Tx_Phpunit_TestableCode::CORE_KEY => $coreTests)));
		$testFinder->expects($this->once())->method('getTestableCodeForExtensions')
			->will($this->returnValue(array('foo' => $extensionTests)));

		$this->assertSame(
			array(
				Tx_Phpunit_TestableCode::CORE_KEY => $coreTests,
				'foo' => $extensionTests,
			),
			$testFinder->getTestableCodeForEverything()
		);
	}

	/**
	 * @test
	 */
	public function getTestableCodeForEverythingForCoreTestsAndExtensionCalledTwoTimesReturnsSameData() {
		$coreTests = new Tx_Phpunit_TestableCode();
		$extensionTests = new Tx_Phpunit_TestableCode();

		$testFinder = $this->getMock('Tx_Phpunit_Service_TestFinder', array('getTestableCodeForCore', 'getTestableCodeForExtensions'));
		$testFinder->expects($this->any())->method('getTestableCodeForCore')
			->will($this->returnValue(array(Tx_Phpunit_TestableCode::CORE_KEY => $coreTests)));
		$testFinder->expects($this->any())->method('getTestableCodeForExtensions')
			->will($this->returnValue(array('foo' => $extensionTests)));

		$this->assertSame(
			$testFinder->getTestableCodeForEverything(),
			$testFinder->getTestableCodeForEverything()
		);
	}

	/**
	 * @test
	 */
	public function getTestableCodeForCoreForNoCoreTestsReturnsEmptyArray() {
		$testFinder = $this->getMock('Tx_Phpunit_Service_TestFinder', array('hasCoreTests'));
		$testFinder->expects($this->once())->method('hasCoreTests')->will($this->returnValue(FALSE));

		$this->assertSame(
			array(),
			$testFinder->getTestableCodeForCore()
		);
	}

	/**
	 * @test
	 */
	public function getTestableCodeForCoreExistingCoreTestsReturnsExactlyOneTestableCodeInstanceUsingCoreArrayKey() {
		$testFinder = $this->getMock('Tx_Phpunit_Service_TestFinder', array('hasCoreTests', 'getAbsoluteCoreTestsPath'));
		$testFinder->expects($this->once())->method('hasCoreTests')->will($this->returnValue(TRUE));
		$testFinder->expects($this->once())->method('getAbsoluteCoreTestsPath')->will($this->returnValue('/core/tests/'));

		$result = $testFinder->getTestableCodeForCore();

		$this->assertSame(
			1,
			count($result),
			'The return array does not have exactly one element.'
		);
		$this->assertInstanceOf(
			'Tx_Phpunit_TestableCode',
			$result[Tx_Phpunit_TestableCode::CORE_KEY]
		);
	}

	/**
	 * @test
	 */
	public function getTestableCodeForCoreExistingCoreTestsReturnsTestableCodeWithCoreType() {
		$testFinder = $this->getMock('Tx_Phpunit_Service_TestFinder', array('hasCoreTests', 'getAbsoluteCoreTestsPath'));
		$testFinder->expects($this->once())->method('hasCoreTests')->will($this->returnValue(TRUE));
		$testFinder->expects($this->once())->method('getAbsoluteCoreTestsPath')->will($this->returnValue('/core/tests/'));

		$this->assertSame(
			Tx_Phpunit_TestableCode::TYPE_CORE,
			array_pop($testFinder->getTestableCodeForCore())->getType()
		);
	}

	/**
	 * @test
	 */
	public function getTestableCodeForCoreExistingCoreTestsReturnsTestableCodeWithTypo3Key() {
		$testFinder = $this->getMock('Tx_Phpunit_Service_TestFinder', array('hasCoreTests', 'getAbsoluteCoreTestsPath'));
		$testFinder->expects($this->once())->method('hasCoreTests')->will($this->returnValue(TRUE));
		$testFinder->expects($this->once())->method('getAbsoluteCoreTestsPath')->will($this->returnValue('/core/tests/'));

		$this->assertSame(
			Tx_Phpunit_TestableCode::CORE_KEY,
			array_pop($testFinder->getTestableCodeForCore())->getKey()
		);
	}

	/**
	 * @test
	 */
	public function getTestableCodeForCoreExistingCoreTestsReturnsTestableCodeWithTypo3CoreTitle() {
		$testFinder = $this->getMock('Tx_Phpunit_Service_TestFinder', array('hasCoreTests', 'getAbsoluteCoreTestsPath'));
		$testFinder->expects($this->once())->method('hasCoreTests')->will($this->returnValue(TRUE));
		$testFinder->expects($this->once())->method('getAbsoluteCoreTestsPath')->will($this->returnValue('/core/tests/'));

		$this->assertSame(
			'TYPO3 Core',
			array_pop($testFinder->getTestableCodeForCore())->getTitle()
		);
	}

	/**
	 * @test
	 */
	public function getTestableCodeForCoreExistingCoreTestsReturnsTestableCodeWithSitePath() {
		$testFinder = $this->getMock('Tx_Phpunit_Service_TestFinder', array('hasCoreTests', 'getAbsoluteCoreTestsPath'));
		$testFinder->expects($this->once())->method('hasCoreTests')->will($this->returnValue(TRUE));
		$testFinder->expects($this->once())->method('getAbsoluteCoreTestsPath')->will($this->returnValue('/core/tests/'));

		$this->assertSame(
			PATH_site,
			array_pop($testFinder->getTestableCodeForCore())->getCodePath()
		);
	}

	/**
	 * @test
	 */
	public function getTestableCodeForCoreExistingCoreTestsReturnsTestableCodeWithCoreTestsPath() {
		$coreTestsPath = '/core/tests/';

		$testFinder = $this->getMock('Tx_Phpunit_Service_TestFinder', array('hasCoreTests', 'getAbsoluteCoreTestsPath'));
		$testFinder->expects($this->once())->method('hasCoreTests')->will($this->returnValue(TRUE));
		$testFinder->expects($this->once())->method('getAbsoluteCoreTestsPath')->will($this->returnValue($coreTestsPath));

		$this->assertSame(
			$coreTestsPath,
			array_pop($testFinder->getTestableCodeForCore())->getTestsPath()
		);
	}

	/**
	 * @test
	 */
	public function getTestableCodeForCoreExistingCoreTestsReturnsTestableCodeWithTypo3IconPath() {
		$testFinder = $this->getMock('Tx_Phpunit_Service_TestFinder', array('hasCoreTests', 'getAbsoluteCoreTestsPath'));
		$testFinder->expects($this->once())->method('hasCoreTests')->will($this->returnValue(TRUE));
		$testFinder->expects($this->once())->method('getAbsoluteCoreTestsPath')->will($this->returnValue('/core/tests/'));

		$this->assertSame(
			t3lib_extMgm::extRelPath('phpunit') . 'Resources/Public/Icons/Typo3.png',
			array_pop($testFinder->getTestableCodeForCore())->getIconPath()
		);
	}

	/**
	 * @test
	 */
	public function getLoadedExtensionKeysReturnsKeysOfLoadedExtensions() {
		$GLOBALS['TYPO3_CONF_VARS']['EXT']['extList'] = 'foo,bar';

		$this->assertSame(
			array('foo', 'bar'),
			$this->fixture->getLoadedExtensionKeys()
		);
	}

	/**
	 * @test
	 */
	public function getExcludedExtensionKeysReturnsKeysOfExcludedExtensions() {
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['excludeextensions'] = 'foo,bar';

		$this->assertSame(
			array('foo', 'bar'),
			$this->fixture->getExcludedExtensionKeys()
		);
	}

	/**
	 * @test
	 */
	public function getExcludedExtensionKeysForNoExcludedExtensionsReturnsEmptyArray() {
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['excludeextensions'] = '';

		$this->assertSame(
			array(),
			$this->fixture->getExcludedExtensionKeys()
		);
	}

	/**
	 * @test
	 */
	public function getExcludedExtensionKeysForNoPhpUnitConfigurationReturnsEmptyArray() {
		unset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['excludeextensions']);

		$this->assertSame(
			array(),
			$this->fixture->getExcludedExtensionKeys()
		);
	}

	/**
	 * @test
	 */
	public function getDummyExtensionKeysReturnsKeysOfPhpUnitDummyExtensions() {
		$this->assertSame(
			array('aaa', 'bbb', 'ccc', 'ddd'),
			$this->fixture->getDummyExtensionKeys()
		);
	}

	/**
	 * @test
	 */
	public function getTestableCodeForExtensionsCreatesTestableCodeForSingleExtensionForInstalledExtensionsWithoutExcludedExtensions() {
		$testFinder = $this->getMock(
			'Tx_Phpunit_Service_TestFinder',
			array('getLoadedExtensionKeys', 'getExcludedExtensionKeys', 'findTestsPathForExtension', 'createTestableCodeForSingleExtension')
		);
		$testFinder->expects($this->once())->method('getLoadedExtensionKeys')->will($this->returnValue(array('foo', 'bar', 'foobar')));
		$testFinder->expects($this->once())->method('getExcludedExtensionKeys')->will($this->returnValue(array('foo', 'baz')));

		$testFinder->expects($this->at(2))->method('createTestableCodeForSingleExtension')->with('bar');
		$testFinder->expects($this->at(3))->method('createTestableCodeForSingleExtension')->with('foobar');

		$testFinder->getTestableCodeForExtensions();
	}

	/**
	 * @test
	 */
	public function getTestableCodeForExtensionsCreatesTestableCodeForSingleExtensionForInstalledExtensionsWithoutDummyExtensions() {
		$testFinder = $this->getMock(
			'Tx_Phpunit_Service_TestFinder',
			array('getLoadedExtensionKeys', 'getExcludedExtensionKeys', 'getDummyExtensionKeys',
				'findTestsPathForExtension', 'createTestableCodeForSingleExtension',
			)
		);
		$testFinder->expects($this->once())->method('getLoadedExtensionKeys')->will($this->returnValue(array('foo', 'bar', 'foobar')));
		$testFinder->expects($this->once())->method('getExcludedExtensionKeys')->will($this->returnValue(array()));
		$testFinder->expects($this->once())->method('getDummyExtensionKeys')->will($this->returnValue(array('foo', 'baz')));

		$testFinder->expects($this->at(3))->method('createTestableCodeForSingleExtension')->with('bar');
		$testFinder->expects($this->at(4))->method('createTestableCodeForSingleExtension')->with('foobar');

		$testFinder->getTestableCodeForExtensions();
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function findTestsPathForExtensionForExtensionWithEmptyExtensionKeyThrowsException() {
		$this->fixture->findTestsPathForExtension('');
	}

	/**
	 * @test
	 *
	 * @expectedException Tx_Phpunit_Exception_NoTestsDirectory
	 */
	public function findTestsPathForExtensionForExtensionWithoutTestsPathThrowsException() {
		if (!t3lib_extMgm::isLoaded('aaa')) {
			$this->markTestSkipped(
				'This test can only be run if the extension "aaa" from Tests/res is installed.'
			);
		}

		$this->fixture->findTestsPathForExtension('aaa');
	}

	/**
	 * @test
	 *
	 * Note: This tests uses a lowercase compare because some systems use a
	 * case-insensitive file system.
	 */
	public function findTestsPathForExtensionForExtensionWithUpperFirstTestsDirectoryReturnsThatDirectory() {
		$this->assertSame(
			strtolower(t3lib_extMgm::extPath('phpunit') . 'Tests/'),
			strtolower($this->fixture->findTestsPathForExtension('phpunit'))
		);
	}

	/**
	 * @test
	 *
	 * Note: This tests uses a lowercase compare because some systems use a
	 * case-insensitive file system.
	 */
	public function findTestsPathForExtensionForExtensionWithLowerCaseTestsDirectoryReturnsThatDirectory() {
		if (!t3lib_extMgm::isLoaded('bbb')) {
			$this->markTestSkipped(
				'This test can only be run if the extension "bbb" from Tests/res is installed.'
			);
		}

		$this->assertSame(
			strtolower(t3lib_extMgm::extPath('bbb') . 'tests/'),
			strtolower($this->fixture->findTestsPathForExtension('bbb'))
		);
	}

	/**
	 * @test
	 */
	public function getTestableCodeForExtensionsForNoInstalledExtensionsReturnsEmptyArray() {
		$testFinder = $this->getMock('Tx_Phpunit_Service_TestFinder', array('getLoadedExtensionKeys'));
		$testFinder->expects($this->once())->method('getLoadedExtensionKeys')->will($this->returnValue(array()));

		$this->assertSame(
			array(),
			$testFinder->getTestableCodeForExtensions()
		);
	}

	/**
	 * @test
	 */
	public function getTestableCodeForExtensionsForOneInstalledExtensionsWithTestsReturnsOneTestableCodeInstance() {
		$testableCodeInstance = new Tx_Phpunit_TestableCode();

		$testFinder = $this->getMock(
			'Tx_Phpunit_Service_TestFinder',
			array('getLoadedExtensionKeys', 'getExcludedExtensionKeys', 'findTestsPathForExtension', 'createTestableCodeForSingleExtension')
		);
		$testFinder->expects($this->once())->method('getLoadedExtensionKeys')->will($this->returnValue(array('foo')));
		$testFinder->expects($this->once())->method('getExcludedExtensionKeys')->will($this->returnValue(array()));
		$testFinder->expects($this->once())->method('createTestableCodeForSingleExtension')
			->with('foo')->will($this->returnValue($testableCodeInstance));

		$this->assertSame(
			array('foo' => $testableCodeInstance),
			$testFinder->getTestableCodeForExtensions()
		);
	}

	/**
	 * @test
	 */
	public function getTestableCodeForExtensionsForTwoInstalledExtensionsWithTestsReturnsTwoResults() {
		$testFinder = $this->getMock(
			'Tx_Phpunit_Service_TestFinder',
			array('getLoadedExtensionKeys', 'getExcludedExtensionKeys', 'findTestsPathForExtension', 'createTestableCodeForSingleExtension')
		);
		$testFinder->expects($this->once())->method('getLoadedExtensionKeys')->will($this->returnValue(array('foo', 'bar')));
		$testFinder->expects($this->once())->method('getExcludedExtensionKeys')->will($this->returnValue(array()));
		$testFinder->expects($this->at(2))->method('createTestableCodeForSingleExtension')
			->with('foo')->will($this->returnValue(new Tx_Phpunit_TestableCode()));
		$testFinder->expects($this->at(3))->method('createTestableCodeForSingleExtension')
			->with('bar')->will($this->returnValue(new Tx_Phpunit_TestableCode()));

		$this->assertSame(
			2,
			count($testFinder->getTestableCodeForExtensions())
		);
	}

	/**
	 * @test
	 */
	public function getTestableCodeForExtensionsForOneInstalledExtensionsWithoutTestsReturnsEmptyArray() {
		$testFinder = $this->getMock(
			'Tx_Phpunit_Service_TestFinder',
			array('getLoadedExtensionKeys', 'getExcludedExtensionKeys', 'findTestsPathForExtension')
		);
		$testFinder->expects($this->once())->method('getLoadedExtensionKeys')->will($this->returnValue(array('foo')));
		$testFinder->expects($this->once())->method('getExcludedExtensionKeys')->will($this->returnValue(array()));
		$testFinder->expects($this->once())->method('findTestsPathForExtension')
			->with('foo')->will($this->throwException(new Tx_Phpunit_Exception_NoTestsDirectory()));

		$this->assertSame(
			array(),
			$testFinder->getTestableCodeForExtensions()
		);
	}

	/**
	 * @test
	 */
	public function getTestableCodeForExtensionsForOneExtensionsWithoutTestsAndOneWithTestsReturnsFirstExtension() {
		$testableCodeInstance = new Tx_Phpunit_TestableCode();

		$testFinder = $this->getMock(
			'Tx_Phpunit_Service_TestFinder',
			array('getLoadedExtensionKeys', 'getExcludedExtensionKeys', 'findTestsPathForExtension', 'createTestableCodeForSingleExtension')
		);
		$testFinder->expects($this->once())->method('getLoadedExtensionKeys')->will($this->returnValue(array('foo', 'bar')));
		$testFinder->expects($this->once())->method('getExcludedExtensionKeys')->will($this->returnValue(array()));
		$testFinder->expects($this->at(2))->method('createTestableCodeForSingleExtension')
			->with('foo')->will($this->throwException(new Tx_Phpunit_Exception_NoTestsDirectory()));
		$testFinder->expects($this->at(3))->method('createTestableCodeForSingleExtension')
			->with('bar')->will($this->returnValue($testableCodeInstance));

		$this->assertSame(
			array('bar' => $testableCodeInstance),
			$testFinder->getTestableCodeForExtensions()
		);
	}

	/**
	 * @test
	 */
	public function getTestableCodeForExtensionsProvidesTestableCodeInstanceWithExtensionType() {
		$testFinder = $this->getMock(
			'Tx_Phpunit_Service_TestFinder',
			array('getLoadedExtensionKeys', 'getExcludedExtensionKeys', 'findTestsPathForExtension', 'retrieveExtensionTitle')
		);
		$testFinder->expects($this->once())->method('getLoadedExtensionKeys')->will($this->returnValue(array('phpunit')));
		$testFinder->expects($this->once())->method('getExcludedExtensionKeys')->will($this->returnValue(array()));
		$testFinder->expects($this->once())->method('findTestsPathForExtension')
			->with('phpunit')->will($this->returnValue(t3lib_extMgm::extPath('phpunit') . 'Tests/'));

		$this->assertSame(
			Tx_Phpunit_TestableCode::TYPE_EXTENSION,
			array_pop($testFinder->getTestableCodeForExtensions())->getType()
		);
	}

	/**
	 * @test
	 */
	public function getTestableCodeForExtensionsProvidesTestableCodeInstanceWithExtensionKey() {
		$testFinder = $this->getMock(
			'Tx_Phpunit_Service_TestFinder',
			array('getLoadedExtensionKeys', 'getExcludedExtensionKeys', 'findTestsPathForExtension', 'retrieveExtensionTitle')
		);
		$testFinder->expects($this->once())->method('getLoadedExtensionKeys')->will($this->returnValue(array('phpunit')));
		$testFinder->expects($this->once())->method('getExcludedExtensionKeys')->will($this->returnValue(array()));
		$testFinder->expects($this->once())->method('findTestsPathForExtension')
			->with('phpunit')->will($this->returnValue(t3lib_extMgm::extPath('phpunit') . 'Tests/'));

		$this->assertSame(
			'phpunit',
			array_pop($testFinder->getTestableCodeForExtensions())->getKey()
		);
	}

	/**
	 * @test
	 */
	public function getTestableCodeForExtensionsProvidesTestableCodeInstanceWithExtensionTitle() {
		$testFinder = $this->getMock(
			'Tx_Phpunit_Service_TestFinder',
			array('getLoadedExtensionKeys', 'getExcludedExtensionKeys', 'findTestsPathForExtension', 'retrieveExtensionTitle')
		);
		$testFinder->expects($this->once())->method('getLoadedExtensionKeys')->will($this->returnValue(array('phpunit')));
		$testFinder->expects($this->once())->method('getExcludedExtensionKeys')->will($this->returnValue(array()));
		$testFinder->expects($this->once())->method('findTestsPathForExtension')
			->with('phpunit')->will($this->returnValue(t3lib_extMgm::extPath('phpunit') . 'Tests/'));
		$testFinder->expects($this->once())->method('retrieveExtensionTitle')
			->with('phpunit')->will($this->returnValue('PHPUnit'));

		$this->assertSame(
			'PHPUnit',
			array_pop($testFinder->getTestableCodeForExtensions())->getTitle()
		);
	}

	/**
	 * @test
	 */
	public function getTestableCodeForExtensionsProvidesTestableCodeInstanceWithCodePath() {
		$testFinder = $this->getMock(
			'Tx_Phpunit_Service_TestFinder',
			array('getLoadedExtensionKeys', 'getExcludedExtensionKeys', 'findTestsPathForExtension', 'retrieveExtensionTitle')
		);
		$testFinder->expects($this->once())->method('getLoadedExtensionKeys')->will($this->returnValue(array('phpunit')));
		$testFinder->expects($this->once())->method('getExcludedExtensionKeys')->will($this->returnValue(array()));
		$testFinder->expects($this->once())->method('findTestsPathForExtension')
			->with('phpunit')->will($this->returnValue(t3lib_extMgm::extPath('phpunit') . 'Tests/'));

		$this->assertSame(
			t3lib_extMgm::extPath('phpunit'),
			array_pop($testFinder->getTestableCodeForExtensions())->getCodePath()
		);
	}

	/**
	 * @test
	 */
	public function getTestableCodeForExtensionsProvidesTestableCodeInstanceWithTestsPath() {
		$testFinder = $this->getMock(
			'Tx_Phpunit_Service_TestFinder',
			array('getLoadedExtensionKeys', 'getExcludedExtensionKeys', 'findTestsPathForExtension', 'retrieveExtensionTitle')
		);
		$testFinder->expects($this->once())->method('getLoadedExtensionKeys')->will($this->returnValue(array('phpunit')));
		$testFinder->expects($this->once())->method('getExcludedExtensionKeys')->will($this->returnValue(array()));
		$testFinder->expects($this->once())->method('findTestsPathForExtension')
			->with('phpunit')->will($this->returnValue(t3lib_extMgm::extPath('phpunit') . 'Tests/'));

		$this->assertSame(
			t3lib_extMgm::extPath('phpunit') . 'Tests/',
			array_pop($testFinder->getTestableCodeForExtensions())->getTestsPath()
		);
	}

	/**
	 * @test
	 */
	public function getTestableCodeForExtensionsProvidesTestableCodeInstanceWithIconPath() {
		$testFinder = $this->getMock(
			'Tx_Phpunit_Service_TestFinder',
			array('getLoadedExtensionKeys', 'getExcludedExtensionKeys', 'findTestsPathForExtension', 'retrieveExtensionTitle')
		);
		$testFinder->expects($this->once())->method('getLoadedExtensionKeys')->will($this->returnValue(array('phpunit')));
		$testFinder->expects($this->once())->method('getExcludedExtensionKeys')->will($this->returnValue(array()));
		$testFinder->expects($this->once())->method('findTestsPathForExtension')
			->with('phpunit')->will($this->returnValue(t3lib_extMgm::extPath('phpunit') . 'Tests/'));

		$this->assertSame(
			t3lib_extMgm::extRelPath('phpunit') . 'ext_icon.gif',
			array_pop($testFinder->getTestableCodeForExtensions())->getIconPath()
		);
	}

	/**
	 * @test
	 */
	public function retrieveExtensionTitleReturnsTitleOfInstalledExtension() {
		$this->assertSame(
			'PHPUnit',
			$this->fixture->retrieveExtensionTitle('phpunit')
		);
	}
}
?>