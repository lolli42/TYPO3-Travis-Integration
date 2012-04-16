<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA["tx_ccc_test"] = array (
	"ctrl" => $TCA["tx_ccc_test"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden"
	),
	"feInterface" => $TCA["tx_ccc_test"]["feInterface"],
	"columns" => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'	=> 'check',
				'default' => '0'
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);



$TCA["tx_ccc_data"] = array (
	"ctrl" => $TCA["tx_ccc_data"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,title,test"
	),
	"feInterface" => $TCA["tx_ccc_data"]["feInterface"],
	"columns" => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'	=> 'check',
				'default' => '0'
			)
		),
		"title" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:ccc/locallang_db.xml:tx_ccc_data.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "trim",
			)
		),
		"test" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:ccc/locallang_db.xml:tx_ccc_data.test",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "tx_ccc_test",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,	
				"MM" => "tx_ccc_data_test_mm",
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1, title;;;;2-2-2, test;;;;3-3-3")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);
?>