<?php
if (!defined ('TYPO3_MODE')) {
	die('Access denied.');
}

$TCA['tx_phpunit_test'] = array(
	'ctrl' => $TCA['tx_phpunit_test']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'hidden,starttime,endtime,title,related_records',
	),
	'columns' => array(
		'hidden' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config' => array(
				'type' => 'check',
				'default' => '0',
			),
		),
		'starttime' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
			'config' => array(
				'type' => 'none',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'default' => '0',
				'checkbox' => '0',
			),
		),
		'endtime' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.endtime',
			'config' => array(
				'type' => 'none',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'checkbox' => '0',
				'default' => '0',
				'range' => array(
					'upper' => mktime(0,0,0,12,31,2020),
					'lower' => mktime(0,0,0,date('m')-1,date('d'),date('Y')),
				),
			),
		),
		'title' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:phpunit/locallang_db.xml:tx_phpunit_test.title',
			'config' => array(
				'type' => 'none',
				'size' => '30',
			),
		),
		'related_records' => array(
			'l10n_mode' => 'exclude',
			'exclude' => 1,
			'label' => 'Related records (m:n relation using an m:n table)',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'tx_phpunit_test',
				'size' => 4,
				'minitems' => 0,
				'maxitems' => 99,
				'MM' => 'tx_phpunit_test_article_mm',
			),
		),
		'bidirectional' => array(
			'l10n_mode' => 'exclude',
			'exclude' => 1,
			'label' => 'Related records (m:n relation using an m:n table)',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'tx_phpunit_test',
				'size' => 4,
				'minitems' => 0,
				'maxitems' => 99,
				'MM' => 'tx_phpunit_test_article_mm',
				'MM_opposite_field' => 'related_records',
			),
		),
	),
	'types' => array(
		'0' => array('showitem' => 'title;;;;2-2-2, related_records'),
	),
	'palettes' => array(
		'1' => array('showitem' => 'starttime, endtime'),
	),
);
?>