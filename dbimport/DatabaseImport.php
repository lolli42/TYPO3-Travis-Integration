<?php
namespace TYPO3\CMS\TravisIntegration;

/***************************************************************
 * Copyright notice
 *
 * (c) 2013 Christian Kuhn <lolli@schwarzbu.ch>
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
 * A copy is found in the textfile GPL.txt and important notices to the license
 * from the author is found in LICENSE.txt distributed with these scripts.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/


define('TYPO3_MODE', 'BE');
define('TYPO3_cliMode', TRUE);

require __DIR__ . '/../../typo3/sysext/core/Classes/Core/CliBootstrap.php';
\TYPO3\CMS\Core\Core\CliBootstrap::checkEnvironmentOrDie();

require __DIR__ . '/../../typo3/sysext/core/Classes/Core/Bootstrap.php';
\TYPO3\CMS\Core\Core\Bootstrap::getInstance()
	->baseSetup('build-environment/dbimport/')
	->loadConfigurationAndInitialize()
	->loadTypo3LoadedExtAndExtLocalconf(TRUE)
	->applyAdditionalConfigurationSettings()
	->initializeTypo3DbGlobal(TRUE)
	->loadExtensionTables(FALSE);

class DatabaseImport {

	/**
	 * Main entry method
	 *
	 * @return void
	 */
	public function run() {
		$this->importDatabaseData();
	}

	/**
	 * Create tables and import static rows
	 *
	 * @return void
	 */
	protected function importDatabaseData() {
		// Import database data
		$database = $this->getDatabase();
		$schemaMigrationService = new \TYPO3\CMS\Install\Service\SqlSchemaMigrationService();
		$expectedSchemaService = new \TYPO3\CMS\Install\Service\SqlExpectedSchemaService();

		// Raw concatenated ext_tables.sql and friends string
		$expectedSchemaString = $expectedSchemaService->getTablesDefinitionString(TRUE);
		$statements = $schemaMigrationService->getStatementArray($expectedSchemaString, TRUE);
		list($_, $insertCount) = $schemaMigrationService->getCreateTables($statements, TRUE);

		$fieldDefinitionsFile = $schemaMigrationService->getFieldDefinitions_fileContent($expectedSchemaString);
		$fieldDefinitionsDatabase = $schemaMigrationService->getFieldDefinitions_database();
		$difference = $schemaMigrationService->getDatabaseExtra($fieldDefinitionsFile, $fieldDefinitionsDatabase);
		$updateStatements = $schemaMigrationService->getUpdateSuggestions($difference);

		$schemaMigrationService->performUpdateQueries($updateStatements['add'], $updateStatements['add']);
		$schemaMigrationService->performUpdateQueries($updateStatements['change'], $updateStatements['change']);
		$schemaMigrationService->performUpdateQueries($updateStatements['create_table'], $updateStatements['create_table']);

		foreach ($insertCount as $table => $count) {
			$insertStatements = $schemaMigrationService->getTableInsertStatements($statements, $table);
			foreach ($insertStatements as $insertQuery) {
				$insertQuery = rtrim($insertQuery, ';');
				$database->admin_query($insertQuery);
			}
		}
	}

	/**
	 * @return \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected function getDatabase() {
		return $GLOBALS['TYPO3_DB'];
	}
}

$databaseImport = new \TYPO3\CMS\TravisIntegration\DatabaseImport();
$databaseImport->run();
?>
