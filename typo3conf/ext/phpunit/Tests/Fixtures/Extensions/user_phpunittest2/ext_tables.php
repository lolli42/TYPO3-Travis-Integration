<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
$TCA["user_phpunittest2_test"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:user_phpunittest2/locallang_db.xml:user_phpunittest2_test',		
		'label'     => 'title',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',	
			'starttime' => 'starttime',	
			'endtime' => 'endtime',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_user_phpunittest2_test.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, starttime, endtime, title",
	)
);
?>