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

	function delete_category($category_id) {
		global $database;

		$query = $database->query("DELETE FROM category WHERE category_id = $category_id");
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

	function get_category($category_id) {
		global $database;

		$query = $database->query("SELECT * FROM category WHERE category_id = $category_id");

		if (!$query) {
			return "An error occurred while fetching the registration";
		}
		else if ($query === true || $query->num_rows == 0) {
			return null;
		}
		else {
            //die nächste Zeile einer Ergebnismenge als assoziatives Array
			$category = $query->fetch_assoc();
			return $category;
		}
	}

	function update_category($category_id, $active, $name) {
		global $database;

		$query = $database->query("UPDATE category SET active = $active, name = '$name' WHERE category_id = $category_id");

		if (!$query) {
			return false;
		}
		return true;
	}

	function get_all_data(){
		global $database;
		$query = $database->query("SELECT * FROM category");
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