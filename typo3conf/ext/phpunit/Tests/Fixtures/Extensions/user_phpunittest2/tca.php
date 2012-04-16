<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA["user_phpunittest2_test"] = array (
	"ctrl" => $TCA["user_phpunittest2_test"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,starttime,endtime,title"
	),
	"feInterface" => $TCA["user_phpunittest2_test"]["feInterface"],
	"columns" => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'starttime' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
			'config'  => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				'eval'     => 'date',
				'default'  => '0',
				'checkbox' => '0'
			)
		),
		'endtime' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.endtime',
			'config'  => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				'eval'     => 'date',
				'checkbox' => '0',
				'default'  => '0',
				'range'    => array (
					'upper' => mktime(0, 0, 0, 12, 31, 2020),
					'lower' => mktime(0, 0, 0, date('m')-1, date('d'), date('Y'))
				)
			)
		),
		"title" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:user_phpunittest2/locallang_db.xml:user_phpunittest2_test.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required",
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1, title;;;;2-2-2")
	),
	"palettes" => array (
		"1" => array("showitem" => "starttime, endtime")
	)
);
?>