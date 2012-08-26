<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

$typo_db_username = 'root';
$typo_db_password = '';
$typo_db_host = 'localhost';
$typo_db = 'typo3_test';


$TYPO3_CONF_VARS['SYS']['sitename'] = 'New TYPO3 site';
$TYPO3_CONF_VARS['SYS']['doNotCheckReferer'] = '1';
$TYPO3_CONF_VARS['SYS']['forceReturnPath'] = '1';
$TYPO3_CONF_VARS['SYS']['encryptionKey'] = 'Travis Tests';
	// Default password is "joh316" :
$TYPO3_CONF_VARS['BE']['installToolPassword'] = 'bacb98acf97e0b6112b1d1b650b84971';
$TYPO3_CONF_VARS['BE']['fileCreateMask'] = '0664';
$TYPO3_CONF_VARS['BE']['folderCreateMask'] = '2774';
$TYPO3_CONF_VARS['BE']['disable_exec_function'] = '0';
$TYPO3_CONF_VARS['BE']['versionNumberInFilename'] = '0';

	// Character set
$TYPO3_CONF_VARS['SYS']['UTF8filesystem'] = '1';
$TYPO3_CONF_VARS['SYS']['setDBinit'] = 'SET NAMES utf8';
$TYPO3_CONF_VARS['BE']['forceCharset'] = 'utf-8';

$TYPO3_CONF_VARS['SYS']['sqlDebug'] = '1';
$TYPO3_CONF_VARS['SYS']['displayErrors'] = '1';
$TYPO3_CONF_VARS['SYS']['devIPmask'] .= ',192.168.1.*';
$TYPO3_CONF_VARS['SYS']['enableDeprecationLog'] = 'file'; // For TYPO3 >=4.3, disable for production sites

	// Required for Fluidpages Extension
$TYPO3_CONF_VARS['FE']['addRootLineFields'] = 'backend_layout';

$TYPO3_CONF_VARS['FE']['lifetime'] = '604800';
$TYPO3_CONF_VARS['FE']['logfile_dir'] = 'localsettings/logs/';
$TYPO3_CONF_VARS['FE']['pageNotFound_handling'] = '/404/';

$TYPO3_CONF_VARS['GFX']['im_version_5'] = 'im6';
$TYPO3_CONF_VARS['GFX']['TTFdpi'] = '96';
$TYPO3_CONF_VARS['GFX']['jpg_quality'] = '80';
$TYPO3_CONF_VARS['GFX']['thumbnails_png'] = '1';
$TYPO3_CONF_VARS['GFX']['gblib_png'] = '1';
$TYPO3_CONF_VARS['GFX']['im_path'] = '/usr/bin/';
$TYPO3_CONF_VARS['GFX']['im_noScaleUp'] = '1';

$TYPO3_CONF_VARS['EXT']['extList'] = 'extbase,fluid,info,perm,func,filelist,about,tsconfig_help,context_help,extra_page_cm_options,impexp,sys_note,tstemplate,tstemplate_ceditor,tstemplate_info,tstemplate_objbrowser,tstemplate_analyzer,func_wizards,wizard_crpages,wizard_sortpages,lowlevel,install,belog,beuser,aboutmodules,setup,taskcenter,info_pagetsconfig,viewpage,rtehtmlarea,css_styled_content,t3skin,t3editor,reports,felogin,form,phpunit';

$GLOBALS['TYPO3_CONF_VARS']['SYS']['enable_DLOG'] = 0;

## INSTALL SCRIPT EDIT POINT TOKEN - all lines after this points may be changed by the install script!
?>