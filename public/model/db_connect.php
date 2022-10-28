<?php
	//Load database connection settings
	require_once "config/db_config.php";

	//Database connection
	$db = new mysqli($db_hostname, $db_username, $db_password, $db_database);
?>