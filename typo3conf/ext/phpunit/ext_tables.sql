#
# Table structure for table 'tx_phpunit_test'
#
CREATE TABLE tx_phpunit_test (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,
	title tinytext,
	related_records int(11) unsigned DEFAULT '0' NOT NULL,
	bidirectional int(11) unsigned DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY phpunit_dummy (is_dummy_record)
);


#
# Table structure for table 'tx_phpunit_testchild'
#
CREATE TABLE tx_phpunit_testchild (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY phpunit_dummy (is_dummy_record)
);


#
# Table structure for table 'tx_phpunit_test_article_mm'
#
CREATE TABLE tx_phpunit_test_article_mm (
	uid_local int(11) unsigned DEFAULT '0' NOT NULL,
	uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,

	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign),
	KEY phpunit_dummy (is_dummy_record)
);


#
# Table structure for table 'be_users'
#
CREATE TABLE be_users (
	tx_phpunit_is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
	KEY phpunit_dummy (tx_phpunit_is_dummy_record)
);


#
# Table structure for table 'be_groups'
#
CREATE TABLE be_groups (
	tx_phpunit_is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
	KEY phpunit_dummy (tx_phpunit_is_dummy_record)
);


#
# Table structure for table 'fe_groups'
#
CREATE TABLE fe_groups (
	tx_phpunit_is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
	KEY phpunit_dummy (tx_phpunit_is_dummy_record)
);


#
# Table structure for table 'fe_users'
#
CREATE TABLE fe_users (
	tx_phpunit_is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
	KEY phpunit_dummy (tx_phpunit_is_dummy_record)
);


#
# Table structure for table 'pages'
#
CREATE TABLE pages (
	tx_phpunit_is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
	KEY phpunit_dummy (tx_phpunit_is_dummy_record)
);


#
# Table structure for table 'sys_template'
#
CREATE TABLE sys_template (
	tx_phpunit_is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
	KEY phpunit_dummy (tx_phpunit_is_dummy_record)
);


#
# Table structure for table 'tt_content'
#
CREATE TABLE tt_content (
	tx_phpunit_is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
	KEY phpunit_dummy (tx_phpunit_is_dummy_record)
);
