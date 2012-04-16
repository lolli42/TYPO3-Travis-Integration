<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
$TCA["tx_bbb_test"] = array (
	"ctrl" => array (
		'title'	 => 'LLL:EXT:bbb/locallang_db.xml:tx_bbb_test',		
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
		'iconfile'		  => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_bbb_test.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden",
	)
);

$tempColumns = Array (
	"tx_bbb_test" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:bbb/locallang_db.xml:tx_aaa_test.tx_bbb_test",		
		"config" => Array (
			"type" => "input",	
			"size" => "30",	
			"eval" => "trim",
		)
	),
);


t3lib_div::loadTCA("tx_aaa_test");
t3lib_extMgm::addTCAcolumns("tx_aaa_test",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("tx_aaa_test","tx_bbb_test;;;;1-1-1");
?>