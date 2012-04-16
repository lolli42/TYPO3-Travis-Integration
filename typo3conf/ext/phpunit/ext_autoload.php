<?php
$extensionPath = t3lib_extMgm::extPath('phpunit');
$vfsStreamPath = $extensionPath . 'PEAR/vfsStream/';
return array(
	'tx_phpunit_backend_ajax' => $extensionPath . 'Classes/BackEnd/Ajax.php',
	'tx_phpunit_backend_module' => $extensionPath . 'Classes/BackEnd/Module.php',
	'tx_phpunit_backend_testlistener' => $extensionPath . 'Classes/BackEnd/TestListener.php',
	'tx_phpunit_cli_testrunner' => $extensionPath . 'Classes/Cli/TestRunner.php',
	'tx_phpunit_database_testcase' => $extensionPath . 'Classes/Database/TestCase.php',
	'tx_phpunit_exception_database' => $extensionPath . 'Classes/Exception/Database.php',
	'tx_phpunit_exception_emptyqueryresult' => $extensionPath . 'Classes/Exception/EmptyQueryResult.php',
	'tx_phpunit_exception_notestsdirectory' => $extensionPath . 'Classes/Exception/NoTestsDirectory.php',
	'tx_phpunit_framework' => $extensionPath . 'Classes/Framework.php',
	'tx_phpunit_interface_frameworkcleanuphook' => $extensionPath . 'Classes/Interface/FrameworkCleanupHook.php',
	'tx_phpunit_reports_status' => $extensionPath . 'Classes/Reports/Status.php',
	'tx_phpunit_selenium_testcase' => $extensionPath . 'Classes/Selenium/TestCase.php',
	'tx_phpunit_service_database' => $extensionPath . 'Classes/Service/Database.php',
	'tx_phpunit_service_testfinder' => $extensionPath . 'Classes/Service/TestFinder.php',
	'tx_phpunit_testablecode' => $extensionPath . 'Classes/TestableCode.php',
	'tx_phpunit_testcase' => $extensionPath . 'Classes/TestCase.php',
	'tx_phpunit_test_testsuite' => $extensionPath . 'Tests/tx_phpunit_testsuite.php',
	'vfsstream' => $vfsStreamPath . 'vfsStream.php',
	'vfsstreamabstractContent' => $vfsStreamPath . 'vfsStreamAbstractContent.php',
	'vfsstreamcontainer' => $vfsStreamPath . 'vfsStreamContainer.php',
	'vfsstreamcontaineriterator' => $vfsStreamPath . 'vfsStreamContainerIterator.php',
	'vfsstreamcontent' => $vfsStreamPath . 'vfsStreamContent.php',
	'vfsstreamdirectory' => $vfsStreamPath . 'vfsStreamDirectory.php',
	'vfsstreamexception' => $vfsStreamPath . 'vfsStreamException.php',
	'vfsstreamfile' => $vfsStreamPath . 'vfsStreamFile.php',
	'vfsstreamwrapper' => $vfsStreamPath . 'vfsStreamWrapper.php',
);
?>