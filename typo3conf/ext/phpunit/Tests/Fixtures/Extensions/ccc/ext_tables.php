<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
$TCA["tx_ccc_test"] = array (
	"ctrl" => array (
		'title'	 => 'LLL:EXT:ccc/locallang_db.xml:tx_ccc_test',		
		'label'	 => 'uid',	
		'tstamp'	=> 'tstamp',
		'crdate'	=> 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'		  => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_ccc_test.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden",
	)
);

$TCA["tx_ccc_data"] = array (
	"ctrl" => array (
		'title'	 => 'LLL:EXT:ccc/locallang_db.xml:tx_ccc_data',		
		'label'	 => 'uid',	
		'tstamp'	=> 'tstamp',
		'crdate'	=> 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'		  => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_ccc_data.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, title, test",
	)
);
?>