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
 * This is another fixture testcase used for testing data providers.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Phpunit_BackEnd_Fixtures_AnotherDataProviderTest extends Tx_Phpunit_TestCase {
	/**
	 * @test
	 */
	public function test1() {
	}

	/**
	 * @test
	 */
	public function test2() {
	}

	/**
	 * Data provider that just returns three empty arrays.
	 *
	 * @see dataProviderTest
	 *
	 * @return array<array>
	 */
	public function dataProvider() {
		return array(
			'some data' => array(),
			'more data' => array(),
			'and even more data' => array()
		);
	}

	/**
	 * @test
	 *
	 * @dataProvider dataProvider
	 */
	public function dataProviderTest() {
	}

	/**
	 * @test
	 */
	public function test3() {
	}

	/**
	 * @test
	 */
	public function test4() {
	}
}
?>