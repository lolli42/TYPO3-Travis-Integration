<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

$TCA['tx_phpunit_test'] = array(
	'ctrl' => array(
		'title' => 'LLL:EXT:phpunit/locallang_db.xml:tx_phpunit_test',
		'readOnly' => 1,
		'adminOnly' => 1,
		'rootLevel' => 1,
		'label' => 'title',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'versioningWS' => FALSE,
		'default_sortby' => 'ORDER BY uid',
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY) . 'Configuration/TCA/TCA.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif',
	)
);

//$TCA['tx_phpunit_testchild'] = array(
//	'ctrl' => array(
//		'title' => 'LLL:EXT:phpunit/locallang_db.xml:tx_phpunit_test',
//		'readOnly' => 1,
//		'adminOnly' => 1,
//		'rootLevel' => 1,
//		'label' => 'title',
//		'tstamp' => 'tstamp',
//		'crdate' => 'crdate',
//		'cruser_id' => 'cruser_id',
//		'versioningWS' => FALSE,
//		'default_sortby' => 'ORDER BY uid',
//		'delete' => 'deleted',
//		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY) . 'Configuration/TCA/TCA.php',
//		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif',
//	)
//);

if (TYPO3_MODE === 'BE') {
	t3lib_extMgm::addModule('tools', 'txphpunitbeM1', '', t3lib_extMgm::extPath($_EXTKEY) . 'Classes/BackEnd/');

	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['reports']['tx_reports']['status']['providers']['PHPUnit'][] = 'Tx_Phpunit_Reports_Status';
}
?>