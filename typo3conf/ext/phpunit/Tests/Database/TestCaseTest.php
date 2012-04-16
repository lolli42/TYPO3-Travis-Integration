<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2008-2011 Kasper Ligaard (kasperligaard@gmail.com)
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
 * Testcase for the database functions of the "phpunit" extension.
 *
 * These testcases require that the following extensions are installed:
 *  1. aaa
 *  2. bbb (depends on aaa and alters aaa' tables)
 *  3. ccc (depends on bbb)
 *  4. ddd (depends on bbb)
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Kasper Ligaard <kasperligaard@gmail.com>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Phpunit_Database_TestCaseTest extends Tx_Phpunit_Database_TestCase {
	public function tearDown() {
		$this->dropDatabase();
		$this->switchToTypo3Database();
	}

	/**
	 * @test
	 */
	public function nullToEmptyString() {
		$this->assertEquals('', mysql_real_escape_string(NULL));
	}

	/**
	 * @test
	 */
	public function creatingTestDatabase() {
		if (!$this->dropDatabase() || !$this->createDatabase()) {
			$this->markTestSkipped(
				'This test can only be run if the current DB user has the ' .
					'permissions to CREATE and DROP databases.'
			);
		}

		$db = $GLOBALS['TYPO3_DB'];

		$databaseNames = $db->admin_get_dbs();

		$this->assertContains($this->testDatabase, $databaseNames);
	}

	/**
	 * @test
	 */
	public function droppingTestDatabase() {
		$db = $GLOBALS['TYPO3_DB'];
		$databaseNames = $db->admin_get_dbs();

		if (!in_array($this->testDatabase, $databaseNames)) {
			if (!$this->createDatabase()) {
				$this->markTestSkipped(
					'This test can only be run if the current DB user has the ' .
						'permissions to CREATE and DROP databases.'
				);
			}
			$databaseNames = $db->admin_get_dbs();
			$this->assertContains($this->testDatabase, $databaseNames);
		}

		if (!$this->dropDatabase()) {
			$this->markTestSkipped(
				'This test can only be run if the current DB user has the ' .
					'permissions to CREATE and DROP databases.'
			);
		}
		$databaseNames = $db->admin_get_dbs();
		$this->assertNotContains($this->testDatabase, $databaseNames);
	}

	/**
	 * @test
	 */
	public function cleaningDatabase() {
		if (!$this->createDatabase()) {
			$this->markTestSkipped(
				'This test can only be run if the current DB user has the ' .
					'permissions to CREATE and DROP databases.'
			);
		}
		$this->importExtensions(array('tsconfig_help'));

		$db = $this->useTestDatabase();
		$res = $db->sql_query('show tables');
		$rows = mysql_num_rows($res);
		$this->assertNotEquals(0, $rows);

		$this->cleanDatabase();
		$res = $db->sql_query('show tables');
		$rows = mysql_num_rows($res);
		$this->assertEquals(0, $rows);
	}

	/**
	 * @test
	 */
	public function importingExtension() {
		if (!$this->createDatabase()) {
			$this->markTestSkipped(
				'This test can only be run if the current DB user has the ' .
					'permissions to CREATE and DROP databases.'
			);
		}
		$db = $this->useTestDatabase();
		$this->importExtensions(array('tsconfig_help'));

		$res = $db->sql_query('show tables');
		$rows = mysql_num_rows($res);

		$this->assertNotEquals(0, $rows);
	}

	/**
	 * @test
	 */
	public function extensionAlteringTable() {
		if (!t3lib_extMgm::isLoaded('aaa') || !t3lib_extMgm::isLoaded('bbb')) {
			$this->markTestSkipped(
				'This test can only be run if the extensions aaa and bbb ' .
					'from tests/res are installed.'
			);
		}

		if (!$this->createDatabase()) {
			$this->markTestSkipped(
				'This test can only be run if the current DB user has the ' .
					'permissions to CREATE and DROP databases.'
			);
		}
		$db = $this->useTestDatabase();
		$this->importExtensions(array('bbb'), TRUE);

		$tableNames = $this->getDatabaseTables();
		$this->assertContains('tx_bbb_test', $tableNames, 'Check that extension bbb is installed. The extension can be found in Tests/Fixtures/Extensions/.');
		$this->assertContains('tx_aaa_test', $tableNames, 'Check that extension aaa is installed. The extension can be found in Tests/Fixtures/Extensions/.');

		// extension BBB extends an AAA table
		$columns = $db->admin_get_fields('tx_aaa_test');
		$this->assertContains('tx_bbb_test', array_keys($columns));
	}

	/**
	 * @test
	 */
	public function recursiveImportingExtensions() {
		if (!t3lib_extMgm::isLoaded('aaa') || !t3lib_extMgm::isLoaded('bbb')
			|| !t3lib_extMgm::isLoaded('ccc')
		) {
			$this->markTestSkipped(
				'This test can only be run if the extensions aaa, bbb and ccc ' .
					'from tests/res are installed.'
			);
		}

		if (!$this->createDatabase()) {
			$this->markTestSkipped(
				'This test can only be run if the current DB user has the ' .
					'permissions to CREATE and DROP databases.'
			);
		}
		$this->useTestDatabase();
		$this->importExtensions(array('ccc', 'aaa'), TRUE);

		$tableNames = $this->getDatabaseTables();

		$this->assertContains('tx_ccc_test', $tableNames, 'Check that extension ccc is installed. The extension can be found in Tests/Fixtures/Extensions/.');
		$this->assertContains('tx_bbb_test', $tableNames, 'Check that extension bbb is installed. The extension can be found in Tests/Fixtures/Extensions/.');
		$this->assertContains('tx_aaa_test', $tableNames, 'Check that extension aaa is installed. The extension can be found in Tests/Fixtures/Extensions/.');
	}

	/**
	 * @test
	 */
	public function skippingDependencyExtensions() {
		if (!t3lib_extMgm::isLoaded('aaa') || !t3lib_extMgm::isLoaded('bbb')
			|| !t3lib_extMgm::isLoaded('ccc') || !t3lib_extMgm::isLoaded('ddd')
		) {
			$this->markTestSkipped(
				'This test can only be run if the extensions aaa, bbb, ccc ' .
					'and ddd from tests/res are installed.'
			);
		}

		if (!$this->createDatabase()) {
			$this->markTestSkipped(
				'This test can only be run if the current DB user has the ' .
					'permissions to CREATE and DROP databases.'
			);
		}
		$this->useTestDatabase();

		$toSkip = array('bbb');
		$this->importExtensions(array('ccc', 'ddd'), TRUE, $toSkip);

		$tableNames = $this->getDatabaseTables();

		$this->assertContains('tx_ccc_test', $tableNames, 'Check that extension ccc is installed. The extension can be found in Tests/Fixtures/Extensions/.');
		$this->assertContains('tx_ddd_test', $tableNames, 'Check that extension ddd is installed. The extension can be found in Tests/Fixtures/Extensions/.');
		$this->assertNotContains('tx_bbb_test', $tableNames);
		$this->assertNotContains('tx_aaa_test', $tableNames);
	}

	/**
	 * @test
	 */
	public function importingDataSet() {
		if (!t3lib_extMgm::isLoaded('ccc')) {
			$this->markTestSkipped(
				'This test can only be run if the extension ccc from ' .
					'tests/res is installed.'
			);
		}

		if (!$this->createDatabase()) {
			$this->markTestSkipped(
				'This test can only be run if the current DB user has the ' .
					'permissions to CREATE and DROP databases.'
			);
		}
		$db = $this->useTestDatabase();
		$this->importExtensions(array('ccc'));
		$this->importDataSet(t3lib_extMgm::extPath('phpunit') . 'Tests/Database/Fixtures/DataSet.xml');

		$result = $db->exec_SELECTgetRows('*', 'tx_ccc_test', NULL);
		$this->assertEquals(2, count($result));
		$this->assertEquals(1, $result[0]['uid']);
		$this->assertEquals(2, $result[1]['uid']);

		$result = $db->exec_SELECTgetRows('*', 'tx_ccc_data', NULL);
		$this->assertEquals(1, count($result));
		$this->assertEquals(1, $result[0]['uid']);

		$result = $db->exec_SELECTgetRows('*', 'tx_ccc_data_test_mm', NULL);
		$this->assertEquals(2, count($result));
		$this->assertEquals(1, $result[0]['uid_local']);
		$this->assertEquals(1, $result[0]['uid_foreign']);
		$this->assertEquals(1, $result[1]['uid_local']);
		$this->assertEquals(2, $result[1]['uid_foreign']);
	}
}
?>