<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2007-2011 Kasper Ligaard (kasperligaard@gmail.com)
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
 * Test case for the Tx_Phpunit_TestCase class.
 *
 * @author Kasper Ligaard <kasperligaard@gmail.com>
 */
class Tx_Phpunit_TestCaseTest extends Tx_Phpunit_TestCase {
	/**
	 * @test
	 */
	public function newArrayIsEmpty() {
		$fixture = array();

		$this->assertEquals(0, sizeof($fixture));
	}

	/**
	 * @test
	 */
	public function thisCaseIsMarkedAsSkipped() {
		$this->markTestSkipped('This test is skipped while testing.');
	}

	/**
	 * @test
	 */
	public function thisCaseIsMarkedAsNotImplemented() {
		$this->markTestIncomplete('This test as incomplete while not implemented for testing.');
	}
}
?>