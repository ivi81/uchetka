CREATE TABLE IF NOT EXISTS `processed_cases_table` (
									 id INT(5) UNSIGNED NOT NULL,
									 root_ID VARCHAR(255) NOT NULL,
									 process_date DATETIME NOT NULL,
    								 PRIMARY KEY(id),
									 INDEX index_for_root_ID(root_ID)) ENGINE=MyISAM DEFAULT CHARSET=utf8