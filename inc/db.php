<?php

/**
	If our database doesn't exist, create our SQLite schema; we'll do it
	here programmatically; but this can be done outside of our application
**/
F3::sql(
	/**
		If an array is passed to the F3::sql() method, the framework
		automatically switches to batch mode; Any error that occurs during
		execution of this command sequence will rollback the transaction.
		If successful, Fat-Free issues a SQL commit.
	**/
	array(
		'CREATE TABLE IF NOT EXISTS kul ('.
			'tc INT UNSIGNED NOT NULL,'.
			'ad CHAR (15),'.
			'soyad CHAR (20),'.
			'PRIMARY KEY(tc)'.
		');' 
	)
);

?>
