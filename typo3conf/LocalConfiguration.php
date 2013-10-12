<?php
return array(
	'BE' => array(
		'disable_exec_function' => 0,
		'fileCreateMask' => '0664',
		'folderCreateMask' => '2774',
		'installToolPassword' => '$P$Cua9PNenq8RGsXuSWGppKKAnDdnRED0',
		'loginSecurityLevel' => 'normal',
		'versionNumberInFilename' => '0',
	),
	'DB' => array(
		'database' => 'typo3_test',
		'host' => 'localhost',
		'password' => '',
		'socket' => '',
		'username' => 'root',
	),
	'EXT' => array(
		'extConf' => array(
			'workspaces' => 'a:0:{}',
		),
	),
	'FE' => array(
		'addRootLineFields' => 'backend_layout',
		'lifetime' => '604800',
		'logfile_dir' => 'localsettings/logs/',
		'pageNotFound_handling' => '/404/',
	),
	'GFX' => array(
		'TTFdpi' => '96',
		'gdlib_png' => '1',
		'im_noScaleUp' => '1',
		'im_path' => '/usr/bin/',
		'im_version_5' => 'im6',
		'jpg_quality' => '80',
		'thumbnails_png' => '1',
	),
	'INSTALL' => array(
		'wizardDone' => array(),
	),
	'SYS' => array(
		'UTF8filesystem' => '1',
		'debugExceptionHandler' => '',
		'devIPmask' => ',192.168.1.*',
		'displayErrors' => '1',
		'doNotCheckReferer' => '1',
		'enableDeprecationLog' => 'file',
		'enable_DLOG' => 'enable_DLOG',
		'encryptionKey' => 'Travis Tests',
		'forceReturnPath' => '1',
		'setDBinit' => 'SET NAMES utf8',
		'setMemoryLimit' => 1024,
		'sitename' => 'New TYPO3 site',
		'sqlDebug' => '1',
	),
);
?>