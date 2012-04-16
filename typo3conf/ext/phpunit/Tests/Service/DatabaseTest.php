<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2008-2011 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Testcase for the Tx_Phpunit_Service_Database class in the "phpunit" extension.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Phpunit_Service_DatabaseTest extends tx_phpunit_testcase {
	/**
	 * @var Tx_Phpunit_Framework
	 */
	private $testingFramework;

	public function setUp() {
		$this->testingFramework = new Tx_Phpunit_Framework('tx_phpunit');
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		unset($this->testingFramework);
	}


	//////////////////////
	// Utility functions
	//////////////////////

	/**
	 * Explodes a comma-separated list of integer values and sorts them
	 * numerically.
	 *
	 * @param string comma-separated list of values, may be empty
	 *
	 * @return array the separate values, sorted numerically, may be empty
	 */
	private function sortExplode($valueList) {
		if ($valueList == '') {
			return array();
		}

		$numbers = t3lib_div::intExplode(',', $valueList);
		sort($numbers, SORT_NUMERIC);

		return ($numbers);
	}


	////////////////////////////////////
	// Tests for the utility functions
	////////////////////////////////////

	/**
     * @test
     */
    public function sortExplodeWithEmptyStringReturnsEmptyArray() {
		$this->assertEquals(
			array(),
			$this->sortExplode('')
		);
	}

	/**
     * @test
     */
    public function sortExplodeWithOneNumberReturnsArrayWithNumber() {
		$this->assertEquals(
			array(42),
			$this->sortExplode('42')
		);
	}

	/**
     * @test
     */
    public function sortExplodeWithTwoAscendingNumbersReturnsArrayWithBothNumbers() {
		$this->assertEquals(
			array(1, 2),
			$this->sortExplode('1,2')
		);
	}

	/**
     * @test
     */
    public function sortExplodeWithTwoDescendingNumbersReturnsSortedArrayWithBothNumbers() {
		$this->assertEquals(
			array(1, 2),
			$this->sortExplode('2,1')
		);
	}


	//////////////////////////////////
	// Tests for enableFields
	//////////////////////////////////

	/**
     * @test
     */
    public function enableFieldsThrowsExceptionForTooSmallShowHidden() {
		$this->setExpectedException('Exception', '$showHidden may only be -1, 0 or 1, but actually is -2');
		Tx_Phpunit_Service_Database::enableFields('tx_phpunit_test', -2);
	}

	/**
     * @test
     */
    public function enableFieldsThrowsExceptionForTooBigShowHidden() {
		$this->setExpectedException('Exception', '$showHidden may only be -1, 0 or 1, but actually is 2');
		Tx_Phpunit_Service_Database::enableFields('tx_phpunit_test', 2);
	}

	/**
     * @test
     */
    public function enableFieldsIsDifferentForDifferentTables() {
		$this->assertNotEquals(
			Tx_Phpunit_Service_Database::enableFields('tx_phpunit_test'),
			Tx_Phpunit_Service_Database::enableFields('pages')
		);
	}

	/**
     * @test
     */
    public function enableFieldsCanBeDifferentForShowHiddenZeroAndOne() {
		$this->assertNotEquals(
			Tx_Phpunit_Service_Database::enableFields('tx_phpunit_test', 0),
			Tx_Phpunit_Service_Database::enableFields('tx_phpunit_test', 1)
		);
	}

	/**
     * @test
     */
    public function enableFieldsAreTheSameForShowHiddenZeroAndMinusOne() {
		$this->assertEquals(
			Tx_Phpunit_Service_Database::enableFields('tx_phpunit_test', 0),
			Tx_Phpunit_Service_Database::enableFields('tx_phpunit_test', -1)
		);
	}

	/**
     * @test
     */
    public function enableFieldsCanBeDifferentForShowHiddenOneAndMinusOne() {
		$this->assertNotEquals(
			Tx_Phpunit_Service_Database::enableFields('tx_phpunit_test', 1),
			Tx_Phpunit_Service_Database::enableFields('tx_phpunit_test', -1)
		);
	}

	/**
     * @test
     */
    public function enableFieldsCanBeDifferentForDifferentIgnores() {
		$this->assertNotEquals(
			Tx_Phpunit_Service_Database::enableFields('tx_phpunit_test', 0, array()),
			Tx_Phpunit_Service_Database::enableFields(
				'tx_phpunit_test', 0, array('endtime' => TRUE)
			)
		);
	}

	/**
	 * TODO: This test does not work until the full versioning feature is
	 * implemented in the testing framework.
	 *
	 * @see https://bugs.oliverklee.com/show_bug.cgi?id=2180
	 */
	/**
     * @test
     */
    public function enableFieldsCanBeDifferentForDifferentVersionParameters() {
		$this->markTestSkipped(
			'This test does not work until the full versioning feature is ' .
				'implemented in the testing framework.'
		);
		Tx_Phpunit_Service_Database::enableVersioningPreviewForCachedPage();

		$this->assertNotEquals(
			Tx_Phpunit_Service_Database::enableFields(
				'tx_phpunit_test', 0, array(), FALSE
			),
			Tx_Phpunit_Service_Database::enableFields(
				'tx_phpunit_test', 0, array(), TRUE
			)
		);
	}


	/////////////////////////////////////////////
	// Tests concerning createRecursivePageList
	/////////////////////////////////////////////

	/**
     * @test
     */
    public function createRecursivePageListReturnsAnEmptyStringForNoPagesWithDefaultRecursion() {
		$this->assertEquals(
			'',
			Tx_Phpunit_Service_Database::createRecursivePageList('')
		);
	}

	/**
     * @test
     */
    public function createRecursivePageListReturnsAnEmptyStringForNoPagesWithZeroRecursion() {
		$this->assertEquals(
			'',
			Tx_Phpunit_Service_Database::createRecursivePageList('', 0)
		);
	}

	/**
     * @test
     */
    public function createRecursivePageListReturnsAnEmptyStringForNoPagesWithNonZeroRecursion() {
		$this->assertEquals(
			'',
			Tx_Phpunit_Service_Database::createRecursivePageList('', 1)
		);
	}

	/**
     * @test
     */
    public function createRecursivePageListThrowsWithNegativeRecursion() {
		$this->setExpectedException('Exception', '$recursionDepth must be >= 0.');

		Tx_Phpunit_Service_Database::createRecursivePageList('', -1);
	}

	/**
     * @test
     */
    public function createRecursivePageListDoesNotContainSubpagesForOnePageWithZeroRecursion() {
		$uid = $this->testingFramework->createSystemFolder();
		$this->testingFramework->createSystemFolder($uid);

		$this->assertEquals(
			(string) $uid,
			Tx_Phpunit_Service_Database::createRecursivePageList((string) $uid, 0)
		);
	}

	/**
     * @test
     */
    public function createRecursivePageListDoesNotContainSubpagesForTwoPagesWithZeroRecursion() {
		$uid1 = $this->testingFramework->createSystemFolder();
		$this->testingFramework->createSystemFolder($uid1);
		$uid2 = $this->testingFramework->createSystemFolder();

		$this->assertEquals(
			$this->sortExplode($uid1 . ',' . $uid2),
			$this->sortExplode(
				Tx_Phpunit_Service_Database::createRecursivePageList($uid1.','.$uid2, 0)
			)
		);
	}

	/**
     * @test
     */
    public function createRecursivePageListDoesNotContainSubsubpagesForRecursionOfOne() {
		$uid = $this->testingFramework->createSystemFolder();
		$subFolderUid = $this->testingFramework->createSystemFolder($uid);
		$this->testingFramework->createSystemFolder($subFolderUid);

		$this->assertEquals(
			$this->sortExplode($uid.','.$subFolderUid),
			$this->sortExplode(Tx_Phpunit_Service_Database::createRecursivePageList($uid, 1))
		);
	}

	/**
     * @test
     */
    public function createRecursivePageListDoesNotContainUnrelatedPages() {
		$uid = $this->testingFramework->createSystemFolder();
		$this->testingFramework->createSystemFolder();

		$this->assertEquals(
			(string) $uid,
			Tx_Phpunit_Service_Database::createRecursivePageList($uid, 0)
		);
	}

	/**
     * @test
     */
    public function createRecursivePageListCanContainTwoSubpagesOfOnePage() {
		$uid = $this->testingFramework->createSystemFolder();
		$subFolderUid1 = $this->testingFramework->createSystemFolder($uid);
		$subFolderUid2 = $this->testingFramework->createSystemFolder($uid);

		$this->assertEquals(
			$this->sortExplode($uid.','.$subFolderUid1.','.$subFolderUid2),
			$this->sortExplode(Tx_Phpunit_Service_Database::createRecursivePageList($uid, 1))
		);
	}

	/**
     * @test
     */
    public function createRecursivePageListCanContainSubpagesOfTwoPages() {
		$uid1 = $this->testingFramework->createSystemFolder();
		$uid2 = $this->testingFramework->createSystemFolder();
		$subFolderUid1 = $this->testingFramework->createSystemFolder($uid1);
		$subFolderUid2 = $this->testingFramework->createSystemFolder($uid2);

		$this->assertEquals(
			$this->sortExplode(
				$uid1.','.$uid2.','.$subFolderUid1.','.$subFolderUid2
			),
			$this->sortExplode(
				Tx_Phpunit_Service_Database::createRecursivePageList($uid1.','.$uid2, 1)
			)
		);
	}

	/**
     * @test
     */
    public function createRecursivePageListHeedsIncreasingRecursionDepthOnSubsequentCalls() {
		$uid = $this->testingFramework->createSystemFolder();
		$subFolderUid = $this->testingFramework->createSystemFolder($uid);

		$this->assertEquals(
			(string) $uid,
			Tx_Phpunit_Service_Database::createRecursivePageList($uid, 0)
		);
		$this->assertEquals(
			$this->sortExplode($uid.','.$subFolderUid),
			$this->sortExplode(Tx_Phpunit_Service_Database::createRecursivePageList($uid, 1))
		);
	}

	/**
     * @test
     */
    public function createRecursivePageListHeedsDecreasingRecursionDepthOnSubsequentCalls() {
		$uid = $this->testingFramework->createSystemFolder();
		$subFolderUid = $this->testingFramework->createSystemFolder($uid);

		$this->assertEquals(
			$this->sortExplode($uid.','.$subFolderUid),
			$this->sortExplode(Tx_Phpunit_Service_Database::createRecursivePageList($uid, 1))
		);
		$this->assertEquals(
			(string) $uid,
			Tx_Phpunit_Service_Database::createRecursivePageList($uid, 0)
		);
	}


	///////////////////////////////////////
	// Tests concerning getColumnsInTable
	///////////////////////////////////////

	/**
     * @test
     */
    public function getColumnsInTableForEmptyTableNameThrowsException() {
		$this->setExpectedException(
			'Exception', 'The table name must not be empty.'
		);

		Tx_Phpunit_Service_Database::getColumnsInTable('');
	}

	/**
	 * @test
	 */
	public function getColumnsInTableForInexistentTableNameThrowsException() {
		$this->setExpectedException(
			'Exception', 'The table "tx_phpunit_doesnotexist" does not exist.'
		);

		Tx_Phpunit_Service_Database::getColumnsInTable('tx_phpunit_doesnotexist');
	}

	/**
     * @test
     */
    public function getColumnsInTableReturnsArrayThatContainsExistingColumn() {
		$columns = Tx_Phpunit_Service_Database::getColumnsInTable('tx_phpunit_test');

		$this->assertTrue(
			isset($columns['title'])
		);
	}

	/**
     * @test
     */
    public function getColumnsInTableReturnsArrayThatNotContainsInexistentColumn() {
		$columns = Tx_Phpunit_Service_Database::getColumnsInTable('tx_phpunit_test');

		$this->assertFalse(
			isset($columns['does_not_exist'])
		);
	}


	//////////////////////////////////////////
	// Tests concerning getColumnDefinition
	//////////////////////////////////////////

	/**
     * @test
     */
    public function getColumnDefinitionForEmptyTableNameThrowsException() {
		$this->setExpectedException(
			'Exception', 'The table name must not be empty.'
		);

		Tx_Phpunit_Service_Database::getColumnDefinition('', 'uid');
	}

	/**
     * @test
     */
    public function getColumnDefinitionReturnsArrayThatContainsFieldName() {
		$definition = Tx_Phpunit_Service_Database::getColumnDefinition('tx_phpunit_test', 'title');

		$this->assertTrue(
			$definition['Field'] == 'title'
		);
	}


	////////////////////////////////////////
	// Tests regarding tableHasColumnUid()
	////////////////////////////////////////

	/**
     * @test
     */
    public function tableHasColumnUidForEmptyTableNameThrowsException() {
		$this->setExpectedException(
			'Exception', 'The table name must not be empty.'
		);

		Tx_Phpunit_Service_Database::tableHasColumnUid('');
	}

	/**
     * @test
     */
    public function tableHasColumnUidIsTrueOnTableWithColumnUid() {
		$this->assertTrue(
			Tx_Phpunit_Service_Database::tableHasColumnUid('tx_phpunit_test')
		);
	}

	/**
     * @test
     */
    public function tableHasColumnUidIsFalseOnTableWithoutColumnUid() {
		$this->assertFalse(
			Tx_Phpunit_Service_Database::tableHasColumnUid('tx_phpunit_test_article_mm')
		);
	}

	/**
     * @test
     */
    public function tableHasColumnUidCanReturnDifferentResultsForDifferentTables() {
		$this->assertNotEquals(
			Tx_Phpunit_Service_Database::tableHasColumnUid('tx_phpunit_test'),
			Tx_Phpunit_Service_Database::tableHasColumnUid('tx_phpunit_test_article_mm')
		);
	}


	/////////////////////////////////////
	// Tests regarding tableHasColumn()
	/////////////////////////////////////

	/**
     * @test
     */
    public function tableHasColumnReturnsTrueOnTableWithColumn() {
		$this->assertTrue(
			Tx_Phpunit_Service_Database::tableHasColumn(
				'tx_phpunit_test', 'title'
			)
		);
	}

	/**
     * @test
     */
    public function tableHasColumnReturnsFalseOnTableWithoutColumn() {
		$this->assertFalse(
			Tx_Phpunit_Service_Database::tableHasColumn(
				'tx_phpunit_test', 'inexistent_column'
			)
		);
	}

	/**
     * @test
     */
    public function tableHasColumnThrowsExceptionOnEmptyTableName() {
		$this->setExpectedException(
			'Exception', 'The table name must not be empty.'
		);

		Tx_Phpunit_Service_Database::tableHasColumn(
			'', 'title'
		);
	}

	/**
     * @test
     */
    public function tableHasColumnReturnsFalseOnEmptyColumnName() {
		$this->assertFalse(
			Tx_Phpunit_Service_Database::tableHasColumn(
				'tx_phpunit_test', ''
			)
		);
	}


	/////////////////////
	// Tests for delete
	/////////////////////

	/**
     * @test
     */
    public function deleteForEmptyTableNameThrowsException() {
		$this->setExpectedException(
			'Exception', 'The table name must not be empty.'
		);

		Tx_Phpunit_Service_Database::delete(
			'', 'uid = 0'
		);
	}

	/**
     * @test
     */
    public function deleteDeletesRecord() {
		$uid = $this->testingFramework->createRecord('tx_phpunit_test');

		Tx_Phpunit_Service_Database::delete(
			'tx_phpunit_test', 'uid = ' . $uid
		);

		$this->assertFalse(
			$this->testingFramework->existsRecordWithUid(
				'tx_phpunit_test', $uid
			)
		);
	}

	/**
     * @test
     */
    public function deleteForNoDeletedRecordReturnsZero() {
		$this->assertEquals(
			0,
			Tx_Phpunit_Service_Database::delete(
				'tx_phpunit_test', 'uid = 0'
			)
		);
	}

	/**
     * @test
     */
    public function deleteForOneDeletedRecordReturnsOne() {
		$uid = $this->testingFramework->createRecord('tx_phpunit_test');

		$this->assertEquals(
			1,
			Tx_Phpunit_Service_Database::delete(
				'tx_phpunit_test', 'uid = ' . $uid
			)
		);
	}

	/**
     * @test
     */
    public function deleteForTwoDeletedRecordsReturnsTwo() {
		$uid1 = $this->testingFramework->createRecord('tx_phpunit_test');
		$uid2 = $this->testingFramework->createRecord('tx_phpunit_test');

		$this->assertEquals(
			2,
			Tx_Phpunit_Service_Database::delete(
				'tx_phpunit_test',
				'uid IN(' . $uid1 . ',' . $uid2 . ')'
			)
		);
	}


	/////////////////////
	// Tests for update
	/////////////////////

	/**
     * @test
     */
    public function updateForEmptyTableNameThrowsException() {
		$this->setExpectedException(
			'Exception', 'The table name must not be empty.'
		);

		Tx_Phpunit_Service_Database::update(
			'', 'uid = 0', array()
		);
	}

	/**
     * @test
     */
    public function updateChangesRecord() {
		$uid = $this->testingFramework->createRecord('tx_phpunit_test');

		Tx_Phpunit_Service_Database::update(
			'tx_phpunit_test', 'uid = ' . $uid, array('title' => 'foo')
		);

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				'tx_phpunit_test', 'title = "foo"'
			)
		);
	}

	/**
     * @test
     */
    public function updateForNoChangedRecordReturnsZero() {
		$this->assertEquals(
			0,
			Tx_Phpunit_Service_Database::update(
				'tx_phpunit_test', 'uid = 0', array('title' => 'foo')
			)
		);
	}

	/**
     * @test
     */
    public function updateForOneChangedRecordReturnsOne() {
		$uid = $this->testingFramework->createRecord('tx_phpunit_test');

		$this->assertEquals(
			1,
			Tx_Phpunit_Service_Database::update(
				'tx_phpunit_test', 'uid = ' . $uid, array('title' => 'foo')
			)
		);
	}

	/**
     * @test
     */
    public function updateForTwoChangedRecordsReturnsTwo() {
		$uid1 = $this->testingFramework->createRecord('tx_phpunit_test');
		$uid2 = $this->testingFramework->createRecord('tx_phpunit_test');

		$this->assertEquals(
			2,
			Tx_Phpunit_Service_Database::update(
				'tx_phpunit_test',
				'uid IN(' . $uid1 . ',' . $uid2 . ')',
				array('title' => 'foo')
			)
		);
	}


	/////////////////////
	// Tests for insert
	/////////////////////

	/**
     * @test
     */
    public function insertForEmptyTableNameThrowsException() {
		$this->setExpectedException(
			'Exception', 'The table name must not be empty.'
		);

		Tx_Phpunit_Service_Database::insert(
			'', array('is_dummy_record' => 1)
		);
	}

	/**
     * @test
     */
    public function insertForEmptyRecordDataThrowsException() {
		$this->setExpectedException(
			'Exception', '$recordData must not be empty.'
		);

		Tx_Phpunit_Service_Database::insert(
			'tx_phpunit_test', array()
		);
	}

	/**
     * @test
     */
    public function insertInsertsRecord() {
		Tx_Phpunit_Service_Database::insert(
			'tx_phpunit_test', array('title' => 'foo', 'is_dummy_record' => 1)
		);
		$this->testingFramework->markTableAsDirty('tx_phpunit_test');

		$this->assertTrue(
			$this->testingFramework->existsRecord(
				'tx_phpunit_test', 'title = "foo"'
			)
		);
	}

	/**
     * @test
     */
    public function insertForTableWithUidReturnsUidOfCreatedRecord() {
		$uid = Tx_Phpunit_Service_Database::insert(
			'tx_phpunit_test', array('is_dummy_record' => 1)
		);
		$this->testingFramework->markTableAsDirty('tx_phpunit_test');

		$this->assertTrue(
			$this->testingFramework->existsRecordWithUid(
				'tx_phpunit_test', $uid
			)
		);
	}

	/**
     * @test
     */
    public function insertForTableWithoutUidReturnsZero() {
		$this->testingFramework->markTableAsDirty('tx_phpunit_test_article_mm');

		$this->assertEquals(
			0,
			Tx_Phpunit_Service_Database::insert(
				'tx_phpunit_test_article_mm', array('is_dummy_record' => 1)
			)
		);
	}


	//////////////////////////////////////////////////////////
	// Tests concerning select, selectSingle, selectMultiple
	//////////////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function selectForEmptyTableNameThrowsException() {
		$this->setExpectedException(
			'Exception', 'The table names must not be empty.'
		);

		Tx_Phpunit_Service_Database::select('*', '');
	}

	/**
	 * @test
	 */
	public function selectForEmptyFieldListThrowsException() {
		$this->setExpectedException(
			'Exception', '$fields must not be empty.'
		);

		Tx_Phpunit_Service_Database::select('', 'tx_phpunit_test');
	}

	/**
	 * @test
	 */
	public function selectReturnsRessource() {
		$this->assertTrue(
			is_resource(Tx_Phpunit_Service_Database::select('title', 'tx_phpunit_test'))
		);
	}

	/**
	 * @test
	 */
	public function selectSingleForEmptyTableNameThrowsException() {
		$this->setExpectedException(
			'Exception', 'The table names must not be empty.'
		);

		Tx_Phpunit_Service_Database::selectSingle('*', '');
	}

	/**
	 * @test
	 */
	public function selectSingleForEmptyFieldListThrowsException() {
		$this->setExpectedException(
			'Exception', '$fields must not be empty.'
		);

		Tx_Phpunit_Service_Database::selectSingle('', 'tx_phpunit_test');
	}

	/**
	 * @test
	 */
	public function selectSingleCanFindOneRow() {
		$uid = $this->testingFramework->createRecord(
			'tx_phpunit_test'
		);

		$this->assertEquals(
			array('uid' => $uid),
			Tx_Phpunit_Service_Database::selectSingle('uid', 'tx_phpunit_test', 'uid = ' . $uid)
		);
	}

	/**
	 * @test
	 */
	public function selectSingleForNoResultsThrowsEmptyQueryResultException() {
		$this->setExpectedException(
			'Tx_Phpunit_Exception_EmptyQueryResult'
		);

		Tx_Phpunit_Service_Database::selectSingle('uid', 'tx_phpunit_test', 'title = "nothing"');
	}

	/**
	 * @test
	 */
	public function selectSingleCanOrderTheResults() {
		$this->testingFramework->createRecord(
			'tx_phpunit_test', array('title' => 'Title A')
		);
		$uid = $this->testingFramework->createRecord(
			'tx_phpunit_test', array('title' => 'Title B')
		);

		$this->assertEquals(
			array('uid' => $uid),
			Tx_Phpunit_Service_Database::selectSingle('uid', 'tx_phpunit_test', '', '', 'title DESC')
		);
	}

	/**
	 * @test
	 */
	public function selectSingleCanUseOffset() {
		$this->testingFramework->createRecord(
			'tx_phpunit_test', array('title' => 'Title A')
		);
		$uid = $this->testingFramework->createRecord(
			'tx_phpunit_test', array('title' => 'Title B')
		);

		$this->assertEquals(
			array('uid' => $uid),
			Tx_Phpunit_Service_Database::selectSingle('uid', 'tx_phpunit_test', '', '', 'title', 1)
		);
	}


	/**
	 * @test
	 */
	public function selectMultipleForEmptyTableNameThrowsException() {
		$this->setExpectedException(
			'Exception', 'The table names must not be empty.'
		);

		Tx_Phpunit_Service_Database::selectMultiple('*', '');
	}

	/**
	 * @test
	 */
	public function selectMultipleForEmptyFieldListThrowsException() {
		$this->setExpectedException(
			'Exception', '$fields must not be empty.'
		);

		Tx_Phpunit_Service_Database::selectMultiple('', 'tx_phpunit_test');
	}

	/**
	 * @test
	 */
	public function selectMultipleForNoResultsReturnsEmptyArray() {
		$this->assertEquals(
			array(),
			Tx_Phpunit_Service_Database::selectMultiple(
				'uid', 'tx_phpunit_test', 'title = "nothing"'
			)
		);
	}

	/**
	 * @test
	 */
	public function selectMultipleCanFindOneRow() {
		$uid = $this->testingFramework->createRecord(
			'tx_phpunit_test'
		);

		$this->assertEquals(
			array(array('uid' => $uid)),
			Tx_Phpunit_Service_Database::selectMultiple('uid', 'tx_phpunit_test', 'uid = ' . $uid)
		);
	}

	/**
	 * @test
	 */
	public function selectMultipleCanFindTwoRows() {
		$this->testingFramework->createRecord(
			'tx_phpunit_test', array('title' => 'foo')
		);
		$this->testingFramework->createRecord(
			'tx_phpunit_test', array('title' => 'foo')
		);

		$this->assertEquals(
			array(
				array('title' => 'foo'),
				array('title' => 'foo'),
			),
			Tx_Phpunit_Service_Database::selectMultiple(
				'title', 'tx_phpunit_test', 'title = "foo"'
			)
		);
	}

	/**
	 * @test
	 */
	public function selectColumnForMultipleForNoMatchesReturnsEmptyArray() {
		$this->assertEquals(
			array(),
			Tx_Phpunit_Service_Database::selectColumnForMultiple(
				'title', 'tx_phpunit_test', 'title = "nothing"'
			)
		);
	}

	/**
	 * @test
	 */
	public function selectColumnForMultipleForOneMatchReturnsArrayWithColumnContent() {
		$uid = $this->testingFramework->createRecord(
			'tx_phpunit_test', array('title' => 'foo')
		);

		$this->assertEquals(
			array('foo'),
			Tx_Phpunit_Service_Database::selectColumnForMultiple(
				'title', 'tx_phpunit_test', 'uid = ' . $uid
			)
		);
	}

	/**
	 * @test
	 */
	public function selectColumnForMultipleForTwoMatchReturnsArrayWithColumnContents() {
		$uid1 = $this->testingFramework->createRecord(
			'tx_phpunit_test', array('title' => 'foo')
		);
		$uid2 = $this->testingFramework->createRecord(
			'tx_phpunit_test', array('title' => 'bar')
		);

		$result = Tx_Phpunit_Service_Database::selectColumnForMultiple(
			'title', 'tx_phpunit_test', 'uid = ' . $uid1 . ' OR uid = ' . $uid2
		);
		sort($result);
		$this->assertEquals(
			array('bar', 'foo'),
			$result
		);
	}


	//////////////////////////////////////
	// Tests concerning getAllTableNames
	//////////////////////////////////////

	/**
     * @test
     */
    public function getAllTableNamesContainsExistingTable() {
		$this->assertTrue(
			in_array('tx_phpunit_test', Tx_Phpunit_Service_Database::getAllTableNames())
		);
	}

	/**
     * @test
     */
    public function getAllTableNamesNotContainsInexistentTable() {
		$this->assertFalse(
			in_array('tx_phpunit_doesnotexist', Tx_Phpunit_Service_Database::getAllTableNames())
		);
	}


	/////////////////////////////////
	// Tests concerning existsTable
	/////////////////////////////////

	/**
     * @test
     */
    public function existsTableWithEmptyTableNameThrowsException() {
		$this->setExpectedException(
			'Exception', 'The table name must not be empty.'
		);

		Tx_Phpunit_Service_Database::existsTable('');
	}

	/**
     * @test
     */
    public function existsTableForExistingTableReturnsTrue() {
		$this->assertTrue(
			Tx_Phpunit_Service_Database::existsTable('tx_phpunit_test')
		);
	}

	/**
     * @test
     */
    public function existsTableForInexistentTableReturnsFalse() {
		$this->assertFalse(
			Tx_Phpunit_Service_Database::existsTable('tx_phpunit_doesnotexist')
		);
	}


	////////////////////////////////////
	// Tests concerning getTcaForTable
	////////////////////////////////////

	/**
     * @test
     */
    public function getTcaForTableReturnsValidTcaArray() {
		$tca = Tx_Phpunit_Service_Database::getTcaForTable('tx_phpunit_test');

		$this->assertTrue(is_array($tca['ctrl']));
		$this->assertTrue(is_array($tca['interface']));
		$this->assertTrue(is_array($tca['columns']));
		$this->assertTrue(is_array($tca['types']));
		$this->assertTrue(is_array($tca['palettes']));
	}

	/**
	 * @test
	 */
	public function getTcaForTableWithEmptyTableNameThrowsExceptionTca() {
		$this->setExpectedException(
			'Exception', 'The table name must not be empty.'
		);

		Tx_Phpunit_Service_Database::getTcaForTable('');
	}

	/**
	 * @test
	 */
	public function getTcaForTableWithInexistentTableNameThrowsExceptionTca() {
		$this->setExpectedException(
			'Exception', 'The table "tx_phpunit_doesnotexist" does not exist.'
		);

		Tx_Phpunit_Service_Database::getTcaForTable('tx_phpunit_doesnotexist');
	}

	/**
     * @test
     */
    public function getTcaForTableThrowsExceptionOnTableWithoutTca() {
		$this->setExpectedException(
			'Exception', 'The table "' . 'tx_phpunit_test_article_mm' . '" has no TCA.'
		);

		Tx_Phpunit_Service_Database::getTcaForTable('tx_phpunit_test_article_mm');
	}

	/**
	 * @test
	 */
	public function getTcaForTableCanLoadFieldsAddedByExtensions() {
		if (!t3lib_extMgm::isLoaded('sr_feuser_register')) {
			$this->markTestSkipped(
				'This test is only applicable if sr_feuser_register is loaded.'
			);
		}
		$tca = Tx_Phpunit_Service_Database::getTcaForTable('fe_users');

		$this->assertTrue(isset($tca['columns']['gender']));
	}


	///////////////////////////
	// Tests concerning count
	///////////////////////////

	/**
	 * @test
	 */
	public function countCanBeCalledWithEmptyWhereClause() {
		Tx_Phpunit_Service_Database::count('tx_phpunit_test', '');
	}

	/**
	 * @test
	 */
	public function countCanBeCalledWithMissingWhereClause() {
		Tx_Phpunit_Service_Database::count('tx_phpunit_test');
	}

	/**
	 * @test
	 */
	public function countForNoMatchesReturnsZero() {
		$this->assertSame(
			0,
			Tx_Phpunit_Service_Database::count(
				'tx_phpunit_test',
				'uid = 42'
			)
		);
	}

	/**
	 * @test
	 */
	public function countForOneMatchReturnsOne() {
		$this->assertSame(
			1,
			Tx_Phpunit_Service_Database::count(
				'tx_phpunit_test',
				'uid = ' . $this->testingFramework->createRecord('tx_phpunit_test')
			)
		);
	}

	/**
	 * @test
	 */
	public function countForTwoMatchesReturnsTwo() {
		$uid1 = $this->testingFramework->createRecord('tx_phpunit_test');
		$uid2 = $this->testingFramework->createRecord('tx_phpunit_test');

		$this->assertSame(
			2,
			Tx_Phpunit_Service_Database::count(
				'tx_phpunit_test',
				'uid IN(' . $uid1 . ',' . $uid2 . ')'
			)
		);
	}

	/**
	 * @test
	 */
	public function countCanBeCalledForTableWithoutUid() {
		Tx_Phpunit_Service_Database::count('tx_phpunit_test_article_mm');
	}

	/**
	 * @test
	 */
	public function countCanBeCalledWithMultipleTables() {
		Tx_Phpunit_Service_Database::count('tx_phpunit_test, tx_phpunit_testchild');
	}

	/**
	 * @test
	 */
	public function countWithInvalidTableNameThrowsException() {
		$this->setExpectedException(
			'Exception', 'The table "tx_phpunit_doesnotexist" does not exist.'
		);

		Tx_Phpunit_Service_Database::count('tx_phpunit_doesnotexist', 'uid = 42');
	}

	/**
	 * @test
	 */
	public function countCanBeCalledWithJoinedTables() {
		Tx_Phpunit_Service_Database::count('tx_phpunit_test JOIN tx_phpunit_testchild');
	}

	/**
	 * @test
	 */
	public function countDoesNotAllowJoinWithoutTables() {
		$this->setExpectedException(
			'Exception', 'The table "JOIN" does not exist.'
		);

		Tx_Phpunit_Service_Database::count('JOIN');
	}

	/**
	 * @test
	 */
	public function countDoesNotAllowJoinWithOnlyOneTableOnTheLeft() {
		$this->setExpectedException(
			'Exception', 'The table "tx_phpunit_test JOIN " does not exist.'
		);

		Tx_Phpunit_Service_Database::count('tx_phpunit_test JOIN ');
	}

	/**
	 * @test
	 */
	public function countDoesNotAllowJoinWithOnlyOneTableOnTheRight() {
		$this->setExpectedException(
			'Exception', 'The table "JOIN tx_phpunit_test" does not exist.'
		);

		Tx_Phpunit_Service_Database::count('JOIN tx_phpunit_test');
	}


	/////////////////////////////////
	// Tests regarding existsRecord
	/////////////////////////////////

	/**
	 * @test
	 */
	public function existsRecordWithEmptyWhereClauseIsAllowed() {
		Tx_Phpunit_Service_Database::existsRecord('tx_phpunit_test', '');
	}

	/**
	 * @test
	 */
	public function existsRecordWithMissingWhereClauseIsAllowed() {
		Tx_Phpunit_Service_Database::existsRecord('tx_phpunit_test');
	}

	/**
	 * @test
	 */
	public function existsRecordWithEmptyTableNameThrowsException() {
		$this->setExpectedException(
			'Exception',
			'The table name must not be empty.'
		);

		Tx_Phpunit_Service_Database::existsRecord('');
	}

	/**
	 * @test
	 */
	public function existsRecordWithInvalidTableNameThrowsException() {
		$this->setExpectedException(
			'Exception', 'The table "tx_phpunit_doesnotexist" does not exist.'
		);

		Tx_Phpunit_Service_Database::existsRecord('tx_phpunit_doesnotexist');
	}

	/**
	 * @test
	 */
	public function existsRecordForNoMatchesReturnsFalse() {
		$this->assertFalse(
			Tx_Phpunit_Service_Database::existsRecord('tx_phpunit_test', 'uid = 42')
		);
	}

	/**
	 * @test
	 */
	public function existsRecordForOneMatchReturnsTrue() {
		$uid = $this->testingFramework->createRecord(
			'tx_phpunit_test'
		);

		$this->assertTrue(
			Tx_Phpunit_Service_Database::existsRecord('tx_phpunit_test', 'uid = ' . $uid)
		);
	}

	/**
	 * @test
	 */
	public function existsRecordForTwoMatchesReturnsTrue() {
		$this->testingFramework->createRecord(
			'tx_phpunit_test', array('title' => 'foo')
		);
		$this->testingFramework->createRecord(
			'tx_phpunit_test', array('title' => 'foo')
		);

		$this->assertTrue(
			Tx_Phpunit_Service_Database::existsRecord('tx_phpunit_test', 'title = "foo"')
		);
	}


	///////////////////////////////////////////
	// Tests regarding existsExactlyOneRecord
	///////////////////////////////////////////

	/**
	 * @test
	 */
	public function existsExactlyOneRecordWithEmptyWhereClauseIsAllowed() {
		Tx_Phpunit_Service_Database::existsExactlyOneRecord('tx_phpunit_test', '');
	}

	/**
	 * @test
	 */
	public function existsExactlyOneRecordWithMissingWhereClauseIsAllowed() {
		Tx_Phpunit_Service_Database::existsExactlyOneRecord('tx_phpunit_test');
	}

	/**
	 * @test
	 */
	public function existsExactlyOneRecordWithEmptyTableNameThrowsException() {
		$this->setExpectedException(
			'Exception',
			'The table name must not be empty.'
		);

		Tx_Phpunit_Service_Database::existsExactlyOneRecord('');
	}

	/**
	 * @test
	 */
	public function existsExactlyOneRecordWithInvalidTableNameThrowsException() {
		$this->setExpectedException(
			'Exception', 'The table "tx_phpunit_doesnotexist" does not exist.'
		);

		Tx_Phpunit_Service_Database::existsExactlyOneRecord('tx_phpunit_doesnotexist');
	}

	/**
	 * @test
	 */
	public function existsExactlyOneRecordForNoMatchesReturnsFalse() {
		$this->assertFalse(
			Tx_Phpunit_Service_Database::existsExactlyOneRecord('tx_phpunit_test', 'uid = 42')
		);
	}

	/**
	 * @test
	 */
	public function existsExactlyOneRecordForOneMatchReturnsTrue() {
		$uid = $this->testingFramework->createRecord(
			'tx_phpunit_test'
		);

		$this->assertTrue(
			Tx_Phpunit_Service_Database::existsExactlyOneRecord('tx_phpunit_test', 'uid = ' . $uid)
		);
	}

	/**
	 * @test
	 */
	public function existsExactlyOneRecordForTwoMatchesReturnsFalse() {
		$this->testingFramework->createRecord(
			'tx_phpunit_test', array('title' => 'foo')
		);
		$this->testingFramework->createRecord(
			'tx_phpunit_test', array('title' => 'foo')
		);

		$this->assertFalse(
			Tx_Phpunit_Service_Database::existsExactlyOneRecord('tx_phpunit_test', 'title = "foo"')
		);
	}


	////////////////////////////////////////
	// Tests regarding existsRecordWithUid
	////////////////////////////////////////

	/**
	 * @test
	 */
	public function existsRecordWithUidWithZeroUidThrowsException() {
		$this->setExpectedException(
			'Exception', '$uid must be > 0.'
		);

		Tx_Phpunit_Service_Database::existsRecordWithUid('tx_phpunit_test', 0);
	}

	/**
	 * @test
	 */
	public function existsRecordWithUidWithNegativeUidThrowsException() {
		$this->setExpectedException(
			'Exception', '$uid must be > 0.'
		);

		Tx_Phpunit_Service_Database::existsRecordWithUid('tx_phpunit_test', -1);
	}

	/**
	 * @test
	 */
	public function existsRecordWithUidWithEmptyTableNameThrowsException() {
		$this->setExpectedException(
			'Exception',
			'The table name must not be empty.'
		);

		Tx_Phpunit_Service_Database::existsRecordWithUid('', 42);
	}

	/**
	 * @test
	 */
	public function existsRecordWithUidWithInvalidTableNameThrowsException() {
		$this->setExpectedException(
			'Exception', 'The table "tx_phpunit_doesnotexist" does not exist.'
		);

		Tx_Phpunit_Service_Database::existsRecordWithUid('tx_phpunit_doesnotexist', 42);
	}

	/**
	 * @test
	 */
	public function existsRecordWithUidForNoMatchReturnsFalse() {
		$this->assertFalse(
			Tx_Phpunit_Service_Database::existsRecordWithUid('tx_phpunit_test', 42)
		);
	}

	/**
	 * @test
	 */
	public function existsRecordWithUidForMatchReturnsTrue() {
		$uid = $this->testingFramework->createRecord(
			'tx_phpunit_test'
		);

		$this->assertTrue(
			Tx_Phpunit_Service_Database::existsRecordWithUid('tx_phpunit_test', $uid)
		);
	}

	/**
	 * @test
	 */
	public function existsRecordWithUidUsesAdditionalNonEmptyWhereClause() {
		$uid = $this->testingFramework->createRecord(
			'tx_phpunit_test', array('deleted' => 1)
		);

		$this->assertFalse(
			Tx_Phpunit_Service_Database::existsRecordWithUid(
				'tx_phpunit_test', $uid, ' AND deleted = 0'
			)
		);
	}
}
?>