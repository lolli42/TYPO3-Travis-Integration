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
	 * @var \TYPO3\CMS\Install\Service\SqlSchemaMigrationService
	 */
	protected $schemaMigrationService;

	/**
	 * @var \TYPO3\CMS\Install\Service\SqlExpectedSchemaService
	 */
	protected $expectedSchemaService;

	/**
	 *
	 */
	public function __construct() {
		$objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
		$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');

		$this->schemaMigrationService = new \TYPO3\CMS\Install\Service\SqlSchemaMigrationService();
		$this->expectedSchemaService = new \TYPO3\CMS\Install\Service\SqlExpectedSchemaService();

		// Disable the extbase object cache, because the signal slot dispatcher will use but it is not present yet.
		$this->disableExtbaseObjectCaching();

		// Take care of property injection
		$objectManagerPropertyReflection = new \ReflectionProperty($this->expectedSchemaService, 'objectManager');
		$signalSlotDispatcherPropertyReflection = new \ReflectionProperty($this->expectedSchemaService, 'signalSlotDispatcher');
		$objectManagerPropertyReflection->setAccessible(TRUE);
		$signalSlotDispatcherPropertyReflection->setAccessible(TRUE);
		$objectManagerPropertyReflection->setValue($this->expectedSchemaService, $objectManager);
		$signalSlotDispatcherPropertyReflection->setValue($this->expectedSchemaService, $signalSlotDispatcher);
	}

	/**
	 *
	 */
	protected function disableExtbaseObjectCaching() {
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['extbase_object'] = array (
			'frontend' => 'TYPO3\CMS\Core\Cache\Frontend\VariableFrontend',
			'backend' => 'TYPO3\CMS\Core\Cache\Backend\TransientMemoryBackend',
			'options' => array()
		);
		$GLOBALS['typo3CacheManager']->setCacheConfigurations($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']);
	}

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

		// Raw concatenated ext_tables.sql and friends string
		$expectedSchemaString = $this->expectedSchemaService->getTablesDefinitionString(TRUE);
		$statements = $this->schemaMigrationService->getStatementArray($expectedSchemaString, TRUE);
		list($_, $insertCount) = $this->schemaMigrationService->getCreateTables($statements, TRUE);

		$fieldDefinitionsFile = $this->schemaMigrationService->getFieldDefinitions_fileContent($expectedSchemaString);
		$fieldDefinitionsDatabase = $this->schemaMigrationService->getFieldDefinitions_database();
		$difference = $this->schemaMigrationService->getDatabaseExtra($fieldDefinitionsFile, $fieldDefinitionsDatabase);
		$updateStatements = $this->schemaMigrationService->getUpdateSuggestions($difference);

		$this->schemaMigrationService->performUpdateQueries($updateStatements['add'], $updateStatements['add']);
		$this->schemaMigrationService->performUpdateQueries($updateStatements['change'], $updateStatements['change']);
		$this->schemaMigrationService->performUpdateQueries($updateStatements['create_table'], $updateStatements['create_table']);

		foreach ($insertCount as $table => $count) {
			$insertStatements = $this->schemaMigrationService->getTableInsertStatements($statements, $table);
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
