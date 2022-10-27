<?php
	use Psr\Http\Message\ResponseInterface as Response;
	use Psr\Http\Message\ServerRequestInterface as Request;
	use Slim\Factory\AppFactory;
	use ReallySimpleJWT\Token;  
   


$app->get("/Read/Product/{product_id}", function (Request $request, Response $response, $args) { 
        require "controller/verify.php";
        $product_id = intval($args["product_id"]);
        //Get the entity.
        $product = get_data($product_id, "product", "product_id");
		//Get the entity.
		if (!$product) {
            error("product_id: " . $product_id . " not found.", 404);
		}
        else if (is_string($product)){
            error($product, 500);
        }
        else {
            echo json_encode($product);
        }
        return $response; 
});

$app->post("/Create/Product", function (Request $request, Response $response, $args) { 
    require "controller/verify.php";
    //Read request body input string.
    // wiil get the raw data from request body
    $request_body_string = file_get_contents("php://input");
    //Parse the JSON string.
    $request_data = json_decode($request_body_string, true);

    if (!isset($request_data["sku"]) && !isset($request_data["active"]) && !isset($request_data["id_category"]) && !isset($request_data["name"]) && !isset($request_data["image"]) && !isset($request_data["description"]) && !isset($request_data["price"]) && !isset($request_data["stock"])) {
        error("Please provide names and values to create", 400);
    }
    if (!isset($request_data["sku"])) {
        error("Please provide sku", 400);
    }
    if (!isset($request_data["active"]) || !is_numeric($request_data["active"])) {
        error("Please provide active", 400);
    }
    if (!isset($request_data["id_category"]) || !is_numeric($request_data["id_category"])) {
        error("Please provide id_category", 400);
    }
    if (!isset($request_data["name"])) {
        error("please provide name", 400);
    }
    if (!isset($request_data["image"])) {
        error("please provide image", 400);
    }
    if (!isset($request_data["description"])) {
        error("please provide description", 400);
    }
    if (!isset($request_data["price"]) || !is_numeric($request_data["price"])) {
        error("Please provide price", 400);
    }
    if (!isset($request_data["stock"]) || !is_numeric($request_data["stock"])) {
        error("Please provide stock", 400);
    }

    $fk = $request_data["id_category"];
    $serach_pk = get_data($fk, "category", "category_id");
    if(!$serach_pk){
        error("The value of id_category is not found in Primary-Key category_id of table category", 404);
    }
    else if (is_string(!$serach_pk)){
        error($serach_pk, 500);
    }

    $sku = strip_tags(addslashes($request_data["sku"]));
    $active = intval($request_data["active"]);
    $id_category = intval($request_data["id_category"]);
    $name = strip_tags(addslashes($request_data["name"]));
    $image = strip_tags(addslashes($request_data["image"]));
    $description = strip_tags(addslashes($request_data["description"]));
    $price = floatval($request_data["price"]);
    $stock = intval($request_data["stock"]);

    if (empty($sku)) {
        error("The sku field must not be empty.", 400);
    }
    //Limit the length of the sku.
    if (strlen($sku) > 100) {
        error("The name is too long. Please enter less than or equal to 500 characters.", 400);
    }

    if ($active < -128 || $active > 127) {
        error("The active must between -128 and 127.", 400);
    } 
    //Make sure the active is integer
    if (is_float($active)) {
        error("The age must not have decimals.", 400);
    }

    if ($id_category < 0 || $id_category > 11) {
        error("The active must between 0 and 11.", 400);
    } 
    //Make sure the active is integer
    if (is_float($id_category)) {
        error("The id_category must not have decimals.", 400);
    }
    //Make sure that the values are not empty and that the diet is one of the expected values.
    if (empty($name)) {
        error("The name field must not be empty.", 400);
    }
    //Limit the length of the name.
    if (strlen($name) > 500) {
        error("The name is too long. Please enter less than or equal to 500 characters.", 400);
    }

    if (empty($image)) {
        error("The image field must not be empty.", 400);
    }

    if ($price < 0 || $price > 65.2) {
        error("The price must between 0 and 65,2.", 400);
    } 
    //Make sure the active is decimal
    if (is_int($price)) {
        error("The price must not have interger.", 400);
    }
   
    if ($stock < 0 || $stock > 11) {
        error("The active must between 0 and 11.", 400);
    } 
    //Make sure the active is integer
    if (is_float($stock)) {
        error("The stock must not have decimals.", 400);
    }

    if (create_product($sku, $active, $id_category, $name, $image, $description, $price, $stock) === true) {
        success("Data are successfully created", 201);
    }
    else {
        error("An error occured while saving the category data.", 500);
    }
    return $response; 
});


$app->delete("/Delete/Product/{product_id}", function (Request $request, Response $response, $args) { 
    require "controller/verify.php";
    $product_id = intval($args["product_id"]);
    $product = delete($product_id, "product", "product_id");

    if (!$product) {
        //No entity found.
        error("product_id: " . $product_id . " not found.", 404);
    }
    else if (is_string($product)) {
        //Error while deleting.
        error($product, 500);
    }
    else {
        //Success.
        success("product_id: " . $product_id . " is successfully deleted", 200);
    }
    return $response; 
});


