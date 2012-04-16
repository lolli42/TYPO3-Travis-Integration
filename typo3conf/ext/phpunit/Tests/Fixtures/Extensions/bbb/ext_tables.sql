#
# Table structure for table 'tx_bbb_test'
#
CREATE TABLE tx_bbb_test (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);



#
# Table structure for table 'tx_aaa_test'
#
CREATE TABLE tx_aaa_test (
	tx_bbb_test varchar(255) DEFAULT '' NOT NULL
);