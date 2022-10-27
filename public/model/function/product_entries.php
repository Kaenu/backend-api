<?php
	//Connect to database.
	require "model/db_connect.php";

	function get_product_entries() {
		global $db;

		$result = $db->query("SELECT * FROM product");

		if (!$result) {
			return "An error occurred while fetching the entries.";
		}
		else if ($result === true || $result->num_rows == 0) {
			return array();
		}
		
		$entries = array();

		while ($prod_entries = $result->fetch_assoc()) {
			$entries[] = $prod_entries;
		}

		return $entries;
	}

	function get_product_id($product_id) {
		global $db;

		$result = $db->query("SELECT * FROM product WHERE product_id = $product_id");

		if (!$result) {
			return "An error occurred while fetching the product_id.";
		}
		else if ($result === true || $result->num_rows == 0) {
			return null;
		}
		else {
			$product_id = $result->fetch_assoc();

			return $product_id;
		}
	}

	function create_product_entry($sku, $active, $id_category, $name, $image, $description, $price, $stock) {
		global $db;

		$result = $db->query("INSERT INTO product(sku, active, id_category, name, image, description, price, stock) VALUES ($sku, $active, $id_category, '$name', '$image', '$description', $price, $stock)");

		if (!$result) {
			return false;
		}
		
		return true;
	}

	function update_product_entry($product_id, $sku, $active, $name, $image, $description, $price, $stock) {
		global $db;

		$result = $db->query("UPDATE product_id SET name = $product_id, sku = $sku, active = $active, name = $name, image = $image, description = $description, price = $price, stock = $stock WHERE product_id = $product_id");

		if (!$result) {
			return false;
		}
		
		return true;
	}

	function delete_product_entry($product_id) {
		global $db;

		$result = $db->query("DELETE FROM product WHERE product_id = $product_id");

		if (!$result) {
			return "An error occurred while deleting the product_id.";
		}
		else if ($db->affected_rows == 0) {
			return null;
		}
		else {
			return true;
		}
	}
?>