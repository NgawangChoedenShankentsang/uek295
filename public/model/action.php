<?php
	//Connect to database.
	require "model/database.php";


	/**
	 * Creates a new registration entity with the given values.
	 * @param $table hold the string value product or category.
	 * @param $sku The sku of the product.
	 * @param $active The active of the product or category.
	 * @param $id_category the id_category of the product.
	 * @param $name The name of product or category.
	 * @param $image The image of product.
	 * @param $description The description of product.
	 * @param $price the price of product.
	 * @param $stock the stock of product.
	 * @return true on success, false otherwise.
	 */
	function create($table, $sku, $active, $id_category, $name, $image, $description, $price, $stock) {
		global $database;
		
		//if $table is identical as product, it will do the query.
		if ($table === "product"){
			$query = $database->query("INSERT INTO product(sku, active, id_category, name, image, description, price, stock) 
			VALUES('$sku', '$active', '$id_category', '$name', '$image', '$description', '$price', '$stock')");
		}
		
		//if $table is identical as category, it will do the query.
		if ($table === "category"){
			$query = $database->query("INSERT INTO category(active, name) VALUES('$active', '$name')");
		}

		if (!$query) {
			//not found
           return false;
		}
		return true;
	}
	

	/**
	 * Deletes the data for the given ID.
	 * @param $value hold the ID of the existing category or product.
	 * @param $table hold the string value product or category.
	 * @param $key hold the the string value product_id or category_id.
	 * @return true on success or null if no registration was found with this ID or a string if an error occurred.
	 */
	function delete($value, $table, $key) {
		global $database;

		$query = $database->query("DELETE FROM $table WHERE $key = $value");

		if (!$query) {
			//not found
			return "An error occurred while deleting the registration.";
		}
		else if ($database->affected_rows == 0) {
			return null;
		}
		else {
			return true;
		}
	}


	/**
	 * Get the data from the givven ID
	 * @param $value hold the ID of the existing category or product.
	 * @param $table hold the string value  product or category.
	 * @param $key hold the the string value product_id or category_id.
	 * @return The data entity as an associative array or null if no data was found with this ID or a string if an error occurred.
	 */
	function get_data($value, $table, $key) {
		global $database;

		$query = $database->query("SELECT * FROM $table WHERE $key = $value");

		if (!$query) {
			//not found.
			return "An error occurred while fetching the registration";
		}
		else if ($query === true || $query->num_rows == 0) {
			return null;
		}
		else {
            //the next row of a result set as an associative array.
			$result = $query->fetch_assoc();
			return $result;
		}
	}


	/**
	 * Updates an existing registration entity with the given values.
	 * @param $value hold the ID of the existing category or product.
	 * @param $table hold the string value  product or category.
	 * @param $key hold the the string value product_id or category_id.
	 * @param $sku The sku of the product.
	 * @param $active The active of the product or category.
	 * @param $id_category the id_category of the product.
	 * @param $name The name of product or category.
	 * @param $image The image of product.
	 * @param $description The description of product.
	 * @param $price the price of product.
	 * @param $stock the stock of product.
	 * @return true on success, false otherwise.
	 */
	function update($value, $table, $key, $sku, $active, $id_category, $name, $image, $description, $price, $stock) {
		global $database;

		//if $table is identical as product, it will do the query.
		if($table === "product"){
		$query = $database->query("UPDATE $table SET sku = '$sku', active = $active, id_category = $id_category, name = '$name', 
		image = '$image', description = '$description', price = $price, stock = $stock WHERE $key = $value");
		}

		//if $table is identical as category, it will do the query.
		if($table === "category"){
		$query = $database->query("UPDATE $table SET active = $active, name = '$name' WHERE $key = $value");
		}
		
		if (!$query) {
			//not found.
			return false;
		}
		return true;
	}


	/**
	 * first it will check from parameter which table have called this function
	 * second it will get all data from the database.
	 * @param $table hold the string product or category
	 * @return An array containing all data or a string if an error occurred.
	 */
	function get_all_data($table){
		global $database;
		$query = $database->query("SELECT * FROM $table");
		if (!$query) {
			//not found.
			return "An error occurred while fetching the registrations.";
		}
		else if ($query === true || $query->num_rows == 0) {
			return array();
		}
		
		$datas = array();

		while ($data = $query->fetch_assoc()) {
			$datas[] = $data;
		}
		return $datas;
	}



?>