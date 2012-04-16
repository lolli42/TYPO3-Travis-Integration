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

// In the back end, PATH_tslib isn't defined yet.
if (!defined('PATH_tslib')) {
	define('PATH_tslib', t3lib_extMgm::extPath('cms') . 'tslib/');
}

/**
 * This class provides various functions to handle dummy records in unit tests.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Mario Rimann <typo3-coding@rimann.org>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Saskia Metzler <saskia@merlin.owl.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Phpunit_Framework {
	/**
	 * prefix of the extension for which this instance of the testing framework
	 * was instantiated (e.g. "tx_seminars")
	 *
	 * @var string
	 */
	protected $tablePrefix = '';

	/**
	 * prefixes of additional extensions to which this instance of the testing
	 * framework has access (e.g. "tx_seminars")
	 *
	 * @var array
	 */
	protected $additionalTablePrefixes = array();

	/**
	 * all own DB table names to which this instance of the testing framework
	 * has access
	 *
	 * @var array
	 */
	protected $ownAllowedTables = array();

	/**
	 * all additional DB table names to which this instance of the testing
	 * framework has access
	 *
	 * @var array
	 */
	protected $additionalAllowedTables = array();

	/**
	 * all sytem table names to which this instance of the testing framework
	 * has access
	 *
	 * @var array
	 */
	protected $allowedSystemTables = array(
		'be_users', 'fe_groups', 'fe_users', 'pages', 'sys_template',
		'tt_content', 'be_groups'
	);

	/**
	 * all "dirty" non-system tables (i.e. all tables that were used for testing
	 * and need to be cleaned up)
	 *
	 * @var array
	 */
	protected $dirtyTables = array();

	/**
	 * all "dirty" system tables (i.e. all tables that were used for testing and
	 * need to be cleaned up)
	 *
	 * @var array
	 */
	protected $dirtySystemTables = array();

	/**
	 * sorting values of all relation tables
	 *
	 * @var array
	 */
	protected $relationSorting = array();

	/**
	 * the number of unusable UIDs after the maximum UID in a table before the
	 * auto increment value will be reset by resetAutoIncrementLazily
	 *
	 * @var integer
	 */
	protected $resetAutoIncrementThreshold = 100;

	/**
	 * the names of the created dummy files relative to the upload folder of the
	 * extension to test
	 *
	 * @var array
	 */
	protected $dummyFiles = array();

	/**
	 * the names of the created dummy folders relative to theupload folder of
	 * the extension to test
	 *
	 * @var array
	 */
	protected $dummyFolders = array();

	/**
	 * the absolute path to the upload folder of the extension to test
	 *
	 * @var string
	 */
	protected $uploadFolderPath = '';

	/**
	 * an instance used for retrieving a unique file name
	 *
	 * @var t3lib_basicFileFunctions
	 */
	protected static $fileNameProcessor = NULL;

	/**
	 * whether a fake front end has been created
	 *
	 * @var boolean
	 */
	protected $hasFakeFrontEnd = FALSE;

	/**
	 * hook objects for this class
	 *
	 * @var array
	 */
	static protected $hooks = array();

	/**
	 * whether the hooks in self::hooks have been retrieved
	 *
	 * @var boolean
	 */
	static protected $hooksHaveBeenRetrieved = FALSE;

	/**
	 * The constructor for this class.
	 *
	 * This testing framework can be instantiated for one extension at a time.
	 * Example: In your testcase, you'll have something similar to this line of code:
	 * $this->fixture = new Tx_Phpunit_Framework('tx_seminars');
	 * The parameter you provide is the prefix of the table names of that particular
	 * extension. Like this, we ensure that the testing framework creates and
	 * deletes records only on table with this prefix.
	 *
	 * If you need dummy records on tables of multiple extensions, you'll have to
	 * instantiate the testing frame work multiple times (once per extension).
	 *
	 * @param string $tablePrefix
	 *        the table name prefix of the extension for which this instance of
	 *        the testing framework should be used
	 * @param array $additionalTablePrefixes
	 *        the additional table name prefixes of the extensions for which
	 *        this instance of the testing framework should be used, may be empty
	 */
	public function __construct($tablePrefix, array $additionalTablePrefixes = array()) {
		$this->tablePrefix = $tablePrefix;
		$this->additionalTablePrefixes = $additionalTablePrefixes;
		$this->createListOfOwnAllowedTables();
		$this->createListOfAdditionalAllowedTables();
		$this->uploadFolderPath = PATH_site . 'uploads/' . $this->tablePrefix . '/';
	}

	/**
	 * Creates a new dummy record for unit tests.
	 *
	 * If no record data for the new array is given, an empty record will be
	 * created. It will only contain a valid UID and the "is_dummy_record" flag
	 * will be set to 1.
	 *
	 * Should there be any problem creating the record (wrong table name or a
	 * problem with the database), 0 instead of a valid UID will be returned.
	 *
	 * @param string $tableName
	 *        the name of the table on which the record should be created, must
	 *        not be empty
	 * @param array $recordData
	 *        associative array that contains the data to save in the new
	 *        record, may be empty, but must not contain the key "uid"
	 *
	 * @return integer the UID of the new record, will be > 0
	 */
	public function createRecord($tableName, array $recordData = array()) {
		if (!$this->isNoneSystemTableNameAllowed($tableName)) {
			throw new InvalidArgumentException('The table name "' . $tableName . '" is not allowed.');
		}
		if (isset($recordData['uid'])) {
			throw new InvalidArgumentException('The column "uid" must not be set in $recordData.');
		}

		return $this->createRecordWithoutTableNameChecks($tableName, $recordData);
	}

	/**
	 * Creates a new dummy record for unit tests without checks for the table
	 * name.
	 *
	 * If no record data for the new array is given, an empty record will be
	 * created. It will only contain a valid UID and the "is_dummy_record" flag
	 * will be set to 1.
	 *
	 * Should there be any problem creating the record (wrong table name or a
	 * problem with the database), 0 instead of a valid UID will be returned.
	 *
	 * @param string $tableName
	 *        the name of the table on which the record should be created, must
	 *        not be empty
	 * @param array $recordData
	 *        associative array that contains the data to save in the new
	 *        record, may be empty, but must not contain the key "uid"
	 *
	 * @return integer the UID of the new record, will be > 0
	 */
	protected function createRecordWithoutTableNameChecks($tableName, array $recordData) {
		$dummyColumnName = $this->getDummyColumnName($tableName);
		$recordData[$dummyColumnName] = 1;

		$uid = Tx_Phpunit_Service_Database::insert($tableName, $recordData);
		$this->markTableAsDirty($tableName);

		return $uid;
	}

	/**
	 * Creates a front-end page on the page with the UID given by the first
	 * parameter $parentId.
	 *
	 * @param integer $parentId
	 *        UID of the page on which the page should be created
	 * @param array $recordData
	 *        associative array that contains the data to save in the new page,
	 *        may be empty, but must not contain the keys "uid", "pid" or "doktype"
	 *
	 * @return integer the UID of the new page, will be > 0
	 */
	public function createFrontEndPage($parentId = 0, array $recordData = array()) {
		return $this->createGeneralPageRecord(1, $parentId, $recordData);
	}

	/**
	 * Creates a system folder on the page with the UID given by the first
	 * parameter $parentId.
	 *
	 * @param integer $parentId
	 *        UID of the page on which the system folder should be created
	 * @param array $recordData
	 *        associative array that contains the data to save in the new page,
	 *        may be empty, but must not contain the keys "uid", "pid" or "doktype"
	 *
	 * @return integer the UID of the new system folder, will be > 0
	 */
	public function createSystemFolder($parentId = 0, array $recordData = array()) {
		return $this->createGeneralPageRecord(254, $parentId, $recordData);
	}

	/**
	 * Creates a page record with the document type given by the first parameter
	 * $documentType.
	 *
	 * The record will be created on the page with the UID given by the second
	 * parameter $parentId.
	 *
	 * @param integer $documentType
	 *        document type of the record to create, must be > 0
	 * @param integer $parentId
	 *        UID of the page on which the record should be created
	 * @param array $recordData
	 *        associative array that contains the data to save in the record,
	 *        may be empty, but must not contain the keys "uid", "pid" or "doktype"
	 *
	 * @return integer the UID of the new record, will be > 0
	 */
	protected function createGeneralPageRecord($documentType, $parentId, array $recordData) {
		if (isset($recordData['uid'])) {
			throw new InvalidArgumentException('The column "uid" must not be set in $recordData.');
		}
		if (isset($recordData['pid'])) {
			throw new InvalidArgumentException('The column "pid" must not be set in $recordData.');
		}
		if (isset($recordData['doktype'])) {
			throw new InvalidArgumentException('The column "doktype" must not be set in $recordData.');
		}

		$completeRecordData = $recordData;
		$completeRecordData['pid'] = $parentId;
		$completeRecordData['doktype'] = $documentType;

		return $this->createRecordWithoutTableNameChecks('pages', $completeRecordData);
	}

	/**
	 * Creates a FE content element on the page with the UID given by the first
	 * parameter $pageId.
	 *
	 * Created content elements are text elements by default, but the content
	 * element's type can be overwritten by setting the key 'CType' in the
	 * parameter $recordData.
	 *
	 * @param integer $pageId
	 *        UID of the page on which the content element should be created
	 * @param array $recordData
	 *        associative array that contains the data to save in the content
	 *        element, may be empty, but must not contain the keys "uid" or "pid"
	 *
	 * @return integer the UID of the new content element, will be > 0
	 */
	public function createContentElement($pageId = 0, array $recordData = array()) {
		if (isset($recordData['uid'])) {
			throw new InvalidArgumentException('The column "uid" must not be set in $recordData.');
		}
		if (isset($recordData['pid'])) {
			throw new InvalidArgumentException('The column "pid" must not be set in $recordData.');
		}

		$completeRecordData = $recordData;
		$completeRecordData['pid'] = $pageId;
		if (!isset($completeRecordData['CType'])) {
			$completeRecordData['CType'] = 'text';
		}

		return $this->createRecordWithoutTableNameChecks('tt_content', $completeRecordData);
	}

	/**
	 * Creates a template on the page with the UID given by the first parameter
	 * $pageId.
	 *
	 * @param integer $pageId
	 *        UID of the page on which the template should be created, must be > 0
	 * @param array $recordData
	 *        associative array that contains the data to save in the new
	 *        template, may be empty, but must not contain the keys "uid" or "pid"
	 *
	 * @return integer the UID of the new template, will be > 0
	 */
	public function createTemplate($pageId, array $recordData = array()) {
		if ($pageId <= 0) {
			throw new InvalidArgumentException('$pageId must be > 0.');
		}
		if (isset($recordData['uid'])) {
			throw new InvalidArgumentException('The column "uid" must not be set in $recordData.');
		}
		if (isset($recordData['pid'])) {
			throw new InvalidArgumentException('The column "pid" must not be set in $recordData.');
		}

		$completeRecordData = $recordData;
		$completeRecordData['pid'] = $pageId;

		return $this->createRecordWithoutTableNameChecks('sys_template', $completeRecordData);
	}

	/**
	 * Creates a FE user group.
	 *
	 * @param array $recordData
	 *        associative array that contains the data to save in the new user
	 *        group record, may be empty, but must not contain the key "uid"
	 *
	 * @return integer the UID of the new user group, will be > 0
	 */
	public function createFrontEndUserGroup(array $recordData = array()) {
		if (isset($recordData['uid'])) {
			throw new InvalidArgumentException('The column "uid" must not be set in $recordData.');
		}

		return $this->createRecordWithoutTableNameChecks('fe_groups', $recordData);
	}

	/**
	 * Creates a FE user record.
	 *
	 * @param string $frontEndUserGroups
	 *        comma-separated list of UIDs of the user groups to which the new
	 *        user belongs, each must be > 0, may contain spaces, if empty a new
	 *        FE user group will be created
	 * @param array $recordData
	 *        associative array that contains the data to save in the new user
	 *        record, may be empty, but must not contain the keys "uid" or
	 *        "usergroup"
	 *
	 * @return integer the UID of the new FE user, will be > 0
	 */
	public function createFrontEndUser(
		$frontEndUserGroups = '', array $recordData = array()
	) {
		$frontEndUserGroupsWithoutSpaces = str_replace(' ', '', $frontEndUserGroups);

		if ($frontEndUserGroupsWithoutSpaces === '') {
			$frontEndUserGroupsWithoutSpaces = $this->createFrontEndUserGroup();
		}
		if (!preg_match('/^(?:[1-9]+[0-9]*,?)+$/', $frontEndUserGroupsWithoutSpaces)
		) {
			throw new InvalidArgumentException(
				'$frontEndUserGroups must contain a comma-separated list of UIDs. Each UID must be > 0.'
			);
		}
		if (isset($recordData['uid'])) {
			throw new InvalidArgumentException('The column "uid" must not be set in $recordData.');
		}
		if (isset($recordData['usergroup'])) {
			throw new InvalidArgumentException('The column "usergroup" must not be set in $recordData.');
		}

		$completeRecordData = $recordData;
		$completeRecordData['usergroup'] = $frontEndUserGroupsWithoutSpaces;

		return $this->createRecordWithoutTableNameChecks('fe_users', $completeRecordData);
	}

	/**
	 * Creates and logs in an FE user.
	 *
	 * @param string $frontEndUserGroups
	 *        comma-separated list of UIDs of the user groups to which the new
	 *        user belongs, each must be > 0, may contain spaces; if empty a new
	 *        front-end user group is created
	 * @param array $recordData
	 *        associative array that contains the data to save in the new user
	 *        record, may be empty, but must not contain the keys "uid" or
	 *        "usergroup"
	 *
	 * @return integer the UID of the new FE user, will be > 0
	 */
	public function createAndLoginFrontEndUser($frontEndUserGroups = '', array $recordData = array()) {
		$frontEndUserUid = $this->createFrontEndUser($frontEndUserGroups, $recordData);

		$this->loginFrontEndUser($frontEndUserUid);

		return $frontEndUserUid;
	}

	/**
	 * Creates a BE user record.
	 *
	 * @param array $recordData
	 *        associative array that contains the data to save in the new user
	 *        record, may be empty, but must not contain the key "uid"
	 *
	 * @return integer the UID of the new BE user, will be > 0
	 */
	public function createBackEndUser(array $recordData = array()) {
		if (isset($recordData['uid'])) {
			throw new InvalidArgumentException('The column "uid" must not be set in $recordData.');
		}

		return $this->createRecordWithoutTableNameChecks('be_users', $recordData);
	}

	/**
	 * Creates a BE user group.
	 *
	 * @param array $recordData
	 *        associative array that contains the data to save in the new user
	 *        group record, may be empty, but must not contain the key "uid"
	 *
	 * @return integer the UID of the new user group, will be > 0
	 */
	public function createBackEndUserGroup(array $recordData = array()) {
		if (isset($recordData['uid'])) {
			throw new InvalidArgumentException('The column "uid" must not be set in $recordData.');
		}

		return $this->createRecordWithoutTableNameChecks('be_groups', $recordData);
	}

	/**
	 * Changes an existing dummy record and stores the new data for this
	 * record. Only fields that get new values in $recordData will be changed,
	 * everything else will stay untouched.
	 *
	 * The array with the new recordData must contain at least one entry, but
	 * must not contain a new UID for the record. If you need to change the UID,
	 * you have to create a new record!
	 *
	 * @param string $tableName
	 *        the name of the table, must not be empty
	 * @param integer $uid
	 *        the UID of the record to change, must not be empty
	 * @param array $recordData
	 *        associative array containing key => value pairs for those fields
	 *        of the record that need to be changed, must not be empty
	 *
	 * @return void
	 */
	public function changeRecord($tableName, $uid, array $recordData) {
		$dummyColumnName = $this->getDummyColumnName($tableName);

		if (!$this->isTableNameAllowed($tableName)) {
			throw new InvalidArgumentException(
				'The table "' . $tableName . '" is not on the lists with allowed tables.'
			);
		}
		if ($uid === 0) {
			throw new InvalidArgumentException('The parameter $uid must not be zero.');
		}
		if (empty($recordData)) {
			throw new InvalidArgumentException('The array with the new record data must not be empty.');
		}
		if (isset($recordData['uid'])) {
			throw new InvalidArgumentException(
				'The parameter $recordData must not contain changes to the UID of a record.'
			);
		}
		if (isset($recordData[$dummyColumnName])) {
			throw new InvalidArgumentException(
				'The parameter $recordData must not contain changes to the ' .
					'field "' . $dummyColumnName . '". It is impossible to ' .
					'convert a dummy record into a regular record.'
			);
		}
		if (!$this->countRecords($tableName, 'uid='.$uid)) {
			throw new Tx_Phpunit_Exception_Database(
				'There is no record with UID ' . $uid . ' on table "' . $tableName . '".'
			);
		}

		Tx_Phpunit_Service_Database::update(
			$tableName,
			'uid = ' . $uid . ' AND ' . $dummyColumnName . ' = 1',
			$recordData
		);
	}

	/**
	 * Deletes a dummy record from the database.
	 *
	 * Important: Only dummy records from non-system tables can be deleted with
	 * this method. Should there for any reason exist a real record with that
	 * UID, it won't be deleted.
	 *
	 * @param string $tableName
	 *        name of the table from which the record should be deleted, must
	 *        not be empty
	 * @param integer $uid
	 *        UID of the record to delete, must be > 0
	 *
	 * @return void
	 */
	public function deleteRecord($tableName, $uid) {
		if (!$this->isNoneSystemTableNameAllowed($tableName)) {
			throw new InvalidArgumentException('The table name "' . $tableName . '" is not allowed.');
		}

		Tx_Phpunit_Service_Database::delete(
			$tableName,
			'uid = ' . $uid . ' AND ' . $this->getDummyColumnName($tableName) . ' = 1'
		);
	}

	/**
	 * Creates a relation between two records on different tables (so called
	 * m:n relation).
	 *
	 * @param string $tableName
	 *        name of the m:n table to which the record should be added, must
	 *        not be empty
	 * @param integer $uidLocal
	 *        UID of the local table, must be > 0
	 * @param integer $uidForeign
	 *        UID of the foreign table, must be > 0
	 * @param integer $sorting
	 *        sorting value of the relation, the default value is 0, which
	 *        enables automatic sorting, a value >= 0 overwrites the automatic
	 *        sorting
	 *
	 * @return void
	 */
	public function createRelation($tableName, $uidLocal, $uidForeign, $sorting = 0) {
		if (!$this->isNoneSystemTableNameAllowed($tableName)) {
			throw new InvalidArgumentException('The table name "' . $tableName . '" is not allowed.');
		}

		// Checks that the two given UIDs are valid.
		if (intval($uidLocal) <= 0) {
			throw new InvalidArgumentException(
				'$uidLocal must be an integer > 0, but actually is "' . $uidLocal . '"'
			);
		}
		if (intval($uidForeign) <= 0) {
			throw new InvalidArgumentException(
				'$uidForeign must be an integer > 0, but actually is "' . $uidForeign . '"'
			);
		}

		$this->markTableAsDirty($tableName);

		$recordData = array(
			'uid_local' => $uidLocal,
			'uid_foreign' => $uidForeign,
			'sorting' => (($sorting > 0) ? $sorting : $this->getRelationSorting($tableName, $uidLocal)),
			$this->getDummyColumnName($tableName) => 1,
		);

		Tx_Phpunit_Service_Database::insert($tableName, $recordData);
	}

	/**
	 * Creates a relation between two records based on the rules defined in TCA
	 * regarding the relation.
	 *
	 * @param string $tableName
	 *        name of the table from which a relation should be created, must
	 *        not be empty
	 * @param integer $uidLocal
	 *        UID of the record in the local table, must be > 0
	 * @param integer $uidForeign
	 *        UID of the record in the foreign table, must be > 0
	 * @param string $columnName
	 *        name of the column in which the relation counter should be
	 *        updated, must not be empty
	 *
	 * @return void
	 */
	public function createRelationAndUpdateCounter(
		$tableName, $uidLocal, $uidForeign, $columnName
	) {
		if (!$this->isTableNameAllowed($tableName)) {
			throw new InvalidArgumentException('The table name "' . $tableName . '" is not allowed.');
		}

		if ($uidLocal <= 0) {
			throw new InvalidArgumentException(
				'$uidLocal must be > 0, but actually is "' . $uidLocal . '"'
			);
		}
		if ($uidForeign <= 0) {
			throw new InvalidArgumentException(
				'$uidForeign must be  > 0, but actually is "' . $uidForeign . '"'
			);
		}

		$tca = Tx_Phpunit_Service_Database::getTcaForTable($tableName);
		$relationConfiguration = $tca['columns'][$columnName];

		if (!isset($relationConfiguration['config']['MM']) || ($relationConfiguration['config']['MM'] === '')) {
			throw new t3lib_exception(
				'The column ' . $columnName . ' in the table ' . $tableName .
				' is not configured to contain m:n relations using a m:n table.'
			);
		}

		if (!isset($relationConfiguration['config']['MM_opposite_field'])) {
			$this->createRelation(
				$relationConfiguration['config']['MM'],
				$uidLocal,
				$uidForeign
			);
		} else {
			// Switches the order of $uidForeign and $uidLocal as the relation
			// is the reverse part of a bidirectional relation.
			$this->createRelationAndUpdateCounter(
				$relationConfiguration['config']['foreign_table'],
				$uidForeign,
				$uidLocal,
				$relationConfiguration['config']['MM_opposite_field']
			);
		}

		$this->increaseRelationCounter($tableName, $uidLocal, $columnName);
	}

	/**
	 * Deletes a dummy relation from an m:n table in the database.
	 *
	 * Important: Only dummy records can be deleted with this method. Should there
	 * for any reason exist a real record with that combination of local and
	 * foreign UID, it won't be deleted!
	 *
	 * @param string $tableName
	 *        name of the table from which the record should be deleted, must
	 *        not be empty
	 * @param integer $uidLocal
	 *        UID on the local table, must be > 0
	 * @param integer $uidForeign
	 *        UID on the foreign table, must be > 0
	 *
	 * @return void
	 */
	public function removeRelation($tableName, $uidLocal, $uidForeign) {
		if (!$this->isNoneSystemTableNameAllowed($tableName)) {
			throw new InvalidArgumentException('The table name "' . $tableName . '" is not allowed.');
		}

		Tx_Phpunit_Service_Database::delete(
			$tableName,
			'uid_local = ' . $uidLocal . ' AND uid_foreign = ' . $uidForeign .
				' AND ' . $this->getDummyColumnName($tableName) . ' = 1'
		);
	}

	/**
	 * Deletes all dummy records that have been added through this framework.
	 * For this, all records with the "is_dummy_record" flag set to 1 will be
	 * deleted from all tables that have been used within this instance of the
	 * testing framework.
	 *
	 * If you set $performDeepCleanUp to TRUE, it will go through ALL tables to
	 * which the current instance of the testing framework has access. Please
	 * consider well, whether you want to do this as it's a huge performance
	 * issue.
	 *
	 * @param boolean $performDeepCleanUp
	 *        whether a deep clean up should be performed
	 *
	 * @return void
	 */
	public function cleanUp($performDeepCleanUp = FALSE) {
		$this->cleanUpTableSet(FALSE, $performDeepCleanUp);
		$this->cleanUpTableSet(TRUE, $performDeepCleanUp);
		$this->deleteAllDummyFoldersAndFiles();
		$this->discardFakeFrontEnd();

		foreach ($this->getHooks() as $hook) {
			if (!($hook instanceof Tx_Phpunit_Interface_FrameworkCleanupHook)) {
				throw new t3lib_exception(
					'The class ' . get_class($hook) . ' must implement Tx_Phpunit_Interface_FrameworkCleanupHook.',
					1299257923
				);
			}
			$hook->cleanUp();
		}
	}

	/**
	 * Deletes a set of records that have been added through this framework for
	 * a set of tables (either the test tables or the allowed system tables).
	 * For this, all records with the "is_dummy_record" flag set to 1 will be
	 * deleted from all tables that have been used within this instance of the
	 * testing framework.
	 *
	 * If you set $performDeepCleanUp to TRUE, it will go through ALL tables to
	 * which the current instance of the testing framework has access. Please
	 * consider well, whether you want to do this as it's a huge performance
	 * issue.
	 *
	 * @param boolean $useSystemTables
	 *        whether to clean up the system tables (TRUE) or the non-system
	 *        test tables (FALSE)
	 * @param boolean $performDeepCleanUp
	 *        whether a deep clean up should be performed
	 *
	 * @return void
	 */
	protected function cleanUpTableSet($useSystemTables, $performDeepCleanUp) {
		if ($useSystemTables) {
			$tablesToCleanUp = $performDeepCleanUp ? $this->allowedSystemTables : $this->dirtySystemTables;
		} else {
			$tablesToCleanUp = $performDeepCleanUp ? $this->ownAllowedTables : $this->dirtyTables;
		}

		foreach ($tablesToCleanUp as $currentTable) {
			$dummyColumnName = $this->getDummyColumnName($currentTable);

			// Runs a delete query for each allowed table. A
			// "one-query-deletes-them-all" approach was tested but we didn't
			// find a working solution for that.
			Tx_Phpunit_Service_Database::delete($currentTable, $dummyColumnName . ' = 1');

			// Resets the auto increment setting of the current table.
			$this->resetAutoIncrementLazily($currentTable);
		}

		// Resets the list of dirty tables.
		$this->dirtyTables = array();
	}

	/**
	 * Deletes all dummy files and folders.
	 *
	 * @return void
	 */
	protected function deleteAllDummyFoldersAndFiles() {
		// If the upload folder was created by the testing framework, it can be
		// removed at once.
		if (isset($this->dummyFolders['uploadFolder'])) {
			t3lib_div::rmdir($this->getUploadFolderPath(), TRUE);
			$this->dummyFolders = array();
			$this->dummyFiles = array();
		} else {
			foreach ($this->dummyFiles as $dummyFile) {
				$this->deleteDummyFile($dummyFile);
			}
			foreach ($this->dummyFolders as $dummyFolder) {
				$this->deleteDummyFolder($dummyFolder);
			}
		}
	}


	// ----------------------------------------------------------------------
	// File creation and deletion
	// ----------------------------------------------------------------------

	/**
	 * Creates an empty dummy file with a unique file name in the calling
	 * extension's upload directory.
	 *
	 * @param string $fileName
	 *        path of the dummy file to create, relative to the calling
	 *        extension's upload directory, must not be empty
	 * @param string $content
	 *        string content for the file to create, may be empty
	 *
	 * @return string
	 *         the absolute path of the created dummy file, will not be empty
	 */
	public function createDummyFile($fileName = 'test.txt', $content = '') {
		$this->createDummyUploadFolder();
		$uniqueFileName = $this->getUniqueFileOrFolderPath($fileName);

		if (!t3lib_div::writeFile($uniqueFileName, $content)) {
			throw new t3lib_exception('The file ' . $uniqueFileName . ' could not be created.');
		}

		$this->addToDummyFileList($uniqueFileName);

		return $uniqueFileName;
	}

	/**
	 * Creates a dummy ZIP archive with a unique file name in the calling
	 * extension's upload directory.
	 *
	 * @param string $fileName
	 *        path of the dummy ZIP archive to create, relative to the calling
	 *        extension's upload directory, must not be empty
	 * @param array $filesToAddToArchive
	 *        Absolute paths of the files to add to the ZIP archive. Note that
	 *        the archives directory structure will be relative to the upload
	 *        folder path, so only files within this folder or in sub-folders of
	 *        this folder can be added.
	 *        The provided array may be empty, but as ZIP archives cannot be
	 *        empty, a content-less dummy text file will be added to the archive
	 *        then.
	 *
	 * @return string
	 *         the absolute path of the created dummy ZIP archive, will not be empty
	 *
	 * @throws t3lib_exception if the PHP installation does not provide ZIPArchive
	 */
	public function createDummyZipArchive($fileName = 'test.zip', array $filesToAddToArchive = array()) {
		$this->checkForZipArchive();

		$this->createDummyUploadFolder();
		$uniqueFileName = $this->getUniqueFileOrFolderPath($fileName);
		$zip = t3lib_div::makeInstance('ZipArchive');

		if ($zip->open($uniqueFileName, ZipArchive::CREATE) !== TRUE) {
			throw new t3lib_exception('The new ZIP archive "' . $fileName . '" could not be created.');
		}

		$contents = !empty($filesToAddToArchive) ? $filesToAddToArchive : array($this->createDummyFile());

		foreach ($contents as $pathToFile) {
			if (!file_exists($pathToFile)) {
				throw new t3lib_exception(
					'The provided path "' . $pathToFile . '" does not point to an exisiting file.'
				);
			}
			$zip->addFile($pathToFile, $this->getPathRelativeToUploadDirectory($pathToFile));
        }

		$zip->close();
		$this->addToDummyFileList($uniqueFileName);

		return $uniqueFileName;
	}

	/**
	 * Adds a file name to $this->dummyFiles.
	 *
	 * @param string $uniqueFileName
	 *        file name to add, must be the unique name of a dummy file, must
	 *        not be empty
	 *
	 * @return void
	 */
	protected function addToDummyFileList($uniqueFileName) {
		$relativeFileName = $this->getPathRelativeToUploadDirectory($uniqueFileName);

		$this->dummyFiles[$relativeFileName] = $relativeFileName;
	}

	/**
	 * Deletes the dummy file specified by the first parameter $fileName.
	 *
	 * @param string $fileName
	 *        the path to the file to delete relative to
	 *        $this->uploadFolderPath, must not be empty
	 *
	 * @return void
	 */
	public function deleteDummyFile($fileName) {
		$absolutePathToFile = $this->uploadFolderPath . $fileName;
		$fileExists = file_exists($absolutePathToFile);

		if (!isset($this->dummyFiles[$fileName])) {
			throw new InvalidArgumentException(
				'The file "' . $absolutePathToFile . '" which you are trying to delete ' .
					(!$fileExists ? 'does not exist and has never been ' : 'was not ') .
					'created by this instance of the testing framework.'
			);
		}

		if ($fileExists && !unlink($absolutePathToFile)) {
			throw new t3lib_exception('The file "' . $absolutePathToFile . '" could not be deleted.');
		}

		unset($this->dummyFiles[$fileName]);
	}

	/**
	 * Creates a dummy folder with a unique folder name in the calling
	 * extension's upload directory.
	 *
	 * @param string $folderName
	 *        name of the dummy folder to create relative to
	 *        $this->uploadFolderPath, must not be empty
	 *
	 * @return string
	 *         the absolute path of the created dummy folder, will not be empty
	 */
	public function createDummyFolder($folderName) {
		$this->createDummyUploadFolder();
		$uniqueFolderName = $this->getUniqueFileOrFolderPath($folderName);

		if (!t3lib_div::mkdir($uniqueFolderName)) {
			throw new t3lib_exception('The folder ' . $uniqueFolderName . ' could not be created.');
		}

		$relativeUniqueFolderName = $this->getPathRelativeToUploadDirectory($uniqueFolderName);

		// Adds the created dummy folder to the top of $this->dummyFolders so
		// it gets deleted before previously created folders through
		// $this->cleanUpFolders(). This is needed for nested dummy folders.
		$this->dummyFolders = array($relativeUniqueFolderName => $relativeUniqueFolderName)
			+ $this->dummyFolders;

		return $uniqueFolderName;
	}

	/**
	 * Deletes the dummy folder specified in the first parameter $folderName.
	 * The folder must be empty (no files or subfolders).
	 *
	 * @param string $folderName
	 *        the path to the folder to delete relative to
	 *        $this->uploadFolderPath, must not be empty
	 *
	 * @return void
	 */
	public function deleteDummyFolder($folderName) {
		$absolutePathToFolder = $this->uploadFolderPath . $folderName;

		if (!is_dir($absolutePathToFolder)) {
			throw new InvalidArgumentException(
				'The folder "' . $absolutePathToFolder . '" which you ' .
					'are trying to delete does not exist.'
			);
		}

		if (!isset($this->dummyFolders[$folderName])) {
			throw new InvalidArgumentException(
				'The folder "' . $absolutePathToFolder . '" which you ' .
					'are trying to delete was not created by this instance of ' .
					'the testing framework.'
			);
		}

		if (!t3lib_div::rmdir($absolutePathToFolder)) {
			throw new t3lib_exception(
				'The folder "' . $absolutePathToFolder . '" could not be deleted.'
			);
		}

		unset($this->dummyFolders[$folderName]);
	}

	/**
	 * Creates the upload folder if it does not exist yet.
	 *
	 * @return void
	 */
	protected function createDummyUploadFolder() {
		if (is_dir($this->getUploadFolderPath())) {
			return;
		}

		if (t3lib_div::mkdir($this->getUploadFolderPath())) {
			// registers the upload folder as dummy folder
			$this->dummyFolders['uploadFolder'] = '';
		} else {
			throw new t3lib_exception(
				'The upload folder ' . $this->getUploadFolderPath() .
					' could not be created.'
			);
		}
	}

	/**
	 * Sets the upload folder path.
	 *
	 * @param string $absolutePath
	 *        absolute path to the folder where to work on during the tests, can
	 *        be either an existing folder which will be cleaned up after the
	 *        tests or a path of a folder to be created as soon as it is needed
	 *        and deleted during cleanUp, must end with a trailing slash
	 *
	 * @return void
	 *
	 * @throws t3lib_exception
	 *         if there are dummy files within the current upload folder as
	 *         these files could not be deleted if the upload folder path has
	 *         changed
	 */
	public function setUploadFolderPath($absolutePath) {
		if (!empty($this->dummyFiles) || !empty($this->dummyFolders)) {
			throw new t3lib_exception(
				'The upload folder path must not be changed if there are ' .
					'already dummy files or folders.'
			);
		}

		$this->uploadFolderPath = $absolutePath;
	}

	/**
	 * Returns the absolute path to the upload folder of the extension to test.
	 *
	 * @return string
	 *         the absolute path to the upload folder of the extension to test,
	 *         including the trailing slash
	 */
	public function getUploadFolderPath() {
		return $this->uploadFolderPath;
	}

	/**
	 * Returns the path relative to the calling extension's upload directory for
	 * a path given in the first parameter $absolutePath.
	 *
	 * @param string $absolutePath
	 *        the absolute path to process, must be within the calling
	 *        extension's upload directory, must not be empty
	 *
	 * @return string
	 *         the path relative to the calling extension's upload directory
	 */
	public function getPathRelativeToUploadDirectory($absolutePath) {
		if (!preg_match(
				'/^' . str_replace('/', '\/', $this->getUploadFolderPath()) . '.*$/',
				$absolutePath
		)) {
			throw new InvalidArgumentException(
				'The first parameter $absolutePath is not within the calling ' .
					'extension\'s upload directory.'
			);
		}

		return mb_substr(
			$absolutePath,
			mb_strlen($this->getUploadFolderPath())
		);
	}

	/**
	 * Returns a unique absolut path of a file or folder.
	 *
	 * @param string $path
	 *        the path of a file or folder relative to the calling extension's
	 *        upload directory, must not be empty
	 *
	 * @return string the unique absolut path of a file or folder
	 */
	public function getUniqueFileOrFolderPath($path) {
		if (empty($path)) {
			throw new InvalidArgumentException('The first parameter $path must not be empty.');
		}

		if (!self::$fileNameProcessor) {
			self::$fileNameProcessor = t3lib_div::makeInstance('t3lib_basicFileFunctions');
		}

		return self::$fileNameProcessor->getUniqueName(
			basename($path),
			$this->uploadFolderPath . t3lib_div::dirname($path)
		);
	}


	// ----------------------------------------------------------------------
	// Functions concerning a fake front end
	// ----------------------------------------------------------------------

	/**
	 * Fakes a TYPO3 front end, using $pageUid as front-end page ID if provided.
	 *
	 * If $pageUid is zero, the UID of the start page of the current domain
	 * will be used as page UID.
	 *
	 * This function creates $GLOBALS['TSFE'] and $GLOBALS['TT'].
	 *
	 * Note: This function does not set TYPO3_MODE to "FE" (because the value of
	 * a constant cannot be changed after it has once been set).
	 *
	 * @param integer $pageUid
	 *        UID of a page record to use, must be >= 0
	 *
	 * @return integer the UID of the used front-end page, will be > 0
	 */
	public function createFakeFrontEnd($pageUid = 0) {
		if ($pageUid < 0) {
			throw new InvalidArgumentException('$pageUid must be >= 0.');
		}

		$this->suppressFrontEndCookies();
		$this->discardFakeFrontEnd();

		$GLOBALS['TT'] = t3lib_div::makeInstance('t3lib_TimeTrackNull');

		$frontEnd = t3lib_div::makeInstance('tslib_fe', $GLOBALS['TYPO3_CONF_VARS'], $pageUid, 0);

		// simulates a normal FE without any logged-in FE or BE user
		$frontEnd->beUserLogin = FALSE;
		$frontEnd->workspacePreview = '';
		$frontEnd->initFEuser();
		$frontEnd->determineId();
		$frontEnd->initTemplate();
		$frontEnd->config = array();

		$frontEnd->tmpl->getFileName_backPath = PATH_site;

		if (($pageUid > 0) && in_array('sys_template', $this->dirtySystemTables)) {
			$frontEnd->tmpl->runThroughTemplates($frontEnd->sys_page->getRootLine($pageUid), 0);
			$frontEnd->tmpl->generateConfig();
			$frontEnd->tmpl->loaded = 1;
			$frontEnd->settingLanguage();
			$frontEnd->settingLocale();
		}

		$frontEnd->newCObj();

		$GLOBALS['TSFE'] = $frontEnd;

		$this->hasFakeFrontEnd = TRUE;
		$this->logoutFrontEndUser();

		return $GLOBALS['TSFE']->id;
	}

	/**
	 * Discards the fake front end.
	 *
	 * This function nulls out $GLOBALS['TSFE'] and $GLOBALS['TT']. In addition,
	 * any logged-in front-end user will be logged out.
	 *
	 * The page record for the current front end will _not_ be deleted by this
	 * function, though.
	 *
	 * If no fake front end has been created, this function does nothing.
	 *
	 * @return void
	 */
	public function discardFakeFrontEnd() {
		if (!$this->hasFakeFrontEnd()) {
			return;
		}

		$this->logoutFrontEndUser();

		unset(
			$GLOBALS['TSFE']->tmpl, $GLOBALS['TSFE']->sys_page,
			$GLOBALS['TSFE']->fe_user, $GLOBALS['TSFE']->TYPO3_CONF_VARS,
			$GLOBALS['TSFE']->config, $GLOBALS['TSFE']->TCAcachedExtras,
			$GLOBALS['TSFE']->imagesOnPage, $GLOBALS['TSFE']->cObj,
			$GLOBALS['TSFE']->csConvObj, $GLOBALS['TSFE']->pagesection_lockObj,
			$GLOBALS['TSFE']->pages_lockObj
		);
		$GLOBALS['TSFE'] = NULL;
		$GLOBALS['TT'] = NULL;

		$this->hasFakeFrontEnd = FALSE;
	}

	/**
	 * Returns whether this testing framework instance has a fake front end.
	 *
	 * @return boolean
	 *         TRUE if this instance has a fake front end, FALSE otherwise
	 */
	public function hasFakeFrontEnd() {
		return $this->hasFakeFrontEnd;
	}

	/**
	 * Makes sure that no FE login cookies will be sent.
	 *
	 * @return void
	 */
	protected function suppressFrontEndCookies() {
		$GLOBALS['_POST']['FE_SESSION_KEY'] = '';
		$GLOBALS['_GET']['FE_SESSION_KEY'] = '';
		$GLOBALS['TYPO3_CONF_VARS']['FE']['dontSetCookie'] = 1;
	}


	// ----------------------------------------------------------------------
	// FE user activities
	// ----------------------------------------------------------------------

	/**
	 * Fakes that a front-end user has logged in.
	 *
	 * If a front-end user currently is logged in, he/she will be logged out
	 * first.
	 *
	 * Note: To set the logged-in users group data properly, the front-end user
	 *       and his groups must actually exist in the database.
	 *
	 * @param integer $userId
	 *        UID of the FE user, must not necessarily exist in the database,
	 *        must be > 0
	 *
	 * @return void
	 *
	 * @throws t3lib_exception if no front end has been created
	 */
	public function loginFrontEndUser($userId) {
		if (intval($userId) === 0) {
			throw new InvalidArgumentException('The user ID must be > 0.');
		}
		if (!$this->hasFakeFrontEnd()) {
			throw new t3lib_exception('Please create a front end before calling loginFrontEndUser.');
		}

		if ($this->isLoggedIn()) {
			$this->logoutFrontEndUser();
		}

		$this->suppressFrontEndCookies();

		// Instead of passing the actual user data to createUserSession, we
		// pass an empty array to improve performance (e.g. no session record
		// will be written to the database).
		$GLOBALS['TSFE']->fe_user->createUserSession(array());
		$GLOBALS['TSFE']->fe_user->user = $GLOBALS['TSFE']->fe_user->getRawUserByUid($userId);
		$GLOBALS['TSFE']->fe_user->fetchGroupData();
		$GLOBALS['TSFE']->loginUser = 1;
	}

	/**
	 * Logs out the current front-end user.
	 *
	 * If no front-end user is logged in, this function does nothing.
	 *
	 * @throws t3lib_exception if no front end has been created
	 *
	 * @return void
	 */
	public function logoutFrontEndUser() {
		if (!$this->hasFakeFrontEnd()) {
			throw new t3lib_exception('Please create a front end before calling logoutFrontEndUser.');
		}
		if (!$this->isLoggedIn()) {
			return;
		}

		$this->suppressFrontEndCookies();

		$GLOBALS['TSFE']->fe_user->logoff();
		$GLOBALS['TSFE']->loginUser = 0;
	}

	/**
	 * Checks whether a FE user is logged in.
	 *
	 * @throws t3lib_exception if no front end has been created
	 *
	 * @return boolean TRUE if a FE user is logged in, FALSE otherwise
	 */
	public function isLoggedIn() {
		if (!$this->hasFakeFrontEnd()) {
			throw new t3lib_exception('Please create a front end before calling isLoggedIn.');
		}

		return isset($GLOBALS['TSFE']) && is_object($GLOBALS['TSFE'])
			&& is_array($GLOBALS['TSFE']->fe_user->user);
	}


	// ----------------------------------------------------------------------
	// Various helper functions
	// ----------------------------------------------------------------------

	/**
	 * Generates a list of allowed tables to which this instance of the testing
	 * framework has access to create/remove test records.
	 *
	 * The generated list is based on the list of all tables that TYPO3 can
	 * access (which will be all tables in this database), filtered by prefix of
	 * the extension to test.
	 *
	 * The array with the allowed table names is written directly to
	 * $this->ownAllowedTables.
	 *
	 * @return void
	 */
	protected function createListOfOwnAllowedTables() {
		$this->ownAllowedTables = array();
		$allTables = Tx_Phpunit_Service_Database::getAllTableNames();
		$length = strlen($this->tablePrefix);

		foreach ($allTables as $currentTable) {
			if (substr_compare($this->tablePrefix, $currentTable, 0, $length) === 0) {
				$this->ownAllowedTables[] = $currentTable;
			}
		}
	}

	/**
	 * Generates a list of additional allowed tables to which this instance of
	 * the testing framework has access to create/remove test records.
	 *
	 * The generated list is based on the list of all tables that TYPO3 can
	 * access (which will be all tables in this database), filtered by the
	 * prefixes of additional extensions.
	 *
	 * The array with the allowed table names is written directly to
	 * $this->additionalAllowedTables.
	 *
	 * @return void
	 */
	protected function createListOfAdditionalAllowedTables() {
		$allTables = implode(',', Tx_Phpunit_Service_Database::getAllTableNames());
		$additionalTablePrefixes = implode('|', $this->additionalTablePrefixes);

		$matches = array();

		preg_match_all(
			'/(('.$additionalTablePrefixes.')_[a-z0-9]+[a-z0-9_]*)(,|$)/', $allTables, $matches
		);

		if (isset($matches[1])) {
			$this->additionalAllowedTables = $matches[1];
		}
	}

	/**
	 * Checks whether the given table name is in the list of allowed tables for
	 * this instance of the testing framework.
	 *
	 * @param string $tableName
	 *        the name of the table to check, must not be empty
	 *
	 * @return boolean
	 *         TRUE if the name of the table is in the list of allowed tables,
	 *         FALSE otherwise
	 */
	protected function isOwnTableNameAllowed($tableName) {
		return in_array($tableName, $this->ownAllowedTables);
	}

	/**
	 * Checks whether the given table name is in the list of additional allowed
	 * tables for this instance of the testing framework.
	 *
	 * @param string $tableName
	 *        the name of the table to check, must not be empty
	 *
	 * @return boolean
	 *         TRUE if the name of the table is in the list of additional
	 *         allowed tables, FALSE otherwise
	 */
	protected function isAdditionalTableNameAllowed($tableName) {
		return in_array($tableName, $this->additionalAllowedTables);
	}

	/**
	 * Checks whether the given table name is in the list of allowed
	 * system tables for this instance of the testing framework.
	 *
	 * @param string $tableName
	 *        the name of the table to check, must not be empty
	 *
	 * @return boolean
	 *         TRUE if the name of the table is in the list of allowed system
	 *         tables, FALSE otherwise
	 */
	protected function isSystemTableNameAllowed($tableName) {
		return in_array($tableName, $this->allowedSystemTables);
	}

	/**
	 * Checks whether the given table name is in the list of allowed tables or
	 * additional allowed tables for this instance of the testing framework.
	 *
	 * @param string $tableName
	 *        the name of the table to check, must not be empty
	 *
	 * @return boolean
	 *         TRUE if the name of the table is in the list of allowed tables or
	 *         additional allowed tables, FALSE otherwise
	 */
	protected function isNoneSystemTableNameAllowed($tableName) {
		return $this->isOwnTableNameAllowed($tableName)
			|| $this->isAdditionalTableNameAllowed($tableName);
	}

	/**
	 * Checks whether the given table name is in the list of allowed tables,
	 * additional allowed tables or allowed system tables.
	 *
	 * @param string $tableName
	 *        the name of the table to check, must not be empty
	 *
	 * @return boolean
	 *         TRUE if the name of the table is in the list of allowed tables,
	 *         additional allowed tables or allowed system tables, FALSE otherwise
	 */
	protected function isTableNameAllowed($tableName) {
		return $this->isNoneSystemTableNameAllowed($tableName)
			|| $this->isSystemTableNameAllowed($tableName);
	}

	/**
	 * Returns the name of the column that marks a record as a dummy record.
	 *
	 * On most tables this is "is_dummy_record", but on system tables like
	 * "pages" or "fe_users", the column is called "tx_phpunit_dummy_record".
	 *
	 * On additional tables, the column is built using $this->tablePrefix as
	 * prefix e.g. "tx_seminars_is_dummy_record" if $this->tablePrefix =
	 * "tx_seminars".
	 *
	 * @param string $tableName
	 *        the table name to look up, must not be empty
	 *
	 * @return string the name of the column that marks a record as dummy record
	 */
	public function getDummyColumnName($tableName) {
		$result = 'is_dummy_record';

		if ($this->isSystemTableNameAllowed($tableName)) {
			$result = 'tx_phpunit_' . $result;
		} elseif ($this->isAdditionalTableNameAllowed($tableName)) {
			$result = $this->tablePrefix . '_' . $result;
		}

		return $result;
	}

	/**
	 * Counts the dummy records in the table given by the first parameter $tableName
	 * that match a given WHERE clause.
	 *
	 * @param string $tableName
	 *        the name of the table to query, must not be empty
	 * @param string $whereClause
	 *        the WHERE part of the query, may be empty (all records will be
	 *        counted in that case)
	 *
	 * @return integer the number of records that have been found, will be >= 0
	 */
	public function countRecords($tableName, $whereClause = '') {
		if (!$this->isTableNameAllowed($tableName)) {
			throw new InvalidArgumentException(
				'The given table name is invalid. This means it is either ' .
					'empty or not in the list of allowed tables.'
			);
		}

		$whereForDummyColumn = $this->getDummyColumnName($tableName) . ' = 1';
		$compoundWhereClause = ($whereClause !== '')
			? '(' . $whereClause . ') AND ' . $whereForDummyColumn : $whereForDummyColumn;

		return Tx_Phpunit_Service_Database::count($tableName, $compoundWhereClause);
	}

	/**
	 * Checks whether there are any dummy records in the table given by the
	 * first parameter $tableName that match a given WHERE clause.
	 *
	 * @param string $tableName
	 *        the name of the table to query, must not be empty
	 * @param string $whereClause
	 *        the WHERE part of the query, may be empty (all records will be
	 *        counted in that case)
	 *
	 * @return boolean
	 *         TRUE if there is at least one matching record, FALSE otherwise
	 */
	public function existsRecord($tableName, $whereClause = '') {
		return ($this->countRecords($tableName, $whereClause) > 0);
	}

	/**
	 * Checks whether there is a dummy record in the table given by the first
	 * parameter $tableName that has the given UID.
	 *
	 * @param string $tableName
	 *        the name of the table to query, must not be empty
	 * @param integer $uid
	 *        the UID of the record to look up, must be > 0
	 *
	 * @return boolean TRUE if there is a matching record, FALSE otherwise
	 */
	public function existsRecordWithUid($tableName, $uid) {
		if ($uid <= 0) {
			throw new InvalidArgumentException('$uid must be > 0.');
		}

		return ($this->countRecords($tableName, 'uid = ' . $uid) > 0);
	}

	/**
	 * Checks whether there is exactly one dummy record in the table given by
	 * the first parameter $tableName that matches a given WHERE clause.
	 *
	 * @param string $tableName
	 *        the name of the table to query, must not be empty
	 * @param string $whereClause
	 *        the WHERE part of the query, may be empty (all records will be
	 *        counted in that case)
	 *
	 * @return boolean
	 *         TRUE if there is exactly one matching record, FALSE otherwise
	 */
	public function existsExactlyOneRecord($tableName, $whereClause = '') {
		return ($this->countRecords($tableName, $whereClause) === 1);
	}

	/**
	 * Eagerly resets the auto increment value for a given table to the highest
	 * existing UID + 1.
	 *
	 * @param string $tableName
	 *        the name of the table on which we're going to reset the auto
	 *        increment entry, must not be empty
	 *
	 * @see resetAutoIncrementLazily
	 *
	 * @return void
	 */
	public function resetAutoIncrement($tableName) {
		if (!$this->isTableNameAllowed($tableName)) {
			throw new InvalidArgumentException(
				'The given table name is invalid. This means it is either ' .
					'empty or not in the list of allowed tables.'
			);
		}

		// Checks whether the current table qualifies for this method. If there
		// is no column "uid" that has the "auto_increment" flag set, we should
		// not try to reset this inexistent auto increment index to avoid DB
		// errors.
		if (!Tx_Phpunit_Service_Database::tableHasColumnUid($tableName)) {
			return;
		}

		$newAutoIncrementValue = $this->getMaximumUidFromTable($tableName) + 1;

		Tx_Phpunit_Service_Database::enableQueryLogging();
		// Updates the auto increment index for this table. The index will be
		// set to one UID above the highest existing UID.
		$dbResult = $GLOBALS['TYPO3_DB']->sql_query(
			'ALTER TABLE ' . $tableName . ' AUTO_INCREMENT=' . $newAutoIncrementValue . ';'
		);
		if (!$dbResult) {
			throw new Tx_Phpunit_Exception_Database();
		}
	}

	/**
	 * Resets the auto increment value for a given table to the highest existing
	 * UID + 1 if the current auto increment value is higher than a certain
	 * threshold over the current maximum UID.
	 *
	 * The threshhold is 100 by default and can be set using
	 * setResetAutoIncrementThreshold.
	 *
	 * @param string $tableName
	 *        the name of the table on which we're going to reset the auto
	 *        increment entry, must not be empty
	 *
	 * @see resetAutoIncrement
	 *
	 * @return void
	 */
	public function resetAutoIncrementLazily($tableName) {
		if (!$this->isTableNameAllowed($tableName)) {
			throw new InvalidArgumentException(
				'The given table name is invalid. This means it is either ' .
					'empty or not in the list of allowed tables.'
			);
		}

		// Checks whether the current table qualifies for this method. If there
		// is no column "uid" that has the "auto_increment" flag set, we should
		// not try to reset this inexistent auto increment index to avoid
		// database errors.
		if (!Tx_Phpunit_Service_Database::tableHasColumnUid($tableName)) {
			return;
		}

		if ($this->getAutoIncrement($tableName) > ($this->getMaximumUidFromTable($tableName) + $this->resetAutoIncrementThreshold)) {
			$this->resetAutoIncrement($tableName);
		}
	}

	/**
	 * Sets the threshold for resetAutoIncrementLazily.
	 *
	 * @param integer $threshold
	 *        threshold, must be > 0
	 *
	 * @see resetAutoIncrementLazily
	 *
	 * @return void
	 */
	public function setResetAutoIncrementThreshold($threshold) {
		if ($threshold <= 0) {
			throw new InvalidArgumentException('$threshold must be > 0.');
		}

		$this->resetAutoIncrementThreshold = $threshold;
	}

	/**
	 * Reads the highest UID for a database table.
	 *
	 * This function may only be called after that the provided table name
	 * has been checked to be non-empty, valid and pointing to an existing
	 * database table that has the "uid" column.
	 *
	 * @param string $tableName
	 *        the name of an existing table that has the "uid" column
	 *
	 * @return integer the highest UID from this table, will be >= 0
	 */
	protected function getMaximumUidFromTable($tableName) {
		$row = Tx_Phpunit_Service_Database::selectSingle('MAX(uid) AS uid', $tableName);

		return $row['uid'];
	}

	/**
	 * Reads the current auto increment value for a given table.
	 *
	 * This function is only valid for tables that actually have an auto
	 * increment value.
	 *
	 * @param string $tableName
	 *        the name of the table for which the auto increment value should be
	 *        retrieved, must not be empty
	 *
	 * @return integer
	 *         the current auto_increment value of table $tableName, will be > 0
	 */
	public function getAutoIncrement($tableName) {
		if (!$this->isTableNameAllowed($tableName)) {
			throw new InvalidArgumentException(
				'The given table name is invalid. This means it is either ' .
					'empty or not in the list of allowed tables.'
			);
		}

		Tx_Phpunit_Service_Database::enableQueryLogging();
		$dbResult = $GLOBALS['TYPO3_DB']->sql_query(
			'SHOW TABLE STATUS WHERE Name = \'' . $tableName . '\';'
		);
		if (!$dbResult) {
			throw new Tx_Phpunit_Exception_Database();
		}

		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult);
		$GLOBALS['TYPO3_DB']->sql_free_result($dbResult);

		$autoIncrement = $row['Auto_increment'];
		if ($autoIncrement === NULL) {
			throw new InvalidArgumentException(
				'The given table name is invalid. This means it is either ' .
					'empty or not in the list of allowed tables.'
			);
		}

		return $autoIncrement;
	}

	/**
	 * Returns the list of allowed table names.
	 *
	 * @return array
	 *         all allowed table names for this instance of the testing framework
	 */
	public function getListOfOwnAllowedTableNames() {
		return $this->ownAllowedTables;
	}

	/**
	 * Returns the list of additional allowed table names.
	 *
	 * @return array
	 *         all additional allowed table names for this instance of the
	 *         testing framework, may be empty
	 */
	public function getListOfAdditionalAllowedTableNames() {
		return $this->additionalAllowedTables;
	}

	/**
	 * Puts one or multiple table names on the list of dirty tables (which
	 * represents a list of tables that were used for testing and contain dummy
	 * records and thus are called "dirty" until the next clean up).
	 *
	 * @param string $tableNames
	 *        the table name or a comma-separated list of table names to put on
	 *        the list of dirty tables, must not be empty
	 *
	 * @return void
	 */
	public function markTableAsDirty($tableNames) {
		foreach (t3lib_div::trimExplode(',', $tableNames) as $currentTable) {
			if ($this->isNoneSystemTableNameAllowed($currentTable)) {
				$this->dirtyTables[$currentTable] = $currentTable;
			} elseif ($this->isSystemTableNameAllowed($currentTable)) {
				$this->dirtySystemTables[$currentTable] = $currentTable;
			} else {
				throw new InvalidArgumentException(
					'The table name "' . $currentTable . '" is not allowed for markTableAsDirty.'
				);
			}
		}
	}

	/**
	 * Returns the list of tables that contain dummy records from testing. These
	 * tables are called "dirty tables" as they need to be cleaned up.
	 *
	 * @return array
	 *         associative array containing names of database tables that need
	 *         to be cleaned up
	 */
	public function getListOfDirtyTables() {
		return $this->dirtyTables;
	}

	/**
	 * Returns the list of system tables that contain dummy records from
	 * testing. These tables are called "dirty tables" as they need to be
	 * cleaned up.
	 *
	 * @return array
	 *         associative array containing names of system database tables that
	 *         need to be cleaned up
	 */
	public function getListOfDirtySystemTables() {
		return $this->dirtySystemTables;
	}

	/**
	 * Returns the next sorting value of the relation table which should be used.
	 *
	 * TODO: This function doesn't take already existing relations in the
	 * database - which were created without using the testing framework - into
	 * account. So you always should create new dummy records and create a
	 * relation between these two dummy records, so you're sure there aren't
	 * already relations for a local UID in the database.
	 *
	 * @param string $tableName
	 *        the relation table, must not be empty
	 * @param integer $uidLocal
	 *        UID of the local table, must be > 0
	 *
	 * @return integer the next sorting value to use (> 0)
	 *
	 * @see https://bugs.oliverklee.com/show_bug.cgi?id=1423
	 */
	public function getRelationSorting($tableName, $uidLocal) {
		if (!$this->relationSorting[$tableName][$uidLocal]) {
			$this->relationSorting[$tableName][$uidLocal] = 0;
		}

		$this->relationSorting[$tableName][$uidLocal]++;

		return $this->relationSorting[$tableName][$uidLocal];
	}

	/**
	 * Updates an integer field of a database table by one. This is mainly needed
	 * for counting up the relation counter when creating a database relation.
	 *
	 * The field to update must be of type integer.
	 *
	 * @param string $tableName
	 *        name of the table, must not be empty
	 * @param integer $uid
	 *        the UID of the record to modify, must be > 0
	 * @param string $fieldName
	 *        the field name of the field to modify, must not be empty
	 *
	 * @return void
	 */
	public function increaseRelationCounter($tableName, $uid, $fieldName) {
		if (!$this->isTableNameAllowed($tableName)) {
			throw new InvalidArgumentException(
				'The table name "' . $tableName . '" is invalid. This means ' .
					'it is either empty or not in the list of allowed tables.'
			);
		}
		if (!Tx_Phpunit_Service_Database::tableHasColumn($tableName, $fieldName)) {
			throw new InvalidArgumentException(
				'The table ' . $tableName . ' has no column ' . $fieldName . '.'
			);
		}

		Tx_Phpunit_Service_Database::enableQueryLogging();
		$dbResult = $GLOBALS['TYPO3_DB']->sql_query(
			'UPDATE ' . $tableName . ' SET ' . $fieldName . '=' . $fieldName . '+1 WHERE uid=' . $uid
		);
		if (!$dbResult) {
			throw new Tx_Phpunit_Exception_Database();
		}

		if ($GLOBALS['TYPO3_DB']->sql_affected_rows() === 0) {
			throw new Tx_Phpunit_Exception_Database(
				'The table ' . $tableName . ' does not contain a record with UID ' . $uid . '.'
			);
		}

		$this->markTableAsDirty($tableName);
	}

	/**
	 * Checks whether the ZIPArchive class is provided by the PHP installation.
	 *
	 * Note: This function can be used to mark tests as skipped if this class is
	 *       not available but required for a test to pass succesfully.
	 *
	 * @throws t3lib_exception if the PHP installation does not provide ZIPArchive
	 *
	 * @return void
	 */
	public function checkForZipArchive() {
		if (!in_array('zip', get_loaded_extensions())) {
			throw new t3lib_exception('This PHP installation does not provide the ZIPArchive class.');
		}
	}

	/**
	 * Gets all hooks for this class.
	 *
	 * @return array the hook objects, will be empty if no hooks have been set
	 */
	protected function getHooks() {
		if (!self::$hooksHaveBeenRetrieved) {
			$hookClasses = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['FrameworkCleanUp'];
			if (is_array($hookClasses)) {
				foreach ($hookClasses as $hookClass) {
					self::$hooks[] = t3lib_div::getUserObj($hookClass);
				}
			}

			self::$hooksHaveBeenRetrieved = TRUE;
		}

		return self::$hooks;
	}

	/**
	 * Purges the cached hooks.
	 *
	 * @return void
	 */
	public function purgeHooks() {
		self::$hooks = array();
		self::$hooksHaveBeenRetrieved = FALSE;
	}
}
?>