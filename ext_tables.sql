#
# Table structure for table 'tx_contentsync_job'
#
CREATE TABLE tx_contentsync_job (
    uid int(11) NOT NULL auto_increment,
    status int(4) DEFAULT '0' NOT NULL,
    error text,
    json_configuration text,
    created_time int(11) unsigned DEFAULT '0' NOT NULL,
    start_time int(11) unsigned DEFAULT '0' NOT NULL,
    end_time int(11) unsigned DEFAULT '0' NOT NULL,
    PRIMARY KEY (uid)
);