$app->put("/Update/Product/{product_id}", function (Request $request, Response $response, $args) { 
    require "controller/verify.php";
    $product_id = $args["product_id"];
    //get the entity
    $product = get_data($product_id, "product", "product_id");
    
    if (!$product) {
        //No entity found.
        error("product_id: " . $product_id . " not found.", 404);
    }
    else if (is_string($product)) {
        error($product, 500);
    }
    $request_body_string = file_get_contents("php://input");
    //Parse the JSON string.
    $request_data = json_decode($request_body_string, true);
    if (!isset($request_data["sku"]) && !isset($request_data["active"]) && !isset($request_data["id_category"]) && !isset($request_data["name"]) && !isset($request_data["image"]) && !isset($request_data["description"]) && !isset($request_data["price"]) && !isset($request_data["stock"])) {
        error("Please provide names and values to update", 400);
    }
    //sku
    if (isset($request_data["sku"])) {
        $sku = strip_tags(addslashes($request_data["sku"]));
    
        if(empty($sku)) {
            error("The sku field must not empty", 400);
        }
         if (strlen($sku) > 100) {
            error("The sku must be less than 100 characters.", 400);
         }
        
        $product["sku"] = $sku;
    }  
    //active
    if (isset($request_data["active"])) {

        if (!is_numeric($request_data["active"])){
            error("active must have integer value", 400);
        }
        $active = intval($request_data["active"]);
        //Limit the range of the age.
        if ($active < -128 || $active > 127) {
            error("The active must be between -128 and 127", 400);
        }
        //Make sure the age is an TinyInt(1).
        if (is_float($active)) {
            error("The active cann't have decimal values.", 400);
        }
        $product["active"] = $active;
    }
    //id_category
    if (isset($request_data["id_category"])) {

        if (!is_numeric($request_data["id_category"])){
            error("id_category must have integer value", 400);
        }
        $id_category = intval($request_data["id_category"]);

        $serach_pk = get_data($id_category, "category", "category_id");
        if(!$serach_pk){
            error("The value of id_category is not found in Primary-Key category_id of table category", 404);
        }
        else if (is_string(!$serach_pk)){
            error($serach_pk, 500);
        }

        //Limit the range of the age.
        if ($id_category < 0 || $id_category > 11) {
            error("The id_category must be between 0 and 11", 400);
        }
        //Make sure the age is an TinyInt(1).
        if (is_float($id_category)) {
            error("The id_category cann't have decimal values.", 400);
        }
        $product["id_category"] = $id_category;
    }
    //name
    if (isset($request_data["name"])) {
        $name = strip_tags(addslashes($request_data["name"]));
    
        if(empty($name)) {
            error("The name field must not empty", 400);
        }
         if (strlen($name) > 500) {
            error("The name must be less than 500 characters.", 400);
         }
        
        $product["name"] = $name;
    }    
    //image
    if (isset($request_data["image"])) {
        $image = strip_tags(addslashes($request_data["image"]));
    
        if(empty($image)) {
            error("The image field must not empty", 400);
        }
         if (strlen($image) > 1000) {
            error("The image must be less than 1000 characters.", 400);
         }
        
        $product["image"] = $image;
    } 
    //description
    if (isset($request_data["description"])) {
        $description = strip_tags(addslashes($request_data["description"]));
    
        if(empty($description)) {
            error("The description field must not empty", 400);
        }
        
        $product["description"] = $description;
    } 
    //price
    if (isset($request_data["price"])) {

        if (!is_numeric($request_data["price"])){
            error("price must have numeric value", 400);
        }
        $price = floatval($request_data["price"]);
        //Limit the range of the age.
        if ($price < 0 || $price > 65.2) {
            error("The price must be between 0 and 65.2", 400);
        }
        //Make sure the price is an decimal.
        if (is_int($price)) {
            error("The price cann't have interger values.", 400);
        }
        $product["price"] = $price;
    }
    //stock
    if (isset($request_data["stock"])) {

        if (!is_numeric($request_data["stock"])){
            error("stock must have integer value", 400);
        }
        $stock = intval($request_data["stock"]);
        //Limit the range of the age.
        if ($stock < 0 || $stock > 11) {
            error("The stock must be between 0 and 11", 400);
        }
        //Make sure the stock is an integer.
        if (is_float($stock)) {
            error("The stock cann't have decimal values.", 400);
        }
        $product["stock"] = $stock;
    }

    if (update($product_id, "product", "product_id", $product["sku"], $product["active"], $product["id_category"], $product["name"], $product["image"], $product["description"], $product["price"], $product["stock"])){
        success("product_id: " . $product_id . " is successfully updated", 200);
    }
    else {
        error("an error occured while saving the data.", 500);
    }
    return $response; 
});

$app->get("/All/Product", function (Request $request, Response $response, $args) {
    //Check the client's authentication.
    require "controller/verify.php";
    $all_data = get_all_data("product");

    if (is_string($all_data)) {
        //Error while fetching.
        error($all_data, 500);
    }
    else {
        //Success.
        echo json_encode($all_data);
    }

    return $response;
});


?>