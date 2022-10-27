<?php
	//Connect to database.
	require "model/db_connect.php";

	function get_category_entries() {
		global $db;
		$result = $db->query("SELECT * FROM category");

		if (!$result) {
			return "An error occurred while fetching the entries.";
		}
		else if ($result === true || $result->num_rows == 0) {
			return array();
		}
		
		$entries = array();
		while ($cat_entries = $result->fetch_assoc()) {
			$entries[] = $cat_entries;
		}
		return $entries;
	}

	function get_category_id($category_id) {
		global $db;

		$result = $db->query("SELECT * FROM category WHERE category_id = $category_id");

		if (!$result) {
			return "An error occurred while fetching the category_id.";
		}
		else if ($result === true || $result->num_rows == 0) {
			return null;
		}
		else {
			$category_id = $result->fetch_assoc();

			return $category_id;
		}
	}

	function create_category_entry($active, $name) {
		global $db;

		$result = $db->query("INSERT INTO category(active, name) VALUES ($active, '$name')");

		if (!$result) {
			return false;
		}
		
		return true;
	}

	function update_category_entry($category_id, $active, $name) {
		global $db;

		$result = $db->query("UPDATE category SET active = $active, name = '$name' WHERE category_id = $category_id");

		if (!$result) {
			return false;
		}
		
		return true;
	}

	function delete_category_entry($category_id) {
		global $db;

		$result = $db->query("DELETE FROM category WHERE category_id = $category_id");

		if (!$result) {
			return "An error occurred while deleting the category_id.";
		}
		else if ($db->affected_rows == 0) {
			return null;
		}
		else {
			return true;
		}
	}
?>