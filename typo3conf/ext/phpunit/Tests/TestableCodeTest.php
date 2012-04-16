<?php
/***************************************************************
* Copyright notice
*
* (c) 2011 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Testcase for the Tx_Phpunit_TestableCode class.
 *
 * @package TYPO3
 * @subpackage tx_
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Phpunit_TestableCodeTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Phpunit_TestableCode
	 */
	private $fixture;

	public function setUp() {
		$this->fixture = new Tx_Phpunit_TestableCode();
	}

	public function tearDown() {
		unset($this->fixture);
	}

	/**
	 * @test
	 */
	public function getKeyInitiallyReturnsEmptyString() {
		$this->assertSame(
			'',
			$this->fixture->getKey()
		);
	}

	/**
	 * @test
	 */
	public function setKeySetsKey() {
		$this->fixture->setKey('foo');

		$this->assertSame(
			'foo',
			$this->fixture->getKey()
		);
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function setKeyWithEmptyStringThrowsException() {
		$this->fixture->setKey('');
	}

	/**
	 * @test
	 */
	public function getTitleInitiallyReturnsEmptyString() {
		$this->assertSame(
			'',
			$this->fixture->getTitle()
		);
	}

	/**
	 * @test
	 */
	public function setTitleSetsTitle() {
		$this->fixture->setTitle('White Russian');

		$this->assertSame(
			'White Russian',
			$this->fixture->getTitle()
		);
	}

	/**
	 * @test
	 */
	public function setTitleCanSetTitleToEmptyString() {
		$this->fixture->setTitle('');

		$this->assertSame(
			'',
			$this->fixture->getTitle()
		);
	}

	/**
	 * @test
	 */
	public function getTypeInitiallyReturnsUndefined() {
		$this->assertSame(
			Tx_Phpunit_TestableCode::TYPE_UNDEFINED,
			$this->fixture->getType()
		);
	}

	/**
	 * @test
	 */
	public function setTypeCanSetTypeToExtension() {
		$this->fixture->setType(Tx_Phpunit_TestableCode::TYPE_EXTENSION);

		$this->assertSame(
			Tx_Phpunit_TestableCode::TYPE_EXTENSION,
			$this->fixture->getType()
		);
	}

	/**
	 * @test
	 */
	public function setTypeCanSetTypeToCore() {
		$this->fixture->setType(Tx_Phpunit_TestableCode::TYPE_CORE);

		$this->assertSame(
			Tx_Phpunit_TestableCode::TYPE_CORE,
			$this->fixture->getType()
		);
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function setTypeForUndefinedTypeThrowsException() {
		$this->fixture->setType(Tx_Phpunit_TestableCode::TYPE_UNDEFINED);
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function setTypeForInvalidTypeThrowsException() {
		$this->fixture->setType(-1);
	}

	/**
	 * @test
	 */
	public function getCodePathInitiallyReturnsEmptyString() {
		$this->assertSame(
			'',
			$this->fixture->getCodePath()
		);
	}

	/**
	 * @test
	 */
	public function setCodePathSetsCodePath() {
		$path = t3lib_extMgm::extPath('phpunit');
		$this->fixture->setCodePath($path);

		$this->assertSame(
			$path,
			$this->fixture->getCodePath()
		);
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function setCodePathWithEmptyStringThrowsException() {
		$this->fixture->setCodePath('');
	}

	/**
	 * @test
	 */
	public function getTestsPathInitiallyReturnsEmptyString() {
		$this->assertSame(
			'',
			$this->fixture->getTestsPath()
		);
	}

	/**
	 * @test
	 */
	public function setTestsPathSetsTestsPath() {
		$path = t3lib_extMgm::extPath('phpunit');
		$this->fixture->setTestsPath($path);

		$this->assertSame(
			$path,
			$this->fixture->getTestsPath()
		);
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function setTestsPathWithEmptyStringThrowsException() {
		$this->fixture->setTestsPath('');
	}

	/**
	 * @test
	 */
	public function getBlacklistInitiallyReturnsEmptyArray() {
		$this->assertSame(
			array(),
			$this->fixture->getBlacklist()
		);
	}

	/**
	 * @test
	 */
	public function setBlacklistSetsBlacklist() {
		$fileNames = array('one file', 'another file');
		$this->fixture->setBlacklist($fileNames);

		$this->assertSame(
			$fileNames,
			$this->fixture->getBlacklist()
		);
	}

	/**
	 * @test
	 */
	public function setBlacklistCanSetEmptyBlacklist() {
		$this->fixture->setBlacklist(array());

		$this->assertSame(
			array(),
			$this->fixture->getBlacklist()
		);
	}

	/**
	 * @test
	 */
	public function getWhitelistInitiallyReturnsEmptyArray() {
		$this->assertSame(
			array(),
			$this->fixture->getWhitelist()
		);
	}

	/**
	 * @test
	 */
	public function setWhitelistSetsWhitelist() {
		$fileNames = array('one file', 'another file');
		$this->fixture->setWhitelist($fileNames);

		$this->assertSame(
			$fileNames,
			$this->fixture->getWhitelist()
		);
	}

	/**
	 * @test
	 */
	public function setWhitelistCanSetEmptyWhitelist() {
		$this->fixture->setWhitelist(array());

		$this->assertSame(
			array(),
			$this->fixture->getWhitelist()
		);
	}

	/**
	 * @test
	 */
	public function getIconPathInitiallyReturnsEmptyString() {
		$this->assertSame(
			'',
			$this->fixture->getIconPath()
		);
	}

	/**
	 * @test
	 */
	public function setIconPathSetsIconPath() {
		$this->fixture->setIconPath('someIcon.gif');

		$this->assertSame(
			'someIcon.gif',
			$this->fixture->getIconPath()
		);
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function setIconPathWithEmptyStringThrowsException() {
		$this->fixture->setIconPath('');
	}
}
?>