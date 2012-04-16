#
# Table structure for table 'user_phpunittest_test'
#
CREATE TABLE user_phpunittest_test (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	starttime int(11) DEFAULT '0' NOT NULL,
	endtime int(11) DEFAULT '0' NOT NULL,
	title tinytext NOT NULL,

	tx_phpunit_is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY dummy (tx_phpunit_is_dummy_record)
);


#
# Table structure for table 'user_phpunittest_test_article_mm'
#
CREATE TABLE user_phpunittest_test_article_mm (
	uid_local int(11) unsigned DEFAULT '0' NOT NULL,
	uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,

	tx_phpunit_is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,

	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign),
	KEY dummy (tx_phpunit_is_dummy_record)
);