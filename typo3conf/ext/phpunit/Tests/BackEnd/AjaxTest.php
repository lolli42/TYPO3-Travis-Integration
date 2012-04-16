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

require_once(PATH_site . 'typo3/classes/class.typo3ajax.php');

/**
 * Testcase for the Tx_Phpunit_BackEnd_Ajax class.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Phpunit_BackEnd_AjaxTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Phpunit_BackEnd_Ajax
	 */
	private $fixture = NULL;

	/**
	 * backup of $GLOBALS['BE_USER']
	 *
	 * @var t3lib_beUserAuth
	 */
	private $backEndUserBackup = NULL;

	/**
	 * backup of $_POST
	 *
	 * @var array
	 */
	private $postBackup = array();

	public function setUp() {
		$this->postBackup = $_POST;
		$this->backEndUserBackup = $GLOBALS['BE_USER'];

		$_POST = array();
		$GLOBALS['BE_USER'] = $this->getMock('t3lib_beUserAuth');

		$this->fixture = new Tx_Phpunit_BackEnd_Ajax();
	}

	public function tearDown() {
		$GLOBALS['BE_USER'] = $this->backEndUserBackup;
		$_POST = $this->postBackup;

		unset($this->fixture, $this->backEndUserBackup, $this->postBackup);
	}

	/**
	 * @test
	 */
	public function ajaxBrokerForFailureCheckboxParameterAndStateTrueSavesOnStateToUserSettings() {
		$_POST['checkbox'] = 'failure';
		$_POST['state'] = 'true';

		$GLOBALS['BE_USER']->expects($this->once())->method('writeUC');

		$this->fixture->ajaxBroker(array(), $this->getMock('TYPO3AJAX'));

		$this->assertSame(
			'on',
			$GLOBALS['BE_USER']->uc['moduleData']['tools_txphpunitM1']['failure']
		);
	}

	/**
	 * @test
	 */
	public function ajaxBrokerForFailureCheckboxParameterAndMissingStateSavesOffStateToUserSettings() {
		$_POST['checkbox'] = 'failure';

		$GLOBALS['BE_USER']->expects($this->once())->method('writeUC');

		$this->fixture->ajaxBroker(array(), $this->getMock('TYPO3AJAX'));

		$this->assertSame(
			'off',
			$GLOBALS['BE_USER']->uc['moduleData']['tools_txphpunitM1']['failure']
		);
	}

	/**
	 * @test
	 */
	public function ajaxBrokerForFailureCheckboxParameterAddsSuccessContent() {
		$_POST['checkbox'] = 'failure';

		$ajax = $this->getMock('TYPO3AJAX');
		$ajax->expects($this->once())->method('addContent')->with('success', TRUE);
		$ajax->expects($this->never())->method('setError');

		$this->fixture->ajaxBroker(array(), $ajax);
	}

	/**
	 * @test
	 */
	public function ajaxBrokerForSuccessCheckboxParameterAddsSuccessContent() {
		$_POST['checkbox'] = 'success';

		$ajax = $this->getMock('TYPO3AJAX');
		$ajax->expects($this->once())->method('addContent')->with('success', TRUE);
		$ajax->expects($this->never())->method('setError');

		$this->fixture->ajaxBroker(array(), $ajax);
	}

	/**
	 * @test
	 */
	public function ajaxBrokerForErrorCheckboxParameterAddsSuccessContent() {
		$_POST['checkbox'] = 'error';

		$ajax = $this->getMock('TYPO3AJAX');
		$ajax->expects($this->once())->method('addContent')->with('success', TRUE);
		$ajax->expects($this->never())->method('setError');

		$this->fixture->ajaxBroker(array(), $ajax);
	}

	/**
	 * @test
	 */
	public function ajaxBrokerForSkippedCheckboxParameterAddsSuccessContent() {
		$_POST['checkbox'] = 'skipped';

		$ajax = $this->getMock('TYPO3AJAX');
		$ajax->expects($this->once())->method('addContent')->with('success', TRUE);
		$ajax->expects($this->never())->method('setError');

		$this->fixture->ajaxBroker(array(), $ajax);
	}

	/**
	 * @test
	 */
	public function ajaxBrokerForNotImplementedCheckboxParameterAddsSuccessContent() {
		$_POST['checkbox'] = 'notimplemented';

		$ajax = $this->getMock('TYPO3AJAX');
		$ajax->expects($this->once())->method('addContent')->with('success', TRUE);
		$ajax->expects($this->never())->method('setError');

		$this->fixture->ajaxBroker(array(), $ajax);
	}

	/**
	 * @test
	 */
	public function ajaxBrokerForTestDoxCheckboxParameterAddsSuccessContent() {
		$_POST['checkbox'] = 'testdox';

		$ajax = $this->getMock('TYPO3AJAX');
		$ajax->expects($this->once())->method('addContent')->with('success', TRUE);
		$ajax->expects($this->never())->method('setError');

		$this->fixture->ajaxBroker(array(), $ajax);
	}

	/**
	 * @test
	 */
	public function ajaxBrokerForCodeCoverageCheckboxParameterAddsSuccessContent() {
		$_POST['checkbox'] = 'codeCoverage';

		$ajax = $this->getMock('TYPO3AJAX');
		$ajax->expects($this->once())->method('addContent')->with('success', TRUE);
		$ajax->expects($this->never())->method('setError');

		$this->fixture->ajaxBroker(array(), $ajax);
	}

	/**
	 * @test
	 */
	public function ajaxBrokerForShowMemoryAndTimeCheckboxParameterAddsSuccessContent() {
		$_POST['checkbox'] = 'showMemoryAndTime';

		$ajax = $this->getMock('TYPO3AJAX');
		$ajax->expects($this->once())->method('addContent')->with('success', TRUE);
		$ajax->expects($this->never())->method('setError');

		$this->fixture->ajaxBroker(array(), $ajax);
	}

	/**
	 * @test
	 */
	public function ajaxBrokerForRunSeleniumTestsCheckboxParameterAddsSuccessContent() {
		$_POST['checkbox'] = 'runSeleniumTests';

		$ajax = $this->getMock('TYPO3AJAX');
		$ajax->expects($this->once())->method('addContent')->with('success', TRUE);
		$ajax->expects($this->never())->method('setError');

		$this->fixture->ajaxBroker(array(), $ajax);
	}

	/**
	 * @test
	 */
	public function ajaxBrokerForMissingCheckboxParameterSetsError() {
		$ajax = $this->getMock('TYPO3AJAX');
		$ajax->expects($this->once())->method('setError');

		$this->fixture->ajaxBroker(array(), $ajax);
	}

	/**
	 * @test
	 */
	public function ajaxBrokerForInvalidCheckboxParameterSetsError() {
		$_POST['checkbox'] = 'anything else';

		$ajax = $this->getMock('TYPO3AJAX');
		$ajax->expects($this->once())->method('setError');

		$this->fixture->ajaxBroker(array(), $ajax);
	}
}
?>