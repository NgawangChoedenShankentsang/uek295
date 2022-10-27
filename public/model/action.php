<?php
	//Connect to database.
	//require "../model/database.php";
	require "model/database.php";
	

	function create_category($active, $name) {
		global $database;
		$query = $database->query("INSERT INTO category(active, name) VALUES('$active', '$name')");

		if (!$query) {
           return false;
		}
		return true;
	}
	function create_product($sku, $active, $id_category, $name, $image, $description, $price, $stock) {
		global $database;
		$query = $database->query("INSERT INTO product(sku, active, id_category, name, image, description, price, stock) VALUES('$sku', '$active', '$id_category', '$name', '$image', '$description', '$price', '$stock')");

		if (!$query) {
           return false;
		}
		return true;
	}
	



	function delete($value, $table, $key) {
		global $database;

		$query = $database->query("DELETE FROM $table WHERE $key = $value");
		if (!$query) {
			return "An error occurred while deleting the registration.";
		}
		else if ($database->affected_rows == 0) {
			return null;
		}
		else {
			return true;
		}
	}

	function get_data($value, $table, $key) {
		global $database;

		$query = $database->query("SELECT * FROM $table WHERE $key = $value");

		if (!$query) {
			return "An error occurred while fetching the registration";
		}
		else if ($query === true || $query->num_rows == 0) {
			return null;
		}
		else {
            //die nächste Zeile einer Ergebnismenge als assoziatives Array
			$result = $query->fetch_assoc();
			return $result;
		}
	}


	function update($value, $table, $key, $sku, $active, $id_category, $name, $image, $description, $price, $stock) {
		global $database;
		if($table === "product"){
		$query = $database->query("UPDATE $table SET sku = '$sku', active = $active, id_category = $id_category, name = '$name', image = '$image', description = '$description', price = $price, stock = $stock WHERE $key = $value");
		}
		if($table === "category"){
		$query = $database->query("UPDATE $table SET active = $active, name = '$name' WHERE $key = $value");
		}
		
		if (!$query) {
			return false;
		}
		return true;
	}

	function get_all_data($table){
		global $database;
		$query = $database->query("SELECT * FROM $table");
		if (!$query) {
			return "An error occurred while fetching the registrations.";
		}
		else if ($query === true || $query->num_rows == 0) {
			return array();
		}
		
		$registrations = array();

		while ($registration = $query->fetch_assoc()) {
			$registrations[] = $registration;
		}

		return $registrations;
	}



?>