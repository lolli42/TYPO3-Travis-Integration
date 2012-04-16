<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2011 Mario Rimann (typo3-coding@rimann.org)
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
 * Testcase for the Tx_Phpunit_Framework class.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Mario Rimann <typo3-coding@rimann.org>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Saskia Metzler <saskia@merlin.owl.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Phpunit_FrameworkTest extends tx_phpunit_testcase {
	/**
	 * @var Tx_Phpunit_Framework
	 */
	private $fixture;

	/**
	 * absolute path to a "foreign" file which was created for test purposes and
	 * which should be deleted in tearDown(); this is needed for
	 * deleteDummyFileWithForeignFileThrowsException
	 *
	 * @var string
	 */
	private $foreignFileToDelete = '';

	/**
	 * absolute path to a "foreign" folder which was created for test purposes
	 * and which should be deleted in tearDown(); this is needed for
	 * deleteDummyFolderWithForeignFolderThrowsException
	 *
	 * @var string
	 */
	private $foreignFolderToDelete = '';

	/**
	 * backed-up extension configuration of the TYPO3 configuration variables
	 *
	 * @var array
	 */
	private $extConfBackup = array();

	/**
	 * backed-up T3_VAR configuration
	 *
	 * @var array
	 */
	private $t3VarBackup = array();

	public function setUp() {
		$this->extConfBackup = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'];
		$this->t3VarBackup = $GLOBALS['T3_VAR']['getUserObj'];

		$this->fixture = new Tx_Phpunit_Framework('tx_phpunit', array('user_phpunittest'));
	}

	public function tearDown() {
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'] = $this->extConfBackup;
		$GLOBALS['T3_VAR']['getUserObj'] = $this->t3VarBackup;

		$this->fixture->setResetAutoIncrementThreshold(1);
		$this->fixture->purgeHooks();
		$this->fixture->cleanUp();
		$this->deleteForeignFile();
		$this->deleteForeignFolder();

		unset($this->fixture);
	}


	// ---------------------------------------------------------------------
	// Utility functions.
	// ---------------------------------------------------------------------

	/**
	 * Returns the sorting value of the relation between the local UID given by
	 * the first parameter $uidLocal and the foreign UID given by the second
	 * parameter $uidForeign.
	 *
	 * @param integer $uidLocal
	 *        the UID of the local record, must be > 0
	 * @param integer $uidForeign
	 *        the UID of the foreign record, must be > 0
	 *
	 * @return integer the sorting value of the relation
	 */
	private function getSortingOfRelation($uidLocal, $uidForeign) {
		$row = Tx_Phpunit_Service_Database::selectSingle(
			'sorting',
			'tx_phpunit_test_article_mm',
			'uid_local = ' . $uidLocal.' AND uid_foreign = ' . $uidForeign
		);

		return $row['sorting'];
	}

	/**
	 * Checks whether the extension user_phpunittest is currently loaded and lets
	 * a test fail if the extension is not loaded.
	 */
	private function checkIfExtensionUserPhpUnittestIsLoaded() {
		if (!t3lib_extMgm::isLoaded('user_phpunittest')) {
			$this->fail(
				'Extension user_phpunittest is not installed but needs to be ' .
					'installed! Please install it from EXT:phpunit/Tests/' .
					'Fixtures/Extensions/user_phpunittest/.'
			);
		}
	}

	/**
	 * Checks whether the extension user_phpunittest2 is currently loaded and lets
	 * a test fail if the extension is not loaded.
	 */
	private function checkIfExtensionUserPhpUnittest2IsLoaded() {
		if (!t3lib_extMgm::isLoaded('user_phpunittest')) {
			$this->fail(
				'Extension user_phpunittest2 is not installed but needs to be ' .
					'installed! Please install it from EXT:phpunit/Tests/' .
					'Fixtures/Extensions/user_phpunittest2/.'
			);
		}
	}

	/**
	 * Deletes a "foreign" file which was created for test purposes.
	 */
	private function deleteForeignFile() {
		if ($this->foreignFileToDelete == '') {
			return;
		}

		@unlink($this->foreignFileToDelete);
		$this->foreignFileToDelete = '';
	}

	/**
	 * Deletes a "foreign" folder which was created for test purposes.
	 */
	private function deleteForeignFolder() {
		if ($this->foreignFolderToDelete == '') {
			return;
		}

		t3lib_div::rmdir($this->foreignFolderToDelete);
		$this->foreignFolderToDelete = '';
	}

	/**
	 * Marks a test as skipped if the ZIPArchive class is not available in the
	 * PHP installation.
	 */
	private function markAsSkippedForNoZipArchive() {
		try {
			$this->fixture->checkForZipArchive();
		} catch (Exception $exception) {
			$this->markTestSkipped($exception->getMessage());
		}
	}


	// ---------------------------------------------------------------------
	// Tests regarding markTableAsDirty()
	// ---------------------------------------------------------------------

	/**
     * @test
     */
    public function markTableAsDirty() {
		$this->assertEquals(
			array(),
			$this->fixture->getListOfDirtyTables()
		);

		$this->fixture->createRecord('tx_phpunit_test', array());
		$this->assertEquals(
			array(
				'tx_phpunit_test' => 'tx_phpunit_test'
			),
			$this->fixture->getListOfDirtyTables()
		);
	}

	/**
     * @test
     */
    public function markTableAsDirtyWillCleanUpANonSystemTable() {
		$uid = Tx_Phpunit_Service_Database::insert(
			'tx_phpunit_test', array('is_dummy_record' => 1)
		);

		$this->fixture->markTableAsDirty('tx_phpunit_test');
		$this->fixture->cleanUp();
		$this->assertEquals(
			0,
			$this->fixture->countRecords('tx_phpunit_test', 'uid=' . $uid)
		);
	}

	/**
     * @test
     */
    public function markTableAsDirtyWillCleanUpASystemTable() {
		$uid = Tx_Phpunit_Service_Database::insert (
			'pages', array('tx_phpunit_is_dummy_record' => 1)
		);

		$this->fixture->markTableAsDirty('pages');
		$this->fixture->cleanUp();
		$this->assertEquals(
			0,
			$this->fixture->countRecords('pages', 'uid=' . $uid)
		);
	}

	/**
     * @test
     */
    public function markTableAsDirtyWillCleanUpAdditionalAllowedTable() {
		$this->checkIfExtensionUserPhpUnittestIsLoaded();

		$uid = Tx_Phpunit_Service_Database::insert(
			'user_phpunittest_test', array('tx_phpunit_is_dummy_record' => 1)
		);

		$this->fixture->markTableAsDirty('user_phpunittest_test');
		$this->fixture->cleanUp();
		$this->assertEquals(
			0,
			$this->fixture->countRecords('user_phpunittest_test', 'uid=' . $uid)
		);
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function markTableAsDirtyFailsOnInexistentTable() {
		$this->fixture->markTableAsDirty('tx_phpunit_DOESNOTEXIST');
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function markTableAsDirtyFailsOnNotAllowedSystemTable() {
		$this->fixture->markTableAsDirty('sys_domain');
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function markTableAsDirtyFailsOnForeignTable() {
		$this->fixture->markTableAsDirty('tx_seminars_seminars');
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function markTableAsDirtyFailsWithEmptyTableName() {
		$this->fixture->markTableAsDirty('');
	}

	/**
     * @test
     */
    public function markTableAsDirtyAcceptsCommaSeparatedListOfTableNames() {
		$this->fixture->markTableAsDirty('tx_phpunit_test'.','.'tx_phpunit_test_article_mm');
		$this->assertEquals(
			array(
				'tx_phpunit_test' => 'tx_phpunit_test',
				'tx_phpunit_test_article_mm' => 'tx_phpunit_test_article_mm'
			),
			$this->fixture->getListOfDirtyTables()
		);
	}


	// ---------------------------------------------------------------------
	// Tests regarding createRecord()
	// ---------------------------------------------------------------------

	/**
     * @test
     */
    public function createRecordOnValidTableWithNoData() {
		$this->assertNotEquals(
			0,
			$this->fixture->createRecord('tx_phpunit_test', array())
		);
	}

	/**
     * @test
     */
    public function createRecordWithValidData() {
		$title = 'TEST record';
		$uid = $this->fixture->createRecord(
			'tx_phpunit_test',
			array(
				'title' => $title
			)
		);
		$this->assertNotEquals(
			0,
			$uid
		);

		$row = Tx_Phpunit_Service_Database::selectSingle(
			'title',
			'tx_phpunit_test',
			'uid = ' . $uid
		);

		$this->assertEquals(
			$title,
			$row['title']
		);
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function createRecordOnInvalidTable() {
		$this->fixture->createRecord('tx_phpunit_DOESNOTEXIST', array());
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function createRecordWithEmptyTableName() {
		$this->fixture->createRecord('', array());
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function createRecordWithUidFails() {
		$this->fixture->createRecord(
			'tx_phpunit_test', array('uid' => 99999)
		);
	}

	/**
     * @test
     */
    public function createRecordOnValidAdditionalAllowedTableWithValidDataSucceeds() {
		$this->checkIfExtensionUserPhpUnittestIsLoaded();

		$title = 'TEST record';
		$this->fixture->createRecord(
			'user_phpunittest_test',
			array(
				'title' => $title
			)
		);
	}


	// ---------------------------------------------------------------------
	// Tests regarding changeRecord()
	// ---------------------------------------------------------------------

	/**
     * @test
     */
    public function changeRecordWithExistingRecord() {
		$uid = $this->fixture->createRecord(
			'tx_phpunit_test',
			array('title' => 'foo')
		);

		$this->fixture->changeRecord(
			'tx_phpunit_test',
			$uid,
			array('title' => 'bar')
		);

		$row = Tx_Phpunit_Service_Database::selectSingle(
			'title',
			'tx_phpunit_test',
			'uid = ' . $uid
		);

		$this->assertEquals(
			'bar',
			$row['title']
		);
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function changeRecordFailsOnForeignTable() {
		$this->fixture->changeRecord(
			'tx_seminars_seminars',
			99999,
			array('title' => 'foo')
		);
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function changeRecordFailsOnInexistentTable() {
		$this->fixture->changeRecord(
			'tx_phpunit_DOESNOTEXIST',
			99999,
			array('title' => 'foo')
		);
	}

	/**
     * @test
     */
    public function changeRecordOnAllowedSystemTableForPages() {
		$pid = $this->fixture->createFrontEndPage(0, array('title' => 'foo'));

		$this->fixture->changeRecord(
			'pages',
			$pid,
			array('title' => 'bar')
		);

		$this->assertEquals(
			1,
			$this->fixture->countRecords('pages', 'uid='.$pid.' AND title="bar"')
		);
	}

	/**
     * @test
     */
    public function changeRecordOnAllowedSystemTableForContent() {
		$pid = $this->fixture->createFrontEndPage(0, array('title' => 'foo'));
		$uid = $this->fixture->createContentElement(
			$pid,
			array('titleText' => 'foo')
		);

		$this->fixture->changeRecord(
			'tt_content',
			$uid,
			array('titleText' => 'bar')
		);

		$this->assertEquals(
			1,
			$this->fixture->countRecords('tt_content', 'uid=' . $uid.' AND titleText="bar"')
		);
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function changeRecordFailsOnOtherSystemTable() {
		$this->fixture->changeRecord(
			'sys_domain',
			1,
			array('title' => 'bar')
		);
	}

	/**
     * @test
     */
    public function changeRecordOnAdditionalAllowedTableSucceeds() {
		$this->checkIfExtensionUserPhpUnittestIsLoaded();

		$uid = $this->fixture->createRecord(
			'user_phpunittest_test',
			array('title' => 'foo')
		);

		$this->fixture->changeRecord(
			'user_phpunittest_test',
			$uid,
			array('title' => 'bar')
		);
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function changeRecordFailsWithUidZero() {
		$this->fixture->changeRecord('tx_phpunit_test', 0, array('title' => 'foo'));
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function changeRecordFailsWithEmptyData() {
		$uid = $this->fixture->createRecord('tx_phpunit_test', array());

		$this->fixture->changeRecord(
			'tx_phpunit_test', $uid, array()
		);
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function changeRecordFailsWithUidFieldInRecordData() {
		$uid = $this->fixture->createRecord('tx_phpunit_test', array());

		$this->fixture->changeRecord(
			'tx_phpunit_test', $uid, array('uid' => '55742')
		);
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function changeRecordFailsWithDummyRecordFieldInRecordData() {
		$uid = $this->fixture->createRecord('tx_phpunit_test', array());

		$this->fixture->changeRecord(
			'tx_phpunit_test', $uid, array('is_dummy_record' => 0)
		);
	}

	/**
     * @test
     *
     * @expectedException Tx_Phpunit_Exception_Database
     */
    public function changeRecordFailsOnInexistentRecord() {
		$uid = $this->fixture->createRecord('tx_phpunit_test', array());

		$this->fixture->changeRecord(
			'tx_phpunit_test', $uid + 1, array('title' => 'foo')
		);
	}


	// ---------------------------------------------------------------------
	// Tests regarding deleteRecord()
	// ---------------------------------------------------------------------

	/**
     * @test
     */
    public function deleteRecordOnValidDummyRecord() {
		// Creates and directly destroys a dummy record.
		$uid = $this->fixture->createRecord('tx_phpunit_test', array());
		$this->fixture->deleteRecord('tx_phpunit_test', $uid);

		// Checks whether the record really was removed from the database.
		$this->assertEquals(
			0,
			$this->fixture->countRecords('tx_phpunit_test', 'uid=' . $uid)
		);
	}

	/**
     * @test
     */
    public function deleteRecordOnValidDummyRecordOnAdditionalAllowedTableSucceeds() {
		$this->checkIfExtensionUserPhpUnittestIsLoaded();

		// Creates and directly destroys a dummy record.
		$uid = $this->fixture->createRecord('user_phpunittest_test', array());
		$this->fixture->deleteRecord('user_phpunittest_test', $uid);
	}

	/**
     * @test
     */
    public function deleteRecordOnInexistentRecord() {
		$uid = 99999;

		// Checks that the record is inexistent before testing on it.
		$this->assertEquals(
			0,
			$this->fixture->countRecords('tx_phpunit_test', 'uid=' . $uid)
		);

		// Runs our delete function - it should run through even when it can't
		// delete a record.
		$this->fixture->deleteRecord('tx_phpunit_test', $uid);
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function deleteRecordOnForeignTable() {
		$table = 'tx_seminars_seminars';
		$uid = 99999;

		$this->fixture->deleteRecord($table, $uid);
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function deleteRecordOnInexistentTable() {
		$table = 'tx_phpunit_DOESNOTEXIST';
		$uid = 99999;

		$this->fixture->deleteRecord($table, $uid);
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function deleteRecordWithEmptyTableName() {
		$table = '';
		$uid = 99999;

		$this->fixture->deleteRecord($table, $uid);
	}

	/**
     * @test
     */
    public function deleteRecordOnNonTestRecordNotDeletesRecord() {
		// Create a new record that looks like a real record, i.e. the
		// is_dummy_record flag is set to 0.
		$uid = Tx_Phpunit_Service_Database::insert(
			'tx_phpunit_test',
			array(
				'title' => 'TEST',
				'is_dummy_record' => 0
			)
		);

		// Runs our delete method which should NOT affect the record created
		// above.
		$this->fixture->deleteRecord('tx_phpunit_test', $uid);

		// Remembers whether the record still exists.
		$counter = Tx_Phpunit_Service_Database::count('tx_phpunit_test', 'uid = ' . $uid);

		// Deletes the record as it will not be caught by the clean up function.
		Tx_Phpunit_Service_Database::delete(
			'tx_phpunit_test',
			'uid = ' . $uid . ' AND is_dummy_record = 0'
		);

		// Checks whether the record still had existed.
		$this->assertEquals(
			1,
			$counter
		);
	}


	// ---------------------------------------------------------------------
	// Tests regarding createRelation()
	// ---------------------------------------------------------------------

	/**
     * @test
     */
    public function createRelationWithValidData() {
		$uidLocal = $this->fixture->createRecord('tx_phpunit_test');
		$uidForeign = $this->fixture->createRecord('tx_phpunit_test');

		$this->fixture->createRelation(
			'tx_phpunit_test_article_mm', $uidLocal, $uidForeign
		);

		// Checks whether the record really exists.
		$this->assertEquals(
			1,
			$this->fixture->countRecords(
				'tx_phpunit_test_article_mm',
				'uid_local=' . $uidLocal.' AND uid_foreign=' . $uidForeign
			)
		);
	}

	/**
     * @test
     */
    public function createRelationWithValidDataOnAdditionalAllowedTableSucceeds() {
		$this->checkIfExtensionUserPhpUnittestIsLoaded();

		$uidLocal = $this->fixture->createRecord('user_phpunittest_test');
		$uidForeign = $this->fixture->createRecord('user_phpunittest_test');

		$this->fixture->createRelation(
			'user_phpunittest_test_article_mm', $uidLocal, $uidForeign
		);
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function createRelationWithInvalidTable() {
		$table = 'tx_phpunit_test_DOESNOTEXIST_mm';
		$uidLocal = 99999;
		$uidForeign = 199999;

		$this->fixture->createRelation($table, $uidLocal, $uidForeign);
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function createRelationWithEmptyTableName() {
		$this->fixture->createRelation('', 99999, 199999);
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function createRelationWithZeroFirstUid() {
		$uid = $this->fixture->createRecord('tx_phpunit_test');
		$this->fixture->createRelation('tx_phpunit_test_article_mm', 0, $uid);
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function createRelationWithZeroSecondUid() {
		$uid = $this->fixture->createRecord('tx_phpunit_test');
		$this->fixture->createRelation('tx_phpunit_test_article_mm', $uid, 0);
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function createRelationWithNegativeFirstUid() {
		$uid = $this->fixture->createRecord('tx_phpunit_test');
		$this->fixture->createRelation('tx_phpunit_test_article_mm', -1, $uid);
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function createRelationWithNegativeSecondUid() {
		$uid = $this->fixture->createRecord('tx_phpunit_test');
		$this->fixture->createRelation('tx_phpunit_test_article_mm', $uid, -1);
	}


	/**
     * @test
     */
    public function createRelationWithAutomaticSorting() {
		$uidLocal = $this->fixture->createRecord('tx_phpunit_test');
		$uidForeign = $this->fixture->createRecord('tx_phpunit_test');
		$this->fixture->createRelation(
			'tx_phpunit_test_article_mm', $uidLocal, $uidForeign
		);
		$previousSorting = $this->getSortingOfRelation($uidLocal, $uidForeign);
		$this->assertGreaterThan(
			0,
			$previousSorting
		);


		$uidForeign = $this->fixture->createRecord('tx_phpunit_test');
		$this->fixture->createRelation(
			'tx_phpunit_test_article_mm', $uidLocal, $uidForeign
		);
		$nextSorting = $this->getSortingOfRelation($uidLocal, $uidForeign);
		$this->assertEquals(
			($previousSorting + 1),
			$nextSorting
		);
	}

	/**
     * @test
     */
    public function createRelationWithManualSorting() {
		$uidLocal = $this->fixture->createRecord('tx_phpunit_test');
		$uidForeign = $this->fixture->createRecord('tx_phpunit_test');
		$sorting = 99999;

		$this->fixture->createRelation(
			'tx_phpunit_test_article_mm', $uidLocal, $uidForeign, $sorting
		);

		$this->assertEquals(
			$sorting,
			$this->getSortingOfRelation($uidLocal, $uidForeign)
		);
	}


	// ---------------------------------------------------------------------
	// Tests regarding createRelationFromTca()
	// ---------------------------------------------------------------------

	/**
     * @test
     */
    public function createRelationAndUpdateCounterIncreasesZeroValueCounterByOne() {
		$firstRecordUid = $this->fixture->createRecord('tx_phpunit_test');
		$secondRecordUid = $this->fixture->createRecord('tx_phpunit_test');

		$this->fixture->createRelationAndUpdateCounter(
			'tx_phpunit_test',
			$firstRecordUid,
			$secondRecordUid,
			'related_records'
		);

		$row = Tx_Phpunit_Service_Database::selectSingle(
			'related_records',
			'tx_phpunit_test',
			'uid = ' . $firstRecordUid
		);

		$this->assertEquals(
			1,
			$row['related_records']
		);
	}

	/**
     * @test
     */
    public function createRelationAndUpdateCounterIncreasesNonZeroValueCounterToOne() {
		$firstRecordUid = $this->fixture->createRecord(
			'tx_phpunit_test',
			array('related_records' => 1)
		);
		$secondRecordUid = $this->fixture->createRecord('tx_phpunit_test');

		$this->fixture->createRelationAndUpdateCounter(
			'tx_phpunit_test',
			$firstRecordUid,
			$secondRecordUid,
			'related_records'
		);

		$row = Tx_Phpunit_Service_Database::selectSingle(
			'related_records',
			'tx_phpunit_test',
			'uid = ' . $firstRecordUid
		);

		$this->assertEquals(
			2,
			$row['related_records']
		);
	}

	/**
     * @test
     */
    public function createRelationAndUpdateCounterCreatesRecordInRelationTable() {
		$firstRecordUid = $this->fixture->createRecord('tx_phpunit_test');
		$secondRecordUid = $this->fixture->createRecord('tx_phpunit_test');

		$this->fixture->createRelationAndUpdateCounter(
			'tx_phpunit_test',
			$firstRecordUid,
			$secondRecordUid,
			'related_records'
		);

		$count = $this->fixture->countRecords(
			'tx_phpunit_test_article_mm',
			'uid_local=' . $firstRecordUid
		);
		$this->assertEquals(
			1,
			$count
		);
	}


	/**
	 * @test
	 */
	public function createRelationAndUpdateCounterWithBidirectionalRelationIncreasesCounter() {
		$firstRecordUid = $this->fixture->createRecord('tx_phpunit_test');
		$secondRecordUid = $this->fixture->createRecord('tx_phpunit_test');

		$this->fixture->createRelationAndUpdateCounter(
			'tx_phpunit_test',
			$firstRecordUid,
			$secondRecordUid,
			'bidirectional'
		);

		$row = Tx_Phpunit_Service_Database::selectSingle(
			'bidirectional',
			'tx_phpunit_test',
			'uid = ' . $firstRecordUid
		);

		$this->assertEquals(
			1,
			$row['bidirectional']
		);
	}

	/**
	 * @test
	 */
	public function createRelationAndUpdateCounterWithBidirectionalRelationIncreasesOppositeFieldCounterInForeignTable() {
		$firstRecordUid = $this->fixture->createRecord('tx_phpunit_test');
		$secondRecordUid = $this->fixture->createRecord('tx_phpunit_test');

		$this->fixture->createRelationAndUpdateCounter(
			'tx_phpunit_test',
			$firstRecordUid,
			$secondRecordUid,
			'bidirectional'
		);

		$row = Tx_Phpunit_Service_Database::selectSingle(
			'related_records',
			'tx_phpunit_test',
			'uid = ' . $secondRecordUid
		);

		$this->assertEquals(
			1,
			$row['related_records']
		);
	}

	/**
	 * @test
	 */
	public function createRelationAndUpdateCounterWithBidirectionalRelationCreatesRecordInRelationTable() {
		$firstRecordUid = $this->fixture->createRecord('tx_phpunit_test');
		$secondRecordUid = $this->fixture->createRecord('tx_phpunit_test');

		$this->fixture->createRelationAndUpdateCounter(
			'tx_phpunit_test',
			$firstRecordUid,
			$secondRecordUid,
			'bidirectional'
		);

		$count = $this->fixture->countRecords(
			'tx_phpunit_test_article_mm',
			'uid_local=' . $secondRecordUid . ' AND uid_foreign=' .
				$firstRecordUid
		);
		$this->assertEquals(
			1,
			$count
		);
	}


	// ---------------------------------------------------------------------
	// Tests regarding removeRelation()
	// ---------------------------------------------------------------------

	/**
     * @test
     */
    public function removeRelationOnValidDummyRecord() {
		$uidLocal = $this->fixture->createRecord('tx_phpunit_test');
		$uidForeign = $this->fixture->createRecord('tx_phpunit_test');

		// Creates and directly destroys a dummy record.
		$this->fixture->createRelation(
			'tx_phpunit_test_article_mm', $uidLocal, $uidForeign
		);
		$this->fixture->removeRelation(
			'tx_phpunit_test_article_mm', $uidLocal, $uidForeign
		);

		// Checks whether the record really was removed from the database.
		$this->assertEquals(
			0,
			$this->fixture->countRecords(
				'tx_phpunit_test_article_mm',
				'uid_local=' . $uidLocal.' AND uid_foreign=' . $uidForeign
			)
		);
	}

	/**
     * @test
     */
    public function removeRelationOnValidDummyRecordOnAdditionalAllowedTableSucceeds() {
		$this->checkIfExtensionUserPhpUnittestIsLoaded();

		$uidLocal = $this->fixture->createRecord('user_phpunittest_test');
		$uidForeign = $this->fixture->createRecord('user_phpunittest_test');

		// Creates and directly destroys a dummy record.
		$this->fixture->createRelation(
			'user_phpunittest_test_article_mm', $uidLocal, $uidForeign
		);
		$this->fixture->removeRelation(
			'user_phpunittest_test_article_mm', $uidLocal, $uidForeign
		);
	}

	/**
     * @test
     */
    public function removeRelationOnInexistentRecord() {
		$uid = $this->fixture->createRecord('tx_phpunit_test');
		$uidLocal = $uid + 1;
		$uidForeign = $uid + 2;

		// Checks that the record is inexistent before testing on it.
		$this->assertEquals(
			0,
			$this->fixture->countRecords(
				'tx_phpunit_test_article_mm',
				'uid_local=' . $uidLocal.' AND uid_foreign=' . $uidForeign
			)
		);

		// Runs our delete function - it should run through even when it can't
		// delete a record.
		$this->fixture->removeRelation(
			'tx_phpunit_test_article_mm', $uidLocal, $uidForeign
		);
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function removeRelationOnForeignTable() {
		$table = 'tx_seminars_seminars_places_mm';
		$uidLocal = 99999;
		$uidForeign = 199999;

		$this->fixture->removeRelation($table, $uidLocal, $uidForeign);
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function removeRelationOnInexistentTable() {
		$table = 'tx_phpunit_DOESNOTEXIST';
		$uidLocal = 99999;
		$uidForeign = 199999;

		$this->fixture->removeRelation($table, $uidLocal, $uidForeign);
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function removeRelationWithEmptyTableName() {
		$table = '';
		$uidLocal = 99999;
		$uidForeign = 199999;

		$this->fixture->removeRelation($table, $uidLocal, $uidForeign);
	}

	/**
     * @test
     */
    public function removeRelationOnRealRecordNotRemovesRelation() {
		$uidLocal = $this->fixture->createRecord('tx_phpunit_test');
		$uidForeign = $this->fixture->createRecord('tx_phpunit_test');;

		// Create a new record that looks like a real record, i.e. the
		// is_dummy_record flag is set to 0.
		Tx_Phpunit_Service_Database::insert(
			'tx_phpunit_test_article_mm',
			array(
				'uid_local' => $uidLocal,
				'uid_foreign' => $uidForeign,
				'is_dummy_record' => 0
			)
		);

		// Runs our delete method which should NOT affect the record created
		// above.
		$this->fixture->removeRelation(
			'tx_phpunit_test_article_mm', $uidLocal, $uidForeign
		);

		// Caches the value that will be tested for later. We need to use the
		// following order to make sure the test record gets deleted even if
		// this test fails:
		// 1. reads the value to test
		// 2. deletes the test record
		// 3. tests the previously read value (and possibly fails)
		$numberOfCreatedRelations = Tx_Phpunit_Service_Database::count(
			'tx_phpunit_test_article_mm',
			'uid_local = ' . $uidLocal . ' AND uid_foreign = ' . $uidForeign
		);

		// Deletes the record as it will not be caught by the clean up function.
		Tx_Phpunit_Service_Database::delete(
			'tx_phpunit_test_article_mm',
			'uid_local = ' . $uidLocal . ' AND uid_foreign = ' . $uidForeign
				.' AND is_dummy_record = 0'
		);

		// Checks whether the relation had been created further up.
		$this->assertEquals(
			1,
			$numberOfCreatedRelations
		);
	}


	// ---------------------------------------------------------------------
	// Tests regarding cleanUp()
	// ---------------------------------------------------------------------

	/**
	 * @test
	 */
	public function cleanUpWithRegularCleanUp() {
		// Creates a dummy record (and marks that table as dirty).
		$this->fixture->createRecord('tx_phpunit_test');

		// Creates a dummy record directly in the database, without putting this
		// table name to the list of dirty tables.
		Tx_Phpunit_Service_Database::insert(
			'tx_phpunit_test_article_mm', array('is_dummy_record' => 1)
		);

		// Runs a regular clean up. This should now delete only the first record
		// which was created through the testing framework and thus that table
		// is on the list of dirty tables. The second record was directly put
		// into the database and it's table is not on this list and will not be
		// removed by a regular clean up run.
		$this->fixture->cleanUp();

		// Checks whether the first dummy record is deleted.
		$this->assertEquals(
			0,
			$this->fixture->countRecords('tx_phpunit_test'),
			'Some test records were not deleted from table "tx_phpunit_test"'
		);

		// Checks whether the second dummy record still exists.
		$this->assertEquals(
			1,
			$this->fixture->countRecords('tx_phpunit_test_article_mm')
		);

		// Runs a deep clean up to delete all dummy records.
		$this->fixture->cleanUp(TRUE);
	}

	/**
	 * @test
	 */
	public function cleanUpWithDeepCleanup() {
		// Creates a dummy record (and marks that table as dirty).
		$this->fixture->createRecord('tx_phpunit_test');

		// Creates a dummy record directly in the database without putting this
		// table name to the list of dirty tables.
		Tx_Phpunit_Service_Database::insert(
			'tx_phpunit_test_article_mm', array('is_dummy_record' => 1)
		);

		// Deletes all dummy records.
		$this->fixture->cleanUp(TRUE);

		// Checks whether ALL dummy records were deleted (independent of the
		// list of dirty tables).
		$allowedTables = $this->fixture->getListOfDirtyTables();
		foreach ($allowedTables as $currentTable) {
			$this->assertEquals(
				0,
				$this->fixture->countRecords($currentTable),
				'Some test records were not deleted from table "'.$currentTable.'"'
			);
		}
	}

	/**
	 * @test
	 */
	public function cleanUpDeletesCreatedDummyFile() {
		$fileName = $this->fixture->createDummyFile();

		$this->fixture->cleanUp();

		$this->assertFalse(file_exists($fileName));
	}

	/**
	 * @test
	 */
	public function cleanUpDeletesCreatedDummyFolder() {
		$folderName = $this->fixture->createDummyFolder('test_folder');

		$this->fixture->cleanUp();

		$this->assertFalse(file_exists($folderName));
	}

	/**
	 * @test
	 */
	public function cleanUpDeletesCreatedNestedDummyFolders() {
		$outerDummyFolder = $this->fixture->createDummyFolder('test_folder');
		$innerDummyFolder = $this->fixture->createDummyFolder(
			$this->fixture->getPathRelativeToUploadDirectory($outerDummyFolder) .
				'/test_folder'
		);

		$this->fixture->cleanUp();

		$this->assertFalse(
			file_exists($outerDummyFolder) && file_exists($innerDummyFolder)
		);
	}

	/**
	 * @test
	 */
	public function cleanUpDeletesCreatedDummyUploadFolder() {
		$this->fixture->setUploadFolderPath(PATH_site . 'typo3temp/tx_phpunit_test/');
		$this->fixture->createDummyFile();

		$this->assertTrue(is_dir($this->fixture->getUploadFolderPath()));

		$this->fixture->cleanUp();

		$this->assertFalse(is_dir($this->fixture->getUploadFolderPath()));
	}

	/**
	 * @test
	 */
	public function cleanUpExecutesCleanUpHook() {
		$this->fixture->purgeHooks();

		$cleanUpHookMock = $this->getMock('Tx_Phpunit_Interface_FrameworkCleanupHook', array('cleanUp'));
		$cleanUpHookMock->expects($this->atLeastOnce())->method('cleanUp');

		$hookClassName = get_class($cleanUpHookMock);

		$GLOBALS['T3_VAR']['getUserObj'][$hookClassName] = $cleanUpHookMock;
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['FrameworkCleanUp']['phpunit_tests'] = $hookClassName;

		$this->fixture->cleanUp();
	}

	/**
	 * @test
	 *
	 * @expectedException t3lib_exception
	 */
	public function cleanUpForHookWithoutHookInterfaceThrowsException() {
		$this->fixture->purgeHooks();

		$hookClassName = uniqid('cleanUpHook');
		$cleanUpHookMock = $this->getMock($hookClassName, array('cleanUp'));

		$GLOBALS['T3_VAR']['getUserObj'][$hookClassName] = $cleanUpHookMock;
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['FrameworkCleanUp']['phpunit_tests'] = $hookClassName;

		$this->fixture->cleanUp();
	}


	// ---------------------------------------------------------------------
	// Tests regarding createListOfAllowedTables()
	//
	// The method is called in the constructor of the fixture.
	// ---------------------------------------------------------------------

	/**
     * @test
     */
    public function createListOfAllowedTablesContainsOurTestTable() {
		$allowedTables = $this->fixture->getListOfOwnAllowedTableNames();
		$this->assertContains(
			'tx_phpunit_test',
			$allowedTables
		);
	}

	/**
     * @test
     */
    public function createListOfAllowedTablesDoesNotContainForeignTables() {
		$allowedTables = $this->fixture->getListOfOwnAllowedTableNames();
		$this->assertNotContains(
			'be_users',
			$allowedTables
		);
	}


	// ---------------------------------------------------------------------
	// Tests regarding createListOfAdditionalAllowedTables()
	//
	// (That method is called in the constructor of the fixture.)
	// ---------------------------------------------------------------------

	/**
     * @test
     */
    public function createListOfAdditionalAllowedTablesContainsOurTestTable() {
		$this->checkIfExtensionUserPhpUnittestIsLoaded();

		$allowedTables = $this->fixture->getListOfAdditionalAllowedTableNames();
		$this->assertContains(
			'user_phpunittest_test',
			$allowedTables
		);
	}

	/**
     * @test
     */
    public function createListOfAdditionalAllowedTablesDoesNotContainForeignTables() {
		$allowedTables = $this->fixture->getListOfAdditionalAllowedTableNames();
		$this->assertNotContains(
			'be_users',
			$allowedTables
		);
	}

	/**
     * @test
     */
    public function createListOfAdditionalAllowedTablesContainsOurTestTables() {
		$this->checkIfExtensionUserPhpUnittestIsLoaded();
		$this->checkIfExtensionUserPhpUnittest2IsLoaded();

		$fixture = new Tx_Phpunit_Framework(
			'tx_phpunit', array('user_phpunittest', 'user_phpunittest2')
		);

		$allowedTables = $fixture->getListOfAdditionalAllowedTableNames();
		$this->assertContains(
			'user_phpunittest_test',
			$allowedTables
		);
		$this->assertContains(
			'user_phpunittest2_test',
			$allowedTables
		);
	}


	// ---------------------------------------------------------------------
	// Tests regarding getAutoIncrement()
	// ---------------------------------------------------------------------

	/**
     * @test
     */
    public function getAutoIncrementReturnsOneForTruncatedTable() {
		Tx_Phpunit_Service_Database::enableQueryLogging();
		$dbResult = $GLOBALS['TYPO3_DB']->sql_query(
			'TRUNCATE TABLE tx_phpunit_test;'
		);
		if (!$dbResult) {
			throw new Tx_Phpunit_Exception_Database();
		}

		$this->assertEquals(
			1,
			$this->fixture->getAutoIncrement('tx_phpunit_test')
		);
	}

	/**
     * @test
     */
    public function getAutoIncrementGetsCurrentAutoIncrement() {
		$uid = $this->fixture->createRecord('tx_phpunit_test');

		// $uid will equals be the previous auto increment value, so $uid + 1
		// should be equal to the current auto increment value.
		$this->assertEquals(
			$uid + 1,
			$this->fixture->getAutoIncrement('tx_phpunit_test')
		);
	}

	/**
     * @test
     */
    public function getAutoIncrementForFeUsersTableIsAllowed() {
		$this->fixture->getAutoIncrement('fe_users');
	}

	/**
     * @test
     */
    public function getAutoIncrementForPagesTableIsAllowed() {
		$this->fixture->getAutoIncrement('pages');
	}

	/**
     * @test
     */
    public function getAutoIncrementForTtContentTableIsAllowed() {
		$this->fixture->getAutoIncrement('tt_content');
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function getAutoIncrementWithOtherSystemTableFails() {
		$this->fixture->getAutoIncrement('sys_domains');
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function getAutoIncrementWithEmptyTableNameFails() {
		$this->fixture->getAutoIncrement('');
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function getAutoIncrementWithForeignTableFails() {
		$this->fixture->getAutoIncrement('tx_seminars_seminars');
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function getAutoIncrementWithInexistentTableFails() {
		$this->fixture->getAutoIncrement('tx_phpunit_DOESNOTEXIST');
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function getAutoIncrementWithTableWithoutUidFails() {
		$this->fixture->getAutoIncrement('tx_phpunit_test_article_mm');
	}


	// ---------------------------------------------------------------------
	// Tests regarding countRecords()
	// ---------------------------------------------------------------------

	/**
     * @test
     */
    public function countRecordsWithEmptyWhereClauseIsAllowed() {
		$this->fixture->countRecords('tx_phpunit_test', '');
	}

	/**
     * @test
     */
    public function countRecordsWithMissingWhereClauseIsAllowed() {
		$this->fixture->countRecords('tx_phpunit_test');
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function countRecordsWithEmptyTableNameThrowsException() {
		$this->fixture->countRecords('');
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function countRecordsWithInvalidTableNameThrowsException() {
		$table = 'foo_bar';
		$this->fixture->countRecords($table);
	}

	/**
     * @test
     */
    public function countRecordsWithFeGroupsTableIsAllowed() {
		$table = 'fe_groups';
		$this->fixture->countRecords($table);
	}

	/**
     * @test
     */
    public function countRecordsWithFeUsersTableIsAllowed() {
		$table = 'fe_users';
		$this->fixture->countRecords($table);
	}

	/**
     * @test
     */
    public function countRecordsWithPagesTableIsAllowed() {
		$table = 'pages';
		$this->fixture->countRecords($table);
	}

	/**
     * @test
     */
    public function countRecordsWithTtContentTableIsAllowed() {
		$table = 'tt_content';
		$this->fixture->countRecords($table);
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function countRecordsWithOtherTableThrowsException() {
		$this->fixture->countRecords('sys_domain');
	}

	/**
     * @test
     */
    public function countRecordsReturnsZeroForNoMatches() {
		$this->assertEquals(
			0,
			$this->fixture->countRecords('tx_phpunit_test', 'title = "foo"')
		);
	}

	/**
     * @test
     */
    public function countRecordsReturnsOneForOneDummyRecordMatch() {
		$this->fixture->createRecord(
			'tx_phpunit_test', array('title' => 'foo')
		);

		$this->assertEquals(
			1,
			$this->fixture->countRecords('tx_phpunit_test', 'title = "foo"')
		);
	}

	/**
     * @test
     */
    public function countRecordsWithMissingWhereClauseReturnsOneForOneDummyRecordMatch() {
		$this->fixture->createRecord(
			'tx_phpunit_test', array('title' => 'foo')
		);

		$this->assertEquals(
			1,
			$this->fixture->countRecords('tx_phpunit_test')
		);
	}

	/**
     * @test
     */
    public function countRecordsReturnsTwoForTwoMatches() {
		$this->fixture->createRecord(
			'tx_phpunit_test', array('title' => 'foo')
		);
		$this->fixture->createRecord(
			'tx_phpunit_test', array('title' => 'foo')
		);

		$this->assertEquals(
			2,
			$this->fixture->countRecords('tx_phpunit_test', 'title = "foo"')
		);
	}

	/**
     * @test
     */
    public function countRecordsForPagesTableIsAllowed() {
		$this->fixture->countRecords('pages');
	}

	/**
     * @test
     */
    public function countRecordsIgnoresNonDummyRecords() {
		Tx_Phpunit_Service_Database::insert(
			'tx_phpunit_test', array('title' => 'foo')
		);

		$testResult = $this->fixture->countRecords(
			'tx_phpunit_test', 'title = "foo"'
		);

		Tx_Phpunit_Service_Database::delete(
			'tx_phpunit_test',
			'title = "foo"'
		);
		// We need to do this manually to not confuse the auto_increment counter
		// of the testing framework.
		$this->fixture->resetAutoIncrement('tx_phpunit_test');

		$this->assertEquals(
			0,
			$testResult
		);
	}


	// ---------------------------------------------------------------------
	// Tests regarding existsRecord()
	// ---------------------------------------------------------------------

	/**
     * @test
     */
    public function existsRecordWithEmptyWhereClauseIsAllowed() {
		$this->fixture->existsRecord('tx_phpunit_test', '');
	}

	/**
     * @test
     */
    public function existsRecordWithMissingWhereClauseIsAllowed() {
		$this->fixture->existsRecord('tx_phpunit_test');
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function existsRecordWithEmptyTableNameThrowsException() {
		$this->fixture->existsRecord('');
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function existsRecordWithInvalidTableNameThrowsException() {
		$table = 'foo_bar';
		$this->fixture->existsRecord($table);
	}

	/**
     * @test
     */
    public function existsRecordForNoMatchesReturnsFalse() {
		$this->assertFalse(
			$this->fixture->existsRecord('tx_phpunit_test', 'title = "foo"')
		);
	}

	/**
     * @test
     */
    public function existsRecordForOneMatchReturnsTrue() {
		$this->fixture->createRecord(
			'tx_phpunit_test', array('title' => 'foo')
		);

		$this->assertTrue(
			$this->fixture->existsRecord('tx_phpunit_test', 'title = "foo"')
		);
	}

	/**
     * @test
     */
    public function existsRecordForTwoMatchesReturnsTrue() {
		$this->fixture->createRecord(
			'tx_phpunit_test', array('title' => 'foo')
		);
		$this->fixture->createRecord(
			'tx_phpunit_test', array('title' => 'foo')
		);

		$this->assertTrue(
			$this->fixture->existsRecord('tx_phpunit_test', 'title = "foo"')
		);
	}

	/**
     * @test
     */
    public function existsRecordIgnoresNonDummyRecords() {
		Tx_Phpunit_Service_Database::insert(
			'tx_phpunit_test', array('title' => 'foo')
		);

		$testResult = $this->fixture->existsRecord(
			'tx_phpunit_test', 'title = "foo"'
		);

		Tx_Phpunit_Service_Database::delete(
			'tx_phpunit_test',
			'title = "foo"'
		);
		// We need to do this manually to not confuse the auto_increment counter
		// of the testing framework.
		$this->fixture->resetAutoIncrement('tx_phpunit_test');

		$this->assertFalse(
			$testResult
		);
	}


	// ---------------------------------------------------------------------
	// Tests regarding existsRecordWithUid()
	// ---------------------------------------------------------------------

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function existsRecordWithUidWithZeroUidThrowsException() {
		$this->fixture->existsRecordWithUid('tx_phpunit_test', 0);
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function existsRecordWithUidWithNegativeUidThrowsException() {
		$this->fixture->existsRecordWithUid('tx_phpunit_test', -1);
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function existsRecordWithUidWithEmptyTableNameThrowsException() {
		$this->fixture->existsRecordWithUid('', 1);
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function existsRecordWithUidWithInvalidTableNameThrowsException() {
		$table = 'foo_bar';
		$this->fixture->existsRecordWithUid($table, 1);
	}

	/**
     * @test
     */
    public function existsRecordWithUidForNoMatcheReturnsFalse() {
		$uid = $this->fixture->createRecord('tx_phpunit_test');
		$this->fixture->deleteRecord('tx_phpunit_test', $uid);

		$this->assertFalse(
			$this->fixture->existsRecordWithUid(
				'tx_phpunit_test', $uid
			)
		);
	}

	/**
     * @test
     */
    public function existsRecordWithUidForAMatchReturnsTrue() {
		$uid = $this->fixture->createRecord('tx_phpunit_test');

		$this->assertTrue(
			$this->fixture->existsRecordWithUid('tx_phpunit_test', $uid)
		);
	}

	/**
     * @test
     */
    public function existsRecordWithUidIgnoresNonDummyRecords() {
		$uid = Tx_Phpunit_Service_Database::insert(
			'tx_phpunit_test', array('title' => 'foo')
		);

		$testResult = $this->fixture->existsRecordWithUid(
			'tx_phpunit_test', $uid
		);

		Tx_Phpunit_Service_Database::delete(
			'tx_phpunit_test', 'uid = ' . $uid
		);
		// We need to do this manually to not confuse the auto_increment counter
		// of the testing framework.
		$this->fixture->resetAutoIncrement('tx_phpunit_test');

		$this->assertFalse(
			$testResult
		);
	}


	// ---------------------------------------------------------------------
	// Tests regarding existsExactlyOneRecord()
	// ---------------------------------------------------------------------

	/**
     * @test
     */
    public function existsExactlyOneRecordWithEmptyWhereClauseIsAllowed() {
		$this->fixture->existsExactlyOneRecord('tx_phpunit_test', '');
	}

	/**
     * @test
     */
    public function existsExactlyOneRecordWithMissingWhereClauseIsAllowed() {
		$this->fixture->existsExactlyOneRecord('tx_phpunit_test');
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function existsExactlyOneRecordWithEmptyTableNameThrowsException() {
		$this->fixture->existsExactlyOneRecord('');
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function existsExactlyOneRecordWithInvalidTableNameThrowsException() {
		$table = 'foo_bar';
		$this->fixture->existsExactlyOneRecord($table);
	}

	/**
     * @test
     */
    public function existsExactlyOneRecordForNoMatchesReturnsFalse() {
		$this->assertFalse(
			$this->fixture->existsExactlyOneRecord(
				'tx_phpunit_test', 'title = "foo"'
			)
		);
	}

	/**
     * @test
     */
    public function existsExactlyOneRecordForOneMatchReturnsTrue() {
		$this->fixture->createRecord(
			'tx_phpunit_test', array('title' => 'foo')
		);

		$this->assertTrue(
			$this->fixture->existsExactlyOneRecord(
				'tx_phpunit_test', 'title = "foo"'
			)
		);
	}

	/**
     * @test
     */
    public function existsExactlyOneRecordForTwoMatchesReturnsFalse() {
		$this->fixture->createRecord(
			'tx_phpunit_test', array('title' => 'foo')
		);
		$this->fixture->createRecord(
			'tx_phpunit_test', array('title' => 'foo')
		);

		$this->assertFalse(
			$this->fixture->existsExactlyOneRecord('tx_phpunit_test', 'title = "foo"')
		);
	}

	/**
     * @test
     */
    public function existsExactlyOneRecordIgnoresNonDummyRecords() {
		Tx_Phpunit_Service_Database::insert(
			'tx_phpunit_test', array('title' => 'foo')
		);

		$testResult = $this->fixture->existsExactlyOneRecord(
			'tx_phpunit_test', 'title = "foo"'
		);

		Tx_Phpunit_Service_Database::delete(
			'tx_phpunit_test',
			'title = "foo"'
		);
		// We need to do this manually to not confuse the auto_increment counter
		// of the testing framework.
		$this->fixture->resetAutoIncrement('tx_phpunit_test');

		$this->assertFalse(
			$testResult
		);
	}


	// ---------------------------------------------------------------------
	// Tests regarding resetAutoIncrement()
	// ---------------------------------------------------------------------

	/**
     * @test
     */
    public function resetAutoIncrementForTestTableSucceeds() {
		$latestUid = $this->fixture->createRecord('tx_phpunit_test');
		$this->fixture->deleteRecord('tx_phpunit_test', $latestUid);
		$this->fixture->resetAutoIncrement('tx_phpunit_test');

		$this->assertEquals(
			$latestUid,
			$this->fixture->getAutoIncrement('tx_phpunit_test')
		);
	}

	/**
     * @test
     */
    public function resetAutoIncrementForUnchangedTestTableCanBeRun() {
		$this->fixture->resetAutoIncrement('tx_phpunit_test');
	}

	/**
     * @test
     */
    public function resetAutoIncrementForAdditionalAllowedTableSucceeds() {
		$this->checkIfExtensionUserPhpUnittestIsLoaded();

		// Creates and deletes a record and then resets the auto increment.
		$latestUid = $this->fixture->createRecord('user_phpunittest_test');
		$this->fixture->deleteRecord('user_phpunittest_test', $latestUid);
		$this->fixture->resetAutoIncrement('user_phpunittest_test');
	}

	/**
     * @test
     */
    public function resetAutoIncrementForTableWithoutUidIsAllowed() {
		$this->fixture->resetAutoIncrement('tx_phpunit_test_article_mm');
	}

	/**
     * @test
     */
    public function resetAutoIncrementForFeUsersTableIsAllowed() {
		$this->fixture->resetAutoIncrement('fe_users');
	}

	/**
     * @test
     */
    public function resetAutoIncrementForPagesTableIsAllowed() {
		$this->fixture->resetAutoIncrement('pages');
	}

	/**
     * @test
     */
    public function resetAutoIncrementForTtContentTableIsAllowed() {
		$this->fixture->resetAutoIncrement('tt_content');
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function resetAutoIncrementWithOtherSystemTableFails() {
		$this->fixture->resetAutoIncrement('sys_domains');
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function resetAutoIncrementWithEmptyTableNameFails() {
		$this->fixture->resetAutoIncrement('');
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function resetAutoIncrementWithForeignTableFails() {
		$this->fixture->resetAutoIncrement('tx_seminars_seminars');
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function resetAutoIncrementWithInexistentTableFails() {
		$this->fixture->resetAutoIncrement('tx_phpunit_DOESNOTEXIST');
	}


	// ---------------------------------------------------------------------
	// Tests regarding resetAutoIncrementLazily() and
	// setResetAutoIncrementThreshold
	// ---------------------------------------------------------------------

	/**
     * @test
     */
    public function resetAutoIncrementLazilyForTestTableIsAllowed() {
		$this->fixture->resetAutoIncrementLazily('tx_phpunit_test');
	}

	/**
     * @test
     */
    public function resetAutoIncrementLazilyForTableWithoutUidIsAllowed() {
		$this->fixture->resetAutoIncrementLazily('tx_phpunit_test_article_mm');
	}

	/**
     * @test
     */
    public function resetAutoIncrementLazilyForFeUsersTableIsAllowed() {
		$this->fixture->resetAutoIncrementLazily('fe_users');
	}

	/**
     * @test
     */
    public function resetAutoIncrementLazilyForPagesTableIsAllowed() {
		$this->fixture->resetAutoIncrementLazily('pages');
	}

	/**
     * @test
     */
    public function resetAutoIncrementLazilyForTtContentTableIsAllowed() {
		$this->fixture->resetAutoIncrementLazily('tt_content');
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function resetAutoIncrementLazilyWithOtherSystemTableFails() {
		$this->fixture->resetAutoIncrementLazily('sys_domains');
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function resetAutoIncrementLazilyWithEmptyTableNameFails() {
		$this->fixture->resetAutoIncrementLazily('');
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function resetAutoIncrementLazilyWithForeignTableFails() {
		$this->fixture->resetAutoIncrementLazily('tx_seminars_seminars');
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function resetAutoIncrementLazilyWithInexistentTableFails() {
		$this->fixture->resetAutoIncrementLazily('tx_phpunit_DOESNOTEXIST');
	}

	/**
     * @test
     */
    public function resetAutoIncrementLazilyDoesNothingAfterOneNewRecordByDefault() {
		$oldAutoIncrement = $this->fixture->getAutoIncrement('tx_phpunit_test');

		$latestUid = $this->fixture->createRecord('tx_phpunit_test');
		$this->fixture->deleteRecord('tx_phpunit_test', $latestUid);
		$this->fixture->resetAutoIncrementLazily('tx_phpunit_test');

		$this->assertNotEquals(
			$oldAutoIncrement,
			$this->fixture->getAutoIncrement('tx_phpunit_test')
		);
	}

	/**
     * @test
     */
    public function resetAutoIncrementLazilyCleansUpsAfterOneNewRecordWithThreshholdOfOne() {
		$oldAutoIncrement = $this->fixture->getAutoIncrement('tx_phpunit_test');
		$this->fixture->setResetAutoIncrementThreshold(1);

		$latestUid = $this->fixture->createRecord('tx_phpunit_test');
		$this->fixture->deleteRecord('tx_phpunit_test', $latestUid);
		$this->fixture->resetAutoIncrementLazily('tx_phpunit_test');

		$this->assertEquals(
			$oldAutoIncrement,
			$this->fixture->getAutoIncrement('tx_phpunit_test')
		);
	}

	/**
     * @test
     */
    public function resetAutoIncrementLazilyCleansUpsAfter100NewRecordsByDefault() {
		$oldAutoIncrement = $this->fixture->getAutoIncrement('tx_phpunit_test');

		for ($i = 0; $i < 100; $i++) {
			$latestUid = $this->fixture->createRecord('tx_phpunit_test');
			$this->fixture->deleteRecord('tx_phpunit_test', $latestUid);
		}

		$this->fixture->resetAutoIncrementLazily('tx_phpunit_test');

		$this->assertEquals(
			$oldAutoIncrement,
			$this->fixture->getAutoIncrement('tx_phpunit_test')
		);
	}

	/**
     * @test
     */
    public function setResetAutoIncrementThresholdForOneIsAllowed() {
		$this->fixture->setResetAutoIncrementThreshold(1);
	}

	/**
     * @test
     */
    public function setResetAutoIncrementThresholdFor100IsAllowed() {
		$this->fixture->setResetAutoIncrementThreshold(100);
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function setResetAutoIncrementThresholdForZeroFails() {
		$this->fixture->setResetAutoIncrementThreshold(0);
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function setResetAutoIncrementThresholdForMinus1Fails() {
		$this->fixture->setResetAutoIncrementThreshold(-1);
	}


	// ---------------------------------------------------------------------
	// Tests regarding createFrontEndPage()
	// ---------------------------------------------------------------------

	/**
     * @test
     */
    public function frontEndPageCanBeCreated() {
		$uid = $this->fixture->createFrontEndPage();

		$this->assertNotEquals(
			0,
			$uid
		);

		$this->assertEquals(
			1,
			$this->fixture->countRecords(
				'pages', 'uid=' . $uid
			)
		);
	}

	/**
     * @test
     */
    public function createFrontEndPageSetsCorrectDocumentType() {
		$uid = $this->fixture->createFrontEndPage();

		$this->assertNotEquals(
			0,
			$uid
		);

		$row = Tx_Phpunit_Service_Database::selectSingle(
			'doktype',
			'pages',
			'uid = ' . $uid
		);

		$this->assertEquals(
			1,
			$row['doktype']
		);
	}

	/**
     * @test
     */
    public function frontEndPageWillBeCreatedOnRootPage() {
		$uid = $this->fixture->createFrontEndPage();

		$this->assertNotEquals(
			0,
			$uid
		);

		$row = Tx_Phpunit_Service_Database::selectSingle(
			'pid',
			'pages',
			'uid = ' . $uid
		);

		$this->assertEquals(
			0,
			$row['pid']
		);
	}

	/**
     * @test
     */
    public function frontEndPageCanBeCreatedOnOtherPage() {
		$parent = $this->fixture->createFrontEndPage();
		$uid = $this->fixture->createFrontEndPage($parent);

		$this->assertNotEquals(
			0,
			$uid
		);

		$row = Tx_Phpunit_Service_Database::selectSingle(
			'pid',
			'pages',
			'uid = ' . $uid
		);

		$this->assertEquals(
			$parent,
			$row['pid']
		);
	}

	/**
     * @test
     */
    public function frontEndPageCanBeDirty() {
		$this->assertEquals(
			0,
			count($this->fixture->getListOfDirtySystemTables())
		);
		$uid = $this->fixture->createFrontEndPage();
		$this->assertNotEquals(
			0,
			$uid
		);

		$this->assertNotEquals(
			0,
			count($this->fixture->getListOfDirtySystemTables())
		);
	}

	/**
     * @test
     */
    public function frontEndPageWillBeCleanedUp() {
		$uid = $this->fixture->createFrontEndPage();
		$this->assertNotEquals(
			0,
			$uid
		);

		$this->fixture->cleanUp();
		$this->assertEquals(
			0,
			$this->fixture->countRecords(
				'pages', 'uid=' . $uid
			)
		);
	}

	/**
     * @test
     */
    public function frontEndPageHasNoTitleByDefault() {
		$uid = $this->fixture->createFrontEndPage();

		$row = Tx_Phpunit_Service_Database::selectSingle(
			'title',
			'pages',
			'uid = ' . $uid
		);

		$this->assertEquals(
			'',
			$row['title']
		);
	}

	/**
     * @test
     */
    public function frontEndPageCanHaveTitle() {
		$uid = $this->fixture->createFrontEndPage(
			0,
			array('title' => 'Test title')
		);

		$row = Tx_Phpunit_Service_Database::selectSingle(
			'title',
			'pages',
			'uid = ' . $uid
		);

		$this->assertEquals(
			'Test title',
			$row['title']
		);
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function frontEndPageMustHaveNoZeroPid() {
		$this->fixture->createFrontEndPage(0, array('pid' => 0));
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function frontEndPageMustHaveNoNonZeroPid() {
		$this->fixture->createFrontEndPage(0, array('pid' => 99999));
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function frontEndPageMustHaveNoZeroUid() {
		$this->fixture->createFrontEndPage(0, array('uid' => 0));
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function frontEndPageMustHaveNoNonZeroUid() {
		$this->fixture->createFrontEndPage(0, array('uid' => 99999));
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function frontEndPageMustHaveNoZeroDoktype() {
		$this->fixture->createFrontEndPage(0, array('doktype' => 0));
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function frontEndPageMustHaveNoNonZeroDoktype() {
		$this->fixture->createFrontEndPage(0, array('doktype' => 99999));
	}


	// ---------------------------------------------------------------------
	// Tests regarding createSystemFolder()
	// ---------------------------------------------------------------------

	/**
     * @test
     */
    public function systemFolderCanBeCreated() {
		$uid = $this->fixture->createSystemFolder();

		$this->assertNotEquals(
			0,
			$uid
		);

		$this->assertEquals(
			1,
			$this->fixture->countRecords(
				'pages', 'uid=' . $uid
			)
		);
	}

	/**
     * @test
     */
    public function createSystemFolderSetsCorrectDocumentType() {
		$uid = $this->fixture->createSystemFolder();

		$this->assertNotEquals(
			0,
			$uid
		);

		$row = Tx_Phpunit_Service_Database::selectSingle(
			'doktype',
			'pages',
			'uid = ' . $uid
		);

		$this->assertEquals(
			254,
			$row['doktype']
		);
	}

	/**
     * @test
     */
    public function systemFolderWillBeCreatedOnRootPage() {
		$uid = $this->fixture->createSystemFolder();

		$this->assertNotEquals(
			0,
			$uid
		);

		$row = Tx_Phpunit_Service_Database::selectSingle(
			'pid',
			'pages',
			'uid = ' . $uid
		);

		$this->assertEquals(
			0,
			$row['pid']
		);
	}

	/**
     * @test
     */
    public function systemFolderCanBeCreatedOnOtherPage() {
		$parent = $this->fixture->createSystemFolder();
		$uid = $this->fixture->createSystemFolder($parent);

		$this->assertNotEquals(
			0,
			$uid
		);

		$row = Tx_Phpunit_Service_Database::selectSingle(
			'pid',
			'pages',
			'uid = ' . $uid
		);

		$this->assertEquals(
			$parent,
			$row['pid']
		);
	}

	/**
     * @test
     */
    public function systemFolderCanBeDirty() {
		$this->assertEquals(
			0,
			count($this->fixture->getListOfDirtySystemTables())
		);
		$uid = $this->fixture->createSystemFolder();
		$this->assertNotEquals(
			0,
			$uid
		);

		$this->assertNotEquals(
			0,
			count($this->fixture->getListOfDirtySystemTables())
		);
	}

	/**
     * @test
     */
    public function systemFolderWillBeCleanedUp() {
		$uid = $this->fixture->createSystemFolder();
		$this->assertNotEquals(
			0,
			$uid
		);

		$this->fixture->cleanUp();
		$this->assertEquals(
			0,
			$this->fixture->countRecords(
				'pages', 'uid=' . $uid
			)
		);
	}

	/**
     * @test
     */
    public function systemFolderHasNoTitleByDefault() {
		$uid = $this->fixture->createSystemFolder();

		$row = Tx_Phpunit_Service_Database::selectSingle(
			'title',
			'pages',
			'uid = ' . $uid
		);

		$this->assertEquals(
			'',
			$row['title']
		);
	}

	/**
     * @test
     */
    public function systemFolderCanHaveTitle() {
		$uid = $this->fixture->createSystemFolder(
			0,
			array('title' => 'Test title')
		);

		$row = Tx_Phpunit_Service_Database::selectSingle(
			'title',
			'pages',
			'uid = ' . $uid
		);

		$this->assertEquals(
			'Test title',
			$row['title']
		);
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function systemFolderMustHaveNoZeroPid() {
		$this->fixture->createSystemFolder(0, array('pid' => 0));
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function systemFolderMustHaveNoNonZeroPid() {
		$this->fixture->createSystemFolder(0, array('pid' => 99999));
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function systemFolderMustHaveNoZeroUid() {
		$this->fixture->createSystemFolder(0, array('uid' => 0));
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function systemFolderMustHaveNoNonZeroUid() {
		$this->fixture->createSystemFolder(0, array('uid' => 99999));
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function systemFolderMustHaveNoZeroDoktype() {
		$this->fixture->createSystemFolder(0, array('doktype' => 0));
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function systemFolderMustHaveNoNonZeroDoktype() {
		$this->fixture->createSystemFolder(0, array('doktype' => 99999));
	}


	// ---------------------------------------------------------------------
	// Tests regarding createContentElement()
	// ---------------------------------------------------------------------

	/**
     * @test
     */
    public function contentElementCanBeCreated() {
		$uid = $this->fixture->createContentElement();

		$this->assertNotEquals(
			0,
			$uid
		);

		$this->assertEquals(
			1,
			$this->fixture->countRecords(
				'tt_content', 'uid=' . $uid
			)
		);
	}

	/**
     * @test
     */
    public function contentElementWillBeCreatedOnRootPage() {
		$uid = $this->fixture->createContentElement();

		$this->assertNotEquals(
			0,
			$uid
		);

		$row = Tx_Phpunit_Service_Database::selectSingle(
			'pid',
			'tt_content',
			'uid = ' . $uid
		);

		$this->assertEquals(
			0,
			$row['pid']
		);
	}

	/**
     * @test
     */
    public function contentElementCanBeCreatedOnNonRootPage() {
		$parent = $this->fixture->createSystemFolder();
		$uid = $this->fixture->createContentElement($parent);

		$this->assertNotEquals(
			0,
			$uid
		);

		$row = Tx_Phpunit_Service_Database::selectSingle(
			'pid',
			'tt_content',
			'uid = ' . $uid
		);

		$this->assertEquals(
			$parent,
			$row['pid']
		);
	}

	/**
     * @test
     */
    public function contentElementCanBeDirty() {
		$this->assertEquals(
			0,
			count($this->fixture->getListOfDirtySystemTables())
		);
		$uid = $this->fixture->createContentElement();
		$this->assertNotEquals(
			0,
			$uid
		);

		$this->assertNotEquals(
			0,
			count($this->fixture->getListOfDirtySystemTables())
		);
	}

	/**
     * @test
     */
    public function contentElementWillBeCleanedUp() {
		$uid = $this->fixture->createContentElement();
		$this->assertNotEquals(
			0,
			$uid
		);

		$this->fixture->cleanUp();
		$this->assertEquals(
			0,
			$this->fixture->countRecords(
				'tt_content', 'uid=' . $uid
			)
		);
	}

	/**
     * @test
     */
    public function contentElementHasNoHeaderByDefault() {
		$uid = $this->fixture->createContentElement();

		$row = Tx_Phpunit_Service_Database::selectSingle(
			'header',
			'tt_content',
			'uid = ' . $uid
		);

		$this->assertEquals(
			'',
			$row['header']
		);
	}

	/**
     * @test
     */
    public function contentElementCanHaveHeader() {
		$uid = $this->fixture->createContentElement(
			0,
			array('header' => 'Test header')
		);

		$row = Tx_Phpunit_Service_Database::selectSingle(
			'header',
			'tt_content',
			'uid = ' . $uid
		);

		$this->assertEquals(
			'Test header',
			$row['header']
		);
	}

	/**
     * @test
     */
    public function contentElementIsTextElementByDefault() {
		$uid = $this->fixture->createContentElement();

		$row = Tx_Phpunit_Service_Database::selectSingle(
			'CType',
			'tt_content',
			'uid = ' . $uid
		);

		$this->assertEquals(
			'text',
			$row['CType']
		);
	}

	/**
     * @test
     */
    public function contentElementCanHaveOtherType() {
		$uid = $this->fixture->createContentElement(
			0,
			array('CType' => 'list')
		);

		$row = Tx_Phpunit_Service_Database::selectSingle(
			'CType',
			'tt_content',
			'uid = ' . $uid
		);

		$this->assertEquals(
			'list',
			$row['CType']
		);
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function contentElementMustHaveNoZeroPid() {
		$this->fixture->createContentElement(0, array('pid' => 0));
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function contentElementMustHaveNoNonZeroPid() {
		$this->fixture->createContentElement(0, array('pid' => 99999));
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function contentElementMustHaveNoZeroUid() {
		$this->fixture->createContentElement(0, array('uid' => 0));
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function contentElementMustHaveNoNonZeroUid() {
		$this->fixture->createContentElement(0, array('uid' => 99999));
	}


	// ---------------------------------------------------------------------
	// Tests regarding createTemplate()
	// ---------------------------------------------------------------------

	/**
     * @test
     */
    public function templateCanBeCreatedOnNonRootPage() {
		$pageId = $this->fixture->createFrontEndPage();
		$uid = $this->fixture->createTemplate($pageId);

		$this->assertNotEquals(
			0,
			$uid
		);

		$this->assertEquals(
			1,
			$this->fixture->countRecords(
				'sys_template', 'uid=' . $uid
			)
		);
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function templateCannotBeCreatedOnRootPage() {
		$this->fixture->createTemplate(0);
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function templateCannotBeCreatedWithNegativePageNumber() {
		$this->fixture->createTemplate(-1);
	}

	/**
     * @test
     */
    public function templateCanBeDirty() {
		$this->assertEquals(
			0,
			count($this->fixture->getListOfDirtySystemTables())
		);

		$pageId = $this->fixture->createFrontEndPage();
		$uid = $this->fixture->createTemplate($pageId);
		$this->assertNotEquals(
			0,
			$uid
		);

		$this->assertNotEquals(
			0,
			count($this->fixture->getListOfDirtySystemTables())
		);
	}

	/**
     * @test
     */
    public function templateWillBeCleanedUp() {
		$pageId = $this->fixture->createFrontEndPage();
		$uid = $this->fixture->createTemplate($pageId);
		$this->assertNotEquals(
			0,
			$uid
		);

		$this->fixture->cleanUp();
		$this->assertEquals(
			0,
			$this->fixture->countRecords(
				'sys_template', 'uid=' . $uid
			)
		);
	}

	/**
     * @test
     */
    public function templateInitiallyHasNoConfig() {
		$pageId = $this->fixture->createFrontEndPage();
		$uid = $this->fixture->createTemplate($pageId);
		$row = Tx_Phpunit_Service_Database::selectSingle(
			'config',
			'sys_template',
			'uid = ' . $uid
		);

		$this->assertEquals(
			'',
			$row['config']
		);
	}

	/**
     * @test
     */
    public function templateCanHaveConfig() {
		$pageId = $this->fixture->createFrontEndPage();
		$uid = $this->fixture->createTemplate(
			$pageId,
			array('config' => 'plugin.tx_phpunit.test = 1')
		);
		$row = Tx_Phpunit_Service_Database::selectSingle(
			'config',
			'sys_template',
			'uid = ' . $uid
		);

		$this->assertEquals(
			'plugin.tx_phpunit.test = 1',
			$row['config']
		);
	}

	/**
     * @test
     */
    public function templateInitiallyHasNoConstants() {
		$pageId = $this->fixture->createFrontEndPage();
		$uid = $this->fixture->createTemplate($pageId);
		$row = Tx_Phpunit_Service_Database::selectSingle(
			'constants',
			'sys_template',
			'uid = ' . $uid
		);

		$this->assertEquals(
			'',
			$row['constants']
		);
	}

	/**
     * @test
     */
    public function templateCanHaveConstants() {
		$pageId = $this->fixture->createFrontEndPage();
		$uid = $this->fixture->createTemplate(
			$pageId,
			array('constants' => 'plugin.tx_phpunit.test = 1')
		);
		$row = Tx_Phpunit_Service_Database::selectSingle(
			'constants',
			'sys_template',
			'uid = ' . $uid
		);

		$this->assertEquals(
			'plugin.tx_phpunit.test = 1',
			$row['constants']
		);
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function templateMustNotHaveAZeroPid() {
		$this->fixture->createTemplate(42, array('pid' => 0));
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function templateMustNotHaveANonZeroPid() {
		$this->fixture->createTemplate(42, array('pid' => 99999));
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function templateMustHaveNoZeroUid() {
		$this->fixture->createTemplate(42, array('uid' => 0));
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function templateMustNotHaveANonZeroUid() {
		$this->fixture->createTemplate(42, array('uid' => 99999));
	}


	// ---------------------------------------------------------------------
	// Tests regarding createDummyFile()
	// ---------------------------------------------------------------------

	/**
     * @test
     */
    public function createDummyFileCreatesFile() {
		$dummyFile = $this->fixture->createDummyFile();

		$this->assertTrue(file_exists($dummyFile));
	}

	/**
     * @test
     */
    public function createDummyFileCreatesFileInSubfolder() {
		$dummyFolder = $this->fixture->createDummyFolder('test_folder');
		$dummyFile = $this->fixture->createDummyFile(
			$this->fixture->getPathRelativeToUploadDirectory($dummyFolder) .
				'/test.txt'
		);

		$this->assertTrue(file_exists($dummyFile));
	}

	/**
     * @test
     */
    public function createDummyFileCreatesFileWithTheProvidedContent() {
		$dummyFile = $this->fixture->createDummyFile('test.txt', 'Hello world!');

		$this->assertEquals('Hello world!', file_get_contents($dummyFile));
	}

	/**
     * @test
     */
    public function createDummyFileForNonExistentUploadFolderSetCreatesUploadFolder() {
		$this->fixture->setUploadFolderPath(PATH_site . 'typo3temp/tx_phpunit_test/');
		$this->fixture->createDummyFile();

		$this->assertTrue(is_dir($this->fixture->getUploadFolderPath()));
	}

	/**
     * @test
     */
    public function createDummyFileForNonExistentUploadFolderSetCreatesFileInCreatedUploadFolder() {
		$this->fixture->setUploadFolderPath(PATH_site . 'typo3temp/tx_phpunit_test/');
		$dummyFile = $this->fixture->createDummyFile();

		$this->assertTrue(file_exists($dummyFile));
	}


	// ---------------------------------------------------------------------
	// Tests regarding createDummyZipArchive()
	// ---------------------------------------------------------------------

	/**
     * @test
     */
    public function createDummyZipArchiveForNoContentProvidedCreatesZipArchive() {
		$this->markAsSkippedForNoZipArchive();

		$dummyFile = $this->fixture->createDummyZipArchive();

		$this->assertTrue(file_exists($dummyFile));
	}

	/**
     * @test
     */
    public function createDummyZipArchiveForFileNameInSubFolderProvidedCreatesZipArchiveInSubFolder() {
		$this->markAsSkippedForNoZipArchive();

		$this->fixture->setUploadFolderPath(PATH_site . 'typo3temp/tx_phpunit_test/');
		$dummyFolder = $this->fixture->getPathRelativeToUploadDirectory(
			$this->fixture->createDummyFolder('sub-folder')
		);
		$this->fixture->createDummyZipArchive($dummyFolder . 'foo.zip');

		$this->assertTrue(
			file_exists($this->fixture->getUploadFolderPath() . $dummyFolder . 'foo.zip')
		);
	}

	/**
     * @test
     */
    public function createDummyZipArchiveForNoContentProvidedCreatesZipArchiveWithDummyFile() {
		$this->markAsSkippedForNoZipArchive();

		$this->fixture->setUploadFolderPath(PATH_site . 'typo3temp/tx_phpunit_test/');
		$dummyFile = $this->fixture->createDummyZipArchive();
		$zip = new ZipArchive();
		$zip->open($dummyFile);
		$zip->extractTo($this->fixture->getUploadFolderPath());
		$zip->close();

		$this->assertTrue(
			file_exists($this->fixture->getUploadFolderPath() . 'test.txt')
		);
	}

	/**
     * @test
     */
    public function createDummyZipArchiveForFileProvidedCreatesZipArchiveWithThatFile() {
		$this->markAsSkippedForNoZipArchive();

		$this->fixture->setUploadFolderPath(PATH_site . 'typo3temp/tx_phpunit_test/');
		$dummyFile = $this->fixture->createDummyZipArchive(
			'foo.zip', array($this->fixture->createDummyFile('bar.txt'))
		);
		$zip = new ZipArchive();
		$zip->open($dummyFile);
		$zip->extractTo($this->fixture->getUploadFolderPath());
		$zip->close();

		$this->assertTrue(
			file_exists($this->fixture->getUploadFolderPath() . 'bar.txt')
		);
	}

	/**
     * @test
     */
    public function createDummyZipArchiveForFileProvidedWithContentCreatesZipArchiveWithThatFileAndContentInIt() {
		$this->markAsSkippedForNoZipArchive();

		$this->fixture->setUploadFolderPath(PATH_site . 'typo3temp/tx_phpunit_test/');
		$dummyFile = $this->fixture->createDummyZipArchive(
			'foo.zip', array($this->fixture->createDummyFile('bar.txt', 'foo bar'))
		);
		$zip = new ZipArchive();
		$zip->open($dummyFile);
		$zip->extractTo($this->fixture->getUploadFolderPath());
		$zip->close();

		$this->assertEquals(
			'foo bar',
			file_get_contents($this->fixture->getUploadFolderPath() . 'bar.txt')
		);
	}

	/**
     * @test
     */
    public function createDummyZipArchiveForTwoFilesProvidedCreatesZipArchiveWithTheseFiles() {
		$this->markAsSkippedForNoZipArchive();

		$this->fixture->setUploadFolderPath(PATH_site . 'typo3temp/tx_phpunit_test/');
		$dummyFile = $this->fixture->createDummyZipArchive(
			'foo.zip', array(
				$this->fixture->createDummyFile('foo.txt'),
				$this->fixture->createDummyFile('bar.txt'),
			)
		);
		$zip = new ZipArchive();
		$zip->open($dummyFile);
		$zip->extractTo($this->fixture->getUploadFolderPath());
		$zip->close();

		$this->assertTrue(
			file_exists($this->fixture->getUploadFolderPath() . 'foo.txt')
		);
		$this->assertTrue(
			file_exists($this->fixture->getUploadFolderPath() . 'bar.txt')
		);
	}

	/**
     * @test
     */
    public function createDummyZipArchiveForFileInSubFolderOfUploadFolderProvidedCreatesZipArchiveWithFileInSubFolder() {
		$this->markAsSkippedForNoZipArchive();

		$this->fixture->setUploadFolderPath(PATH_site . 'typo3temp/tx_phpunit_test/');
		$this->fixture->createDummyFolder('sub-folder');
		$dummyFile = $this->fixture->createDummyZipArchive(
			'foo.zip', array($this->fixture->createDummyFile('sub-folder/foo.txt'))
		);
		$zip = new ZipArchive();
		$zip->open($dummyFile);
		$zip->extractTo($this->fixture->getUploadFolderPath());
		$zip->close();

		$this->assertTrue(
			file_exists($this->fixture->getUploadFolderPath() . 'sub-folder/foo.txt')
		);
	}

	/**
     * @test
     */
    public function createDummyZipArchiveForNonExistentUploadFolderSetCreatesUploadFolder() {
		$this->markAsSkippedForNoZipArchive();

		$this->fixture->setUploadFolderPath(PATH_site . 'typo3temp/tx_phpunit_test/');
		$this->fixture->createDummyZipArchive();

		$this->assertTrue(is_dir($this->fixture->getUploadFolderPath()));
	}

	/**
     * @test
     */
    public function createDummyZipArchiveForNonExistentUploadFolderSetCreatesFileInCreatedUploadFolder() {
		$this->markAsSkippedForNoZipArchive();

		$this->fixture->setUploadFolderPath(PATH_site . 'typo3temp/tx_phpunit_test/');
		$dummyFile = $this->fixture->createDummyZipArchive();

		$this->assertTrue(file_exists($dummyFile));
	}


	// ---------------------------------------------------------------------
	// Tests regarding deleteDummyFile()
	// ---------------------------------------------------------------------

	/**
     * @test
     */
    public function deleteDummyFileDeletesCreatedDummyFile() {
		$dummyFile = $this->fixture->createDummyFile();
		$this->fixture->deleteDummyFile(basename($dummyFile));

		$this->assertFalse(file_exists($dummyFile));
	}

	/**
     * @test
     */
    public function deleteDummyFileWithAlreadyDeletedFileThrowsNoException() {
		$dummyFile = $this->fixture->createDummyFile();
		unlink($dummyFile);

		$this->fixture->deleteDummyFile(basename($dummyFile));
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function deleteDummyFileWithInexistentFileThrowsException() {
		$uniqueFileName = $this->fixture->getUniqueFileOrFolderPath('test.txt');

		$this->fixture->deleteDummyFile(basename($uniqueFileName));
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function deleteDummyFileWithForeignFileThrowsException() {
		$uniqueFileName = $this->fixture->getUniqueFileOrFolderPath('test.txt');
		t3lib_div::writeFile($uniqueFileName, '');
		$this->foreignFileToDelete = $uniqueFileName;

		$this->fixture->deleteDummyFile(basename($uniqueFileName));
	}


	// ---------------------------------------------------------------------
	// Tests regarding createDummyFolder()
	// ---------------------------------------------------------------------

	/**
     * @test
     */
    public function createDummyFolderCreatesFolder() {
		$dummyFolder = $this->fixture->createDummyFolder('test_folder');

		$this->assertTrue(is_dir($dummyFolder));
	}

	/**
     * @test
     */
    public function createDummyFolderCanCreateFolderInDummyFolder() {
		$outerDummyFolder = $this->fixture->createDummyFolder('test_folder');
		$innerDummyFolder = $this->fixture->createDummyFolder(
			$this->fixture->getPathRelativeToUploadDirectory($outerDummyFolder) .
				'/test_folder'
		);

		$this->assertTrue(is_dir($innerDummyFolder));
	}

	/**
     * @test
     */
    public function createDummyFolderForNonExistentUploadFolderSetCreatesUploadFolder() {
		$this->fixture->setUploadFolderPath(PATH_site . 'typo3temp/tx_phpunit_test/');
		$this->fixture->createDummyFolder('test_folder');

		$this->assertTrue(is_dir($this->fixture->getUploadFolderPath()));
	}

	/**
     * @test
     */
    public function createDummyFolderForNonExistentUploadFolderSetCreatesFileInCreatedUploadFolder() {
		$this->fixture->setUploadFolderPath(PATH_site . 'typo3temp/tx_phpunit_test/');
		$dummyFolder = $this->fixture->createDummyFolder('test_folder');

		$this->assertTrue(is_dir($dummyFolder));
	}


	// ---------------------------------------------------------------------
	// Tests regarding deleteDummyFolder()
	// ---------------------------------------------------------------------

	/**
     * @test
     */
    public function deleteDummyFolderDeletesCreatedDummyFolder() {
		$dummyFolder = $this->fixture->createDummyFolder('test_folder');
		$this->fixture->deleteDummyFolder(
			$this->fixture->getPathRelativeToUploadDirectory($dummyFolder)
		);

		$this->assertFalse(is_dir($dummyFolder));
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function deleteDummyFolderWithInexistentFolderThrowsException() {
		$uniqueFolderName = $this->fixture->getUniqueFileOrFolderPath('test_folder');

		$this->fixture->deleteDummyFolder(
			$this->fixture->getPathRelativeToUploadDirectory($uniqueFolderName)
		);
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function deleteDummyFolderWithForeignFolderThrowsException() {
		$uniqueFolderName = $this->fixture->getUniqueFileOrFolderPath('test_folder');
		t3lib_div::mkdir($uniqueFolderName);
		$this->foreignFolderToDelete = $uniqueFolderName;

		$this->fixture->deleteDummyFolder(basename($uniqueFolderName));
	}

	/**
     * @test
     */
    public function deleteDummyFolderCanDeleteCreatedDummyFolderInDummyFolder() {
		$outerDummyFolder = $this->fixture->createDummyFolder('test_folder');
		$innerDummyFolder = $this->fixture->createDummyFolder(
			$this->fixture->getPathRelativeToUploadDirectory($outerDummyFolder) .
				'/test_folder'
		);

		$this->fixture->deleteDummyFolder(
			$this->fixture->getPathRelativeToUploadDirectory($innerDummyFolder)
		);

		$this->assertFalse(file_exists($innerDummyFolder));
		$this->assertTrue(file_exists($outerDummyFolder));
	}

	/**
     * @test
     *
     * @expectedException t3lib_exception
     */
    public function deleteDummyFolderWithNonEmptyDummyFolderThrowsException() {
		$dummyFolder = $this->fixture->createDummyFolder('test_folder');
		$this->fixture->createDummyFile(
			$this->fixture->getPathRelativeToUploadDirectory($dummyFolder) .
				'/test.txt'
		);

		$this->fixture->deleteDummyFolder(
			$this->fixture->getPathRelativeToUploadDirectory($dummyFolder)
		);
	}

	/**
     * @test
     */
    public function deleteDummyFolderWithFolderNameConsistingOnlyOfNumbersDoesNotThrowAnException() {
		$dummyFolder = $this->fixture->createDummyFolder('123');

		$this->fixture->deleteDummyFolder(
			$this->fixture->getPathRelativeToUploadDirectory($dummyFolder)
		);
	}


	// ---------------------------------------------------------------------
	// Tests regarding set- and getUploadFolderPath()
	// ---------------------------------------------------------------------

	/**
     * @test
     */
    public function getUploadFolderPathReturnsUploadFolderPathIncludingTablePrefix() {
		$this->assertRegExp(
			'/\/uploads\/tx_phpunit\/$/',
			$this->fixture->getUploadFolderPath()
		);
	}

	/**
     * @test
     */
    public function getUploadFolderPathAfterSetReturnsSetUploadFolderPath() {
		$this->fixture->setUploadFolderPath('/foo/bar/');

		$this->assertEquals(
			'/foo/bar/',
			$this->fixture->getUploadFolderPath()
		);
	}

	/**
     * @test
     *
     * @expectedException t3lib_exception
     */
    public function setUploadFolderPathAfterCreatingADummyFileThrowsException() {
		$this->fixture->createDummyFile();
		$this->fixture->setUploadFolderPath('/foo/bar/');
	}


	// ---------------------------------------------------------------------
	// Tests regarding getPathRelativeToUploadDirectory()
	// ---------------------------------------------------------------------

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function getPathRelativeToUploadDirectoryWithPathOutsideUploadDirectoryThrowsException() {
		$this->fixture->getPathRelativeToUploadDirectory(PATH_site);
	}


	// ---------------------------------------------------------------------
	// Tests regarding getUniqueFileOrFolderPath()
	// ---------------------------------------------------------------------

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function getUniqueFileOrFolderPathWithEmptyPathThrowsException() {
		$this->fixture->getUniqueFileOrFolderPath('');
	}


	// ---------------------------------------------------------------------
	// Tests regarding createFrontEndUserGroup()
	// ---------------------------------------------------------------------

	/**
     * @test
     */
    public function frontEndUserGroupCanBeCreated() {
		$uid = $this->fixture->createFrontEndUserGroup();

		$this->assertNotEquals(
			0,
			$uid
		);

		$this->assertEquals(
			1,
			$this->fixture->countRecords(
				'fe_groups', 'uid=' . $uid
			)
		);
	}

	/**
     * @test
     */
    public function frontEndUserGroupTableCanBeDirty() {
		$this->assertEquals(
			0,
			count($this->fixture->getListOfDirtySystemTables())
		);
		$uid = $this->fixture->createFrontEndUserGroup();
		$this->assertNotEquals(
			0,
			$uid
		);

		$this->assertNotEquals(
			0,
			count($this->fixture->getListOfDirtySystemTables())
		);
	}

	/**
     * @test
     */
    public function frontEndUserGroupTableWillBeCleanedUp() {
		$uid = $this->fixture->createFrontEndUserGroup();
		$this->assertNotEquals(
			0,
			$uid
		);

		$this->fixture->cleanUp();
		$this->assertEquals(
			0,
			$this->fixture->countRecords(
				'fe_groups', 'uid=' . $uid
			)
		);
	}

	/**
     * @test
     */
    public function frontEndUserGroupHasNoTitleByDefault() {
		$uid = $this->fixture->createFrontEndUserGroup();

		$row = Tx_Phpunit_Service_Database::selectSingle(
			'title',
			'fe_groups',
			'uid = ' . $uid
		);

		$this->assertEquals(
			'',
			$row['title']
		);
	}

	/**
     * @test
     */
    public function frontEndUserGroupCanHaveATitle() {
		$uid = $this->fixture->createFrontEndUserGroup(
			array('title' => 'Test title')
		);

		$row = Tx_Phpunit_Service_Database::selectSingle(
			'title',
			'fe_groups',
			'uid = ' . $uid
		);

		$this->assertEquals(
			'Test title',
			$row['title']
		);
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function frontEndUserGroupMustHaveNoZeroUid() {
		$this->fixture->createFrontEndUserGroup(array('uid' => 0));
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function frontEndUserGroupMustHaveNoNonZeroUid() {
		$this->fixture->createFrontEndUserGroup(array('uid' => 99999));
	}


	// ---------------------------------------------------------------------
	// Tests regarding createFrontEndUser()
	// ---------------------------------------------------------------------

	/**
     * @test
     */
    public function frontEndUserCanBeCreated() {
		$uid = $this->fixture->createFrontEndUser();

		$this->assertNotEquals(
			0,
			$uid
		);

		$this->assertEquals(
			1,
			$this->fixture->countRecords(
				'fe_users', 'uid=' . $uid
			)
		);
	}

	/**
     * @test
     */
    public function frontEndUserTableCanBeDirty() {
		$this->assertEquals(
			0,
			count($this->fixture->getListOfDirtySystemTables())
		);
		$uid = $this->fixture->createFrontEndUser();
		$this->assertNotEquals(
			0,
			$uid
		);

		$this->greaterThan(
			1,
			count($this->fixture->getListOfDirtySystemTables())
		);
	}

	/**
     * @test
     */
    public function frontEndUserTableWillBeCleanedUp() {
		$uid = $this->fixture->createFrontEndUser();
		$this->assertNotEquals(
			0,
			$uid
		);

		$this->fixture->cleanUp();
		$this->assertEquals(
			0,
			$this->fixture->countRecords(
				'fe_users', 'uid=' . $uid
			)
		);
	}

	/**
     * @test
     */
    public function frontEndUserHasNoUserNameByDefault() {
		$uid = $this->fixture->createFrontEndUser();

		$row = Tx_Phpunit_Service_Database::selectSingle(
			'username',
			'fe_users',
			'uid = ' . $uid
		);

		$this->assertEquals(
			'',
			$row['username']
		);
	}

	/**
     * @test
     */
    public function frontEndUserCanHaveAUserName() {
		$uid = $this->fixture->createFrontEndUser(
			'',
			array('username' => 'Test name')
		);

		$row = Tx_Phpunit_Service_Database::selectSingle(
			'username',
			'fe_users',
			'uid = ' . $uid
		);

		$this->assertEquals(
			'Test name',
			$row['username']
		);
	}

	/**
     * @test
     */
    public function frontEndUserCanHaveSeveralUserGroups() {
		$feUserGroupUidOne = $this->fixture->createFrontEndUserGroup();
		$feUserGroupUidTwo = $this->fixture->createFrontEndUserGroup();
		$feUserGroupUidThree = $this->fixture->createFrontEndUserGroup();
		$uid = $this->fixture->createFrontEndUser(
			$feUserGroupUidOne.', '.$feUserGroupUidTwo.', '.$feUserGroupUidThree
		);

		$this->assertNotEquals(
			0,
			$uid
		);

		$this->assertEquals(
			1,
			$this->fixture->countRecords(
				'fe_users', 'uid=' . $uid
			)
		);
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function frontEndUserMustHaveNoZeroUid() {
		$this->fixture->createFrontEndUser('', array('uid' => 0));
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function frontEndUserMustHaveNoNonZeroUid() {
		$this->fixture->createFrontEndUser('', array('uid' => 99999));
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function frontEndUserMustHaveNoZeroUserGroupInTheDataArray() {
		$this->fixture->createFrontEndUser('', array('usergroup' => 0));
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function frontEndUserMustHaveNoNonZeroUserGroupInTheDataArray() {
		$this->fixture->createFrontEndUser('', array('usergroup' => 99999));
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function frontEndUserMustHaveNoUserGroupListInTheDataArray() {
		$this->fixture->createFrontEndUser(
			'', array('usergroup' => '1,2,4,5')
		);
	}

	/**
     * @test
     */
    public function createFrontEndUserWithEmptyGroupCreatesGroup() {
		$this->fixture->createFrontEndUser('');

		$this->assertTrue(
			$this->fixture->existsExactlyOneRecord('fe_groups')
		);
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function frontEndUserMustHaveNoZeroUserGroupEvenIfSeveralGroupsAreProvided() {
		$feUserGroupUidOne = $this->fixture->createFrontEndUserGroup();
		$feUserGroupUidTwo = $this->fixture->createFrontEndUserGroup();
		$feUserGroupUidThree = $this->fixture->createFrontEndUserGroup();

		$this->fixture->createFrontEndUser(
			$feUserGroupUidOne.', '.$feUserGroupUidTwo.', 0, '.$feUserGroupUidThree
		);
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function frontEndUserMustHaveNoAlphabeticalCharactersInTheUserGroupList() {
		$feUserGroupUid = $this->fixture->createFrontEndUserGroup();

		$this->fixture->createFrontEndUser(
			$feUserGroupUid.', abc'
		);
	}


	// ---------------------------------------------------------------------
	// Tests regarding createBackEndUser()
	// ---------------------------------------------------------------------

	/**
     * @test
     */
    public function createBackEndUserReturnsUidGreaterZero() {
		$this->assertNotEquals(
			0,
			$this->fixture->createBackEndUser()
		);
	}

	/**
     * @test
     */
    public function createBackEndUserCreatesBackEndUserRecordInTheDatabase() {
		$this->assertEquals(
			1,
			$this->fixture->countRecords(
				'be_users', 'uid=' . $this->fixture->createBackEndUser()
			)
		);
	}

	/**
     * @test
     */
    public function createBackEndUserMarksBackEndUserTableAsDirty() {
		$this->assertEquals(
			0,
			count($this->fixture->getListOfDirtySystemTables())
		);
		$this->fixture->createBackEndUser();

		$this->greaterThan(
			1,
			count($this->fixture->getListOfDirtySystemTables())
		);
	}

	/**
	 * @test
	 */
	public function cleanUpCleansUpDirtyBackEndUserTable() {
		$uid = $this->fixture->createBackEndUser();

		$this->fixture->cleanUp();
		$this->assertEquals(
			0,
			$this->fixture->countRecords('be_users', 'uid=' . $uid)
		);
	}

	/**
     * @test
     */
    public function createBackEndUserCreatesRecordWithoutUserNameByDefault() {
		$uid = $this->fixture->createBackEndUser();

		$row = Tx_Phpunit_Service_Database::selectSingle('username', 'be_users', 'uid = ' . $uid);

		$this->assertEquals(
			'',
			$row['username']
		);
	}

	/**
     * @test
     */
    public function createBackEndUserForUserNameProvidedCreatesRecordWithUserName() {
		$uid = $this->fixture->createBackEndUser(array('username' => 'Test name'));

		$row = Tx_Phpunit_Service_Database::selectSingle('username', 'be_users', 'uid = ' . $uid);

		$this->assertEquals(
			'Test name',
			$row['username']
		);
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function createBackEndUserWithZeroUidProvidedInRecordDataThrowsExeption() {
		$this->fixture->createBackEndUser(array('uid' => 0));
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function createBackEndUserWithNonZeroUidProvidedInRecordDataThrowsExeption() {
		$this->fixture->createBackEndUser(array('uid' => 999999));
	}


	// ---------------------------------------------------------------------
	// Tests concerning fakeFrontend
	// ---------------------------------------------------------------------

	/**
     * @test
     */
    public function createFakeFrontEndCreatesGlobalFrontEnd() {
		$GLOBALS['TSFE'] = NULL;
		$this->fixture->createFakeFrontEnd();

		$this->assertTrue(
			$GLOBALS['TSFE'] instanceof tslib_fe
		);
	}

	/**
     * @test
     */
    public function createFakeFrontEndReturnsPositivePageUidIfCalledWithoutParameters() {
		$this->assertGreaterThan(
			0,
			$this->fixture->createFakeFrontEnd()
		);
	}

	/**
     * @test
     */
    public function createFakeFrontEndReturnsCurrentFrontEndPageUid() {
		$GLOBALS['TSFE'] = NULL;
		$result = $this->fixture->createFakeFrontEnd();

		$this->assertEquals(
			$GLOBALS['TSFE']->id,
			$result
		);
	}

	/**
	 * @test
	 */
	public function createFakeFrontEndCreatesNullTimeTrackInstance() {
		$GLOBALS['TT'] = NULL;
		$this->fixture->createFakeFrontEnd();

		$this->assertTrue(
			$GLOBALS['TT'] instanceof t3lib_timeTrackNull
		);
	}

	/**
     * @test
     */
    public function createFakeFrontEndCreatesSysPage() {
		$GLOBALS['TSFE'] = NULL;
		$this->fixture->createFakeFrontEnd();

		$this->assertTrue(
			$GLOBALS['TSFE']->sys_page instanceof t3lib_pageSelect
		);
	}

	/**
     * @test
     */
    public function createFakeFrontEndCreatesFrontEndUser() {
		$GLOBALS['TSFE'] = NULL;
		$this->fixture->createFakeFrontEnd();

		$this->assertTrue(
			$GLOBALS['TSFE']->fe_user instanceof tslib_feUserAuth
		);
	}

	/**
     * @test
     */
    public function createFakeFrontEndCreatesContentObject() {
		$GLOBALS['TSFE'] = NULL;
		$this->fixture->createFakeFrontEnd();

		$this->assertTrue(
			$GLOBALS['TSFE']->cObj instanceof tslib_cObj
		);
	}

	/**
     * @test
     */
    public function createFakeFrontEndCreatesTemplate() {
		$GLOBALS['TSFE'] = NULL;
		$this->fixture->createFakeFrontEnd();

		$this->assertTrue(
			$GLOBALS['TSFE']->tmpl instanceof t3lib_TStemplate
		);
	}

	/**
     * @test
     */
    public function createFakeFrontEndReadsTypoScriptSetupFromPage() {
		$pageUid = $this->fixture->createFrontEndPage();
		$this->fixture->createTemplate(
			$pageUid,
			array('config' => 'foo = 42')
		);

		$this->fixture->createFakeFrontEnd($pageUid);

		$this->assertEquals(
			42,
			$GLOBALS['TSFE']->tmpl->setup['foo']
		);
	}

	/**
	 * @test
	 */
	public function createFakeFrontEndWithTemplateRecordMarksTemplateAsLoaded() {
		$pageUid = $this->fixture->createFrontEndPage();
		$this->fixture->createTemplate(
			$pageUid,
			array('config' => 'foo = 42')
		);

		$this->fixture->createFakeFrontEnd($pageUid);

		$this->assertEquals(
			1,
			$GLOBALS['TSFE']->tmpl->loaded
		);
	}

	/**
     * @test
     */
    public function createFakeFrontEndCreatesConfiguration() {
		$GLOBALS['TSFE'] = NULL;
		$this->fixture->createFakeFrontEnd();

		$this->assertTrue(
			is_array($GLOBALS['TSFE']->config)
		);
	}

	/**
	 * @test
	 */
	public function loginUserIsZeroAfterCreateFakeFrontEnd() {
		$this->fixture->createFakeFrontEnd();

		$this->assertEquals(
			0,
			$GLOBALS['TSFE']->loginUser
		);
	}

	/**
	 * @test
	 */
	public function createFakeFrontEndSetsDefaultGroupList() {
		$this->fixture->createFakeFrontEnd();

		$this->assertEquals(
			'0,-1',
			$GLOBALS['TSFE']->gr_list
		);
	}

	/**
     * @test
     */
    public function discardFakeFrontEndNullsOutGlobalFrontEnd() {
		$this->fixture->createFakeFrontEnd();
		$this->fixture->discardFakeFrontEnd();

		$this->assertNull(
			$GLOBALS['TSFE']
		);
	}

	/**
     * @test
     */
    public function discardFakeFrontEndNullsOutGlobalTimeTrack() {
		$this->fixture->createFakeFrontEnd();
		$this->fixture->discardFakeFrontEnd();

		$this->assertNull(
			$GLOBALS['TT']
		);
	}

	/**
     * @test
     */
    public function discardFakeFrontEndCanBeCalledTwoTimesInARow() {
		$this->fixture->discardFakeFrontEnd();
		$this->fixture->discardFakeFrontEnd();
	}

	/**
     * @test
     */
    public function hasFakeFrontEndInitiallyIsFalse() {
		$this->assertFalse(
			$this->fixture->hasFakeFrontEnd()
		);
	}

	/**
     * @test
     */
    public function hasFakeFrontEndIsTrueAfterCreateFakeFrontEnd() {
		$this->fixture->createFakeFrontEnd();

		$this->assertTrue(
			$this->fixture->hasFakeFrontEnd()
		);
	}

	/**
     * @test
     */
    public function hasFakeFrontEndIsFalseAfterCreateAndDiscardFakeFrontEnd() {
		$this->fixture->createFakeFrontEnd();
		$this->fixture->discardFakeFrontEnd();

		$this->assertFalse(
			$this->fixture->hasFakeFrontEnd()
		);
	}

	/**
	 * @test
	 */
	public function cleanUpDiscardsFakeFrontEnd() {
		$this->fixture->createFakeFrontEnd();
		$this->fixture->cleanUp();

		$this->assertFalse(
			$this->fixture->hasFakeFrontEnd()
		);
	}

	/**
     * @test
     */
    public function createFakeFrontEndReturnsProvidedPageUid() {
		$pageUid = $this->fixture->createFrontEndPage();

		$this->assertEquals(
			$pageUid,
			$this->fixture->createFakeFrontEnd($pageUid)
		);
	}

	/**
     * @test
     */
    public function createFakeFrontEndUsesProvidedPageUidAsFrontEndId() {
		$pageUid = $this->fixture->createFrontEndPage();
		$this->fixture->createFakeFrontEnd($pageUid);

		$this->assertEquals(
			$pageUid,
			$GLOBALS['TSFE']->id
		);
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function createFakeFrontThrowsExceptionForNegativePageUid() {
		$this->fixture->createFakeFrontEnd(-1);
	}

	// Note: In the unit tests, the src attribute of the generated image tag
	// will be empty because the IMAGE handles does not accept absolute paths
	// and handles relative paths and EXT: paths inconsistently:
	//
	// It correctly resolves paths which are relative to the TYPO3 document
	// root, but then calls t3lib_stdGraphic::getImageDimensions (which is
	// inherited by tslib_gifBuilder) which again uses the relative path. So
	// IMAGE will use the path to the TYPO3 root (which is the same as relative
	// to the FE index.php), but getImageDimensions use the path relative to the
	// executed script which is the FE index.php or the PHPUnit BE module
	// index.php. This results getImageDimensions not returning anything useful.
	/**
     * @test
     */
    public function fakeFrontEndCObjImageCreatesImageTagForExistingImageFile() {
		$this->fixture->createFakeFrontEnd();

		$this->assertContains(
			'<img ',
			$GLOBALS['TSFE']->cObj->IMAGE(
				array('file' => 'typo3conf/ext/phpunit/Tests/Fixtures/test.png')
			)
		);
	}


	// ---------------------------------------------------------------------
	// Tests regarding user login and logout
	// ---------------------------------------------------------------------

	/**
     * @test
     */
    public function isLoggedInInitiallyIsFalse() {
		$this->fixture->createFakeFrontEnd();

		$this->assertFalse(
			$this->fixture->isLoggedIn()
		);
	}

	/**
     * @test
     *
     * @expectedException t3lib_exception
     */
    public function isLoggedThrowsExceptionWithoutFrontEnd() {
		$this->fixture->isLoggedIn();
	}

	/**
     * @test
     */
    public function loginFrontEndUserSwitchesToLoggedIn() {
		$this->fixture->createFakeFrontEnd();

		$feUserId = $this->fixture->createFrontEndUser();
		$this->fixture->loginFrontEndUser($feUserId);

		$this->assertTrue(
			$this->fixture->isLoggedIn()
		);
	}

	/**
	 * @test
	 */
	public function loginFrontEndUserSwitchesLoginManagerToLoggedIn() {
		$this->fixture->createFakeFrontEnd();

		$feUserId = $this->fixture->createFrontEndUser();
		$this->fixture->loginFrontEndUser($feUserId);

		$this->assertTrue(
			$this->fixture->isLoggedIn()
		);
	}

	/**
     * @test
     */
    public function loginFrontEndUserSetsLoginUserToOne() {
		$this->fixture->createFakeFrontEnd();

		$feUserId = $this->fixture->createFrontEndUser();
		$this->fixture->loginFrontEndUser($feUserId);

		$this->assertEquals(
			1,
			$GLOBALS['TSFE']->loginUser
		);
	}

	/**
     * @test
     */
    public function loginFrontEndUserRetrievesNameOfUser() {
		$this->fixture->createFakeFrontEnd();

		$feUserId = $this->fixture->createFrontEndUser(
			'', array('name' => 'John Doe')
		);
		$this->fixture->loginFrontEndUser($feUserId);

		$this->assertEquals(
			'John Doe',
			$GLOBALS['TSFE']->fe_user->user['name']
		);
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function loginFrontEndUserWithZeroUidThrowsException() {
		$this->fixture->createFakeFrontEnd();

		$this->fixture->loginFrontEndUser(0);
	}

	/**
     * @test
     *
     * @expectedException t3lib_exception
     */
    public function loginFrontEndUserWithoutFrontEndThrowsException() {
		$feUserId = $this->fixture->createFrontEndUser();
		$this->fixture->loginFrontEndUser($feUserId);
	}

	/**
     * @test
     */
    public function loginFrontEndUserSetsGroupDataOfUser() {
		$this->fixture->createFakeFrontEnd();

		$feUserGroupUid = $this->fixture->createFrontEndUserGroup(
			array('title' => 'foo')
		);
		$feUserId = $this->fixture->createFrontEndUser($feUserGroupUid);
		$this->fixture->loginFrontEndUser($feUserId);

		$this->assertEquals(
			array($feUserGroupUid => 'foo'),
			$GLOBALS['TSFE']->fe_user->groupData['title']
		);
	}

	/**
     * @test
     */
    public function logoutFrontEndUserAfterLoginSwitchesToNotLoggedIn() {
		$this->fixture->createFakeFrontEnd();

		$feUserId = $this->fixture->createFrontEndUser();
		$this->fixture->loginFrontEndUser($feUserId);
		$this->fixture->logoutFrontEndUser();

		$this->assertFalse(
			$this->fixture->isLoggedIn()
		);
	}

	/**
	 * @test
	 */
	public function logoutFrontEndUserAfterLoginSwitchesLoginManagerToNotLoggedIn() {
		$this->fixture->createFakeFrontEnd();

		$feUserId = $this->fixture->createFrontEndUser();
		$this->fixture->loginFrontEndUser($feUserId);
		$this->fixture->logoutFrontEndUser();

		$this->assertFalse(
			$this->fixture->isLoggedIn()
		);
	}

	/**
     * @test
     */
    public function logoutFrontEndUserSetsLoginUserToZero() {
		$this->fixture->createFakeFrontEnd();

		$this->fixture->logoutFrontEndUser();

		$this->assertEquals(
			0,
			$GLOBALS['TSFE']->loginUser
		);
	}

	/**
     * @test
     *
     * @expectedException t3lib_exception
     */
    public function logoutFrontEndUserWithoutFrontEndThrowsException() {
		$this->fixture->logoutFrontEndUser();
	}

	/**
     * @test
     */
    public function logoutFrontEndUserCanBeCalledTwoTimesInARow() {
		$this->fixture->createFakeFrontEnd();

		$this->fixture->logoutFrontEndUser();
		$this->fixture->logoutFrontEndUser();
	}

	/**
     * @test
     */
    public function createAndLogInFrontEndUserCreatesFrontEndUser() {
		$this->fixture->createFakeFrontEnd();
		$this->fixture->createAndLogInFrontEndUser();

		$this->assertEquals(
			1,
			$this->fixture->countRecords('fe_users')
		);
	}

	/**
     * @test
     */
    public function createAndLogInFrontEndUserWithRecordDataCreatesFrontEndUserWithThatData() {
		$this->fixture->createFakeFrontEnd();
		$this->fixture->createAndLogInFrontEndUser(
			'', array('name' => 'John Doe')
		);

		$this->assertEquals(
			1,
			$this->fixture->countRecords('fe_users', 'name = "John Doe"')
		);
	}

	/**
     * @test
     */
    public function createAndLogInFrontEndUserLogsInFrontEndUser() {
		$this->fixture->createFakeFrontEnd();
		$this->fixture->createAndLogInFrontEndUser();

		$this->assertTrue(
			$this->fixture->isLoggedIn()
		);
	}

	/**
     * @test
     */
    public function createAndLogInFrontEndUserWithFrontEndUserGroupCreatesFrontEndUser() {
		$this->fixture->createFakeFrontEnd();
		$frontEndUserGroupUid = $this->fixture->createFrontEndUserGroup();
		$this->fixture->createAndLogInFrontEndUser($frontEndUserGroupUid);

		$this->assertEquals(
			1,
			$this->fixture->countRecords('fe_users')
		);
	}

	/**
     * @test
     */
    public function createAndLogInFrontEndUserWithFrontEndUserGroupCreatesFrontEndUserWithGivenGroup() {
		$this->fixture->createFakeFrontEnd();
		$frontEndUserGroupUid = $this->fixture->createFrontEndUserGroup();
		$frontEndUserUid = $this->fixture->createAndLogInFrontEndUser(
			$frontEndUserGroupUid
		);

		$dbResultRow = Tx_Phpunit_Service_Database::selectSingle(
			'usergroup',
			'fe_users',
			'uid = ' . $frontEndUserUid
		);

		$this->assertEquals(
			$frontEndUserGroupUid,
			$dbResultRow['usergroup']
		);
	}

	/**
     * @test
     */
    public function createAndLogInFrontEndUserWithFrontEndUserGroupDoesNotCreateAFrontEndUserGroup() {
		$this->fixture->createFakeFrontEnd();
		$frontEndUserGroupUid = $this->fixture->createFrontEndUserGroup();
		$this->fixture->createAndLogInFrontEndUser(
			$frontEndUserGroupUid
		);

		$this->assertEquals(
			1,
			$this->fixture->countRecords('fe_groups')
		);
	}

	/**
     * @test
     */
    public function createAndLogInFrontEndUserWithFrontEndUserGroupLogsInFrontEndUser() {
		$this->fixture->createFakeFrontEnd();
		$frontEndUserGroupUid = $this->fixture->createFrontEndUserGroup();
		$this->fixture->createAndLogInFrontEndUser($frontEndUserGroupUid);

		$this->assertTrue(
			$this->fixture->isLoggedIn()
		);
	}


	// ---------------------------------------------------------------------
	// Tests regarding increaseRelationCounter()
	// ---------------------------------------------------------------------

	/**
     * @test
     */
    public function increaseRelationCounterIncreasesNonZeroFieldValueByOne() {
		$uid = $this->fixture->createRecord(
			'tx_phpunit_test',
			array('related_records' => 41)
		);

		$this->fixture->increaseRelationCounter(
			'tx_phpunit_test',
			$uid,
			'related_records'
		);

		$row = Tx_Phpunit_Service_Database::selectSingle(
			'related_records',
			'tx_phpunit_test',
			'uid = ' . $uid
		);

		$this->assertEquals(
			42,
			$row['related_records']
		);
	}

	/**
     * @test
     *
     * @expectedException Tx_Phpunit_Exception_Database
     */
    public function increaseRelationCounterThrowsExceptionOnInvalidUid() {
		$uid = $this->fixture->createRecord('tx_phpunit_test');
		$invalidUid = $uid + 1;

		$this->fixture->increaseRelationCounter(
			'tx_phpunit_test',
			$invalidUid,
			'related_records'
		);
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function increaseRelationCounterThrowsExceptionOnInvalidTableName() {
		$uid = $this->fixture->createRecord('tx_phpunit_test');

		$this->fixture->increaseRelationCounter(
			'tx_phpunit_inexistent',
			$uid,
			'related_records'
		);
	}

	/**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function increaseRelationCounterThrowsExceptionOnInexistentFieldName() {
		$uid = $this->fixture->createRecord('tx_phpunit_test');
		$this->fixture->increaseRelationCounter(
			'tx_phpunit_test',
			$uid,
			'inexistent_column'
		);
	}

	/**
	 * @test
	 */
	public function getDummyColumnNameForExtensionTableReturnsDummyColumnName() {
		$this->assertEquals(
			'is_dummy_record',
			$this->fixture->getDummyColumnName('tx_phpunit_test')
		);
	}

	/**
	 * @test
	 */
	public function getDummyColumnNameForSystemTableReturnsPhpUnitPrefixedColumnName() {
		$this->assertEquals(
			'tx_phpunit_is_dummy_record',
			$this->fixture->getDummyColumnName('fe_users')
		);
	}

	/**
	 * @test
	 */
	public function getDummyColumnNameForThirdPartyExtensionTableReturnsPrefixedColumnName() {
		$testingFramework = new Tx_Phpunit_Framework(
			'user_phpunittest', array('user_phpunittest2')
		);
		$this->assertEquals(
			'user_phpunittest_is_dummy_record',
			$testingFramework->getDummyColumnName('user_phpunittest2_test')
		);
	}


	////////////////////////////////////////////
	// Tests concerning createBackEndUserGroup
	////////////////////////////////////////////

	/**
	 * @test
	 */
	public function createBackEndUserGroupForNoDataGivenCreatesBackEndGroup() {
		$this->fixture->createBackEndUserGroup(array());

		$this->assertTrue(
			$this->fixture->existsRecord('be_groups')
		);
	}

	/**
	 * @test
	 */
	public function createBackEndUserGroupForNoDataGivenReturnsUidOfCreatedBackEndGroup() {
		$backendGroupUid = $this->fixture->createBackEndUserGroup(array());

		$this->assertTrue(
			$this->fixture->existsRecord(
				'be_groups', 'uid = ' . $backendGroupUid
			)
		);
	}

	/**
	 * @test
	 */
	public function createBackEndUserGroupForTitleGivenStoresTitleInGroupRecord() {
		$this->fixture->createBackEndUserGroup(
			array('title' => 'foo group')
		);

		$this->assertTrue(
			$this->fixture->existsRecord(
				'be_groups', 'title = "foo group"'
			)
		);
	}
}
?>