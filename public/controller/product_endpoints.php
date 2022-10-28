<?php
	use Psr\Http\Message\ResponseInterface as Response;
	use Psr\Http\Message\ServerRequestInterface as Request;
	use Slim\Factory\AppFactory;
	use ReallySimpleJWT\Token;  
   
/**
 * @OA\Get(
 *     path="/Read/Product/{product_id}",
 *     summary="Fetches a data with the given ID",
 *     tags={"Product"},
 *     @OA\Parameter(
 *         name="product_id",
 *         in="path",
 *         required=true,
 *         description="The ID of the product to fetch",
 *         @OA\Schema(
 *             type="integer",
 *             example="1"
 *         )
 *     ),
 *     @OA\Response(response="200", description="Successfully authenticated"),
 *     @OA\Response(response="401", description="Invalid credentials/ Unauthorized"),
 *     @OA\Response(response="500", description="Internal server error"),
 *     @OA\Response(response="404", description="product_id not found"))
*/
$app->get("/Read/Product/{product_id}", function (Request $request, Response $response, $args) { 
        //checking authentification.
        require "controller/verify.php";

        //assigning parameter value into variable.
        $product_id = intval($args["product_id"]);

        //Get the entity.
        //sending in parameter (value, table name, key).
        $product = get_data($product_id, "product", "product_id");
		if (!$product) {
            //No entity found.
            error("product_id: " . $product_id . " not found.", 404);
		}
        else if (is_string($product)){
            //error while fetching
            error($product, 500);
        }
        else {
            //success
            echo json_encode($product);
        }
        return $response; 
});
   
/**
 * @OA\Post(
 *     path="/Create/Product",
 *     summary="Used to  obtain an data that will be stored in the product Table.",
 *     tags={"Product"},
 *     requestBody=@OA\RequestBody(
 *         request="/Create/Product",
 *         required=true,
 *         description="Data to create into product Table",
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 @OA\Property(property="sku", type="VARCHER(100)", example="Namaste"),                
 *                 @OA\Property(property="active", type="TINYINT(1)", example="12"),
 *                 @OA\Property(property="id_category", type="INTEGER(11)", example="12"),
 *                 @OA\Property(property="name", type="VARCHAR(500)", example="Namaste"),
 *                 @OA\Property(property="image", type="VARCHER(1000)", example="Namaste"),
 *                 @OA\Property(property="description", type="TEXT", example="Namaste"),
 *                 @OA\Property(property="price", type="DECIMAL(65.2)", example="12.22"),
 *                 @OA\Property(property="id_category", type="INTEGER(11)", example="12")
 *             )
 *          )
 *     ),
 *     @OA\Response(response="201", description="Successfully created"),
 *     @OA\Response(response="200", description="Successfully authenticated"),
 *     @OA\Response(response="401", description="Invalid credentials/ Unauthorized"),
 *     @OA\Response(response="400", description="Bad Request/ Unknow key values"),
 *     @OA\Response(response="500", description="Internal server error"))
 * )
*/
$app->post("/Create/Product", function (Request $request, Response $response, $args) { 
    //checking the authentification.
    require "controller/verify.php";

    //Read request body input string.
    // wiil get the raw data from request body.
    $request_body_string = file_get_contents("php://input");

    //Parse the JSON string.
    $request_data = json_decode($request_body_string, true);

    //if there is nothing in request bodyl, retun 400.
    if (!isset($request_data["sku"]) && !isset($request_data["active"]) && !isset($request_data["id_category"]) 
        && !isset($request_data["name"]) && !isset($request_data["image"]) && !isset($request_data["description"]) 
        && !isset($request_data["price"]) && !isset($request_data["stock"])) {
        error("Please provide names and values to create", 400);
    }

    //making sure sku is not empty.
    if (!isset($request_data["sku"])) {
        error("Please provide sku", 400);
    }
    
    //making sure acitve is numeric or not empty.
    if (!isset($request_data["active"]) || !is_numeric($request_data["active"])) {
        error("Please provide active", 400);
    }

    //making sure id is numeric or not empty
    if (!isset($request_data["id_category"]) || !is_numeric($request_data["id_category"])) {
        error("Please provide id_category", 400);
    }

    //making sure name is not empty.
    if (!isset($request_data["name"])) {
        error("please provide name", 400);
    }

    //making sure image is not empty.
    if (!isset($request_data["image"])) {
        error("please provide image", 400);
    }

    //making sure description is not empty.
    if (!isset($request_data["description"])) {
        error("please provide description", 400);
    }

    //making sure price is numeric or not empty.
    if (!isset($request_data["price"]) || !is_numeric($request_data["price"])) {
        error("Please provide price", 400);
    }

    //making sure stock is numeric or not empty.
    if (!isset($request_data["stock"]) || !is_numeric($request_data["stock"])) {
        error("Please provide stock", 400);
    }

    //assigning value to $fk.
    $fk = $request_data["id_category"];

    //get entity.
    //it will search into table category, whethere the id_category value existed in category_id.
    //sending in parameter (value, table name, key).
    $serach_pk = get_data($fk, "category", "category_id");
    if(!$serach_pk){
        //not entity found
        error("The value of id_category is not found in Primary-Key category_id of table category", 404);
    }
    else if (is_string(!$serach_pk)){
        //error while fetching
        error($serach_pk, 500);
    }
    //assigning values into variables.
    $sku = strip_tags(addslashes($request_data["sku"]));
    $active = intval($request_data["active"]);
    $id_category = intval($request_data["id_category"]);
    $name = strip_tags(addslashes($request_data["name"]));
    $image = strip_tags(addslashes($request_data["image"]));
    $description = strip_tags(addslashes($request_data["description"]));
    $price = floatval($request_data["price"]);
    $stock = intval($request_data["stock"]);

    //if sku is empty, return 400.
    if (empty($sku)) {
        error("The sku field must not be empty.", 400);
    }

    //Limit the length of the sku.
    if (strlen($sku) > 100) {
        error("The name is too long. Please enter less than or equal to 500 characters.", 400);
    }

    //limiting length of active.
    if ($active < -128 || $active > 127) {
        error("The active must between -128 and 127.", 400);
    } 

    //Make sure the active is integer.
    if (is_float($active)) {
        error("The age must not have decimals.", 400);
    }
 
    //Make sure the id_category is integer.
    if (is_float($id_category)) {
        error("The id_category must not have decimals.", 400);
    }

    //if name is empty, return 400.
    if (empty($name)) {
        error("The name field must not be empty.", 400);
    }

    //Limit the length of the name.
    if (strlen($name) > 500) {
        error("The name is too long. Please enter less than or equal to 500 characters.", 400);
    }
    
    //if image is empty, return 400.
    if (empty($image)) {
        error("The image field must not be empty.", 400);
    }

    //Limit the length of price .
    if ($price < 0 || $price > 65.2) {
        error("The price must between 0 and 65,2.", 400);
    } 

    //Make sure the active is decimal.
    if (is_int($price)) {
        error("The price must not have interger.", 400);
    }
   
    //Limit the length of stock
    if ($stock < 0 || $stock > 11) {
        error("The stock must between 0 and 11.", 400);
    } 

    //Make sure the stock is integer
    if (is_float($stock)) {
        error("The stock must not have decimals.", 400);
    }

    //saving the data
    if (create("product", $sku, $active, $id_category, $name, $image, $description, $price, $stock) === true) {
        success("Data are successfully created", 201);
    }
    else {
        error("An error occured while saving the category data.", 500);
    }
    return $response; 
});

/**
 * @OA\Delete(
 *     path="/Delete/Product/{product_id}",
 *     summary="it will  delete ",
 *     tags={"Product"},
 *     @OA\Parameter(
 *         name="product_id",
 *         in="path",
 *         required=true,
 *         description="Id will be fetch and deleted",
 *         @OA\Schema(
 *             type="Integer",
 *             example="1"
 *         )
 *     ),
 * 
 *     @OA\Response(response="404", description="product_id not found"),
 *     @OA\Response(response="500", description="Internal server error"),
 *     @OA\Response(response="401", description="Invalid credentials/ Unauthorized"),
 *     @OA\Response(response="200", description="OK: executed/Successfully authenticated"))
 * ) 
*/
$app->delete("/Delete/Product/{product_id}", function (Request $request, Response $response, $args) { 
    //checking the authentification.
    require "controller/verify.php";

    //assigning the value into variable.
    $product_id = intval($args["product_id"]);

    //delete the entity.
    //Sending parameter (value, table name , key).
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

/**
 * @OA\Put(
 *     path="/Update/Product/{product_id}",
 *     summary="It will update data from Product table",
 *     tags={"Product"},
 *     @OA\Parameter(
 *         name="product_id",
 *         in="path",
 *         required=true,
 *         description="it will find the matches product_id and update it",
 *         @OA\Schema(
 *             type="Int",
 *             example="1"
 *         )
 *     ),
 *     requestBody=@OA\RequestBody(
 *         request="/Update/Product/{product_id}",
 *         required=true,
 *         description="Data which will be updata in json format",
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 @OA\Property(property="active", type="TINYINT(1)", example=12),
 *                 @OA\Property(property="name", type="VARCHER(500)", example="Namaste")
 *             )
 *         )
 *     ),
 *     @OA\Response(response="401", description="Unauthorized"),
 *     @OA\Response(response="404", description="product_id not found"),
 *     @OA\Response(response="500", description="Internal server error"),
 *     @OA\Response(response="400", description="Bad Request/ Unknow key values"),
 *     @OA\Response(response="200", description="OK: executed/Successfully authenticated"))
 * )
*/
$app->put("/Update/Product/{product_id}", function (Request $request, Response $response, $args) { 
    //checking the authentification.
    require "controller/verify.php";

    //assigning the value into variable.
    $product_id = $args["product_id"];

    //get the entity.
    $product = get_data($product_id, "product", "product_id");
    
    if (!$product) {
        //No entity found.
        error("product_id: " . $product_id . " not found.", 404);
    }
    else if (is_string($product)) {
        //error while fetching.
        error($product, 500);
    }

    //fetching the data from request body.
    $request_body_string = file_get_contents("php://input");

    //Parse the JSON string.
    $request_data = json_decode($request_body_string, true);

    //making sure all the data are written in request body.
    if (!isset($request_data["sku"]) && !isset($request_data["active"]) && !isset($request_data["id_category"]) && !isset($request_data["name"]) && !isset($request_data["image"]) && !isset($request_data["description"]) && !isset($request_data["price"]) && !isset($request_data["stock"])) {
        error("Please provide names and values to update", 400);
    }
    //making sure sku is set.
    if (isset($request_data["sku"])) {
        $sku = strip_tags(addslashes($request_data["sku"]));
        
        //making sure sku is not empty.
        if(empty($sku)) {
            error("The sku field must not empty", 400);
        }

        //limit the length of sku.
         if (strlen($sku) > 100) {
            error("The sku must be less than 100 characters.", 400);
         }

        //assigning into associative array.
        $product["sku"] = $sku;
    }

    //making sure active is set.
    if (isset($request_data["active"])) {

        //making sure active is numeric.
        if (!is_numeric($request_data["active"])){
            error("active must have integer value", 400);
        }

        //assigning value into variable.
        $active = intval($request_data["active"]);

        //Limit the range of the age.
        if ($active < -128 || $active > 127) {
            error("The active must be between -128 and 127", 400);
        }

        //Make sure the age is an TinyInt(1).
        if (is_float($active)) {
            error("The active cann't have decimal values.", 400);
        }

        //assigning into associative array.
        $product["active"] = $active;
    }

    //making sure id is set.
    if (isset($request_data["id_category"])) {

        //making sure id is numeric.
        if (!is_numeric($request_data["id_category"])){
            error("id_category must have integer value", 400);
        }

        //assigning value into variable.
        $id_category = intval($request_data["id_category"]);

        //get entity: it will search into table category, whethere the id_category value existed in category_id.
        //Sending parameter (value, table name , key).
        
        $serach_pk = get_data($id_category, "category", "category_id");
        if(!$serach_pk){
            //no entity found.
            error("The value of id_category is not found in Primary-Key category_id of table category", 404);
        }
        else if (is_string(!$serach_pk)){
            //error while fetching.
            error($serach_pk, 500);
        }

        //Make sure the age is an TinyInt(1).
        if (is_float($id_category)) {
            error("The id_category cann't have decimal values.", 400);
        }

        //assigning into associative array.
        $product["id_category"] = $id_category;
    }
    
    //making sure name is set.
    if (isset($request_data["name"])) {

        //assigning into variable.
        $name = strip_tags(addslashes($request_data["name"]));
        
        //making sure name is not empty.
        if(empty($name)) {
            error("The name field must not empty", 400);
        }

        //limit length of name.
         if (strlen($name) > 500) {
            error("The name must be less than 500 characters.", 400);
         }
        
         //assigning into associative array.
        $product["name"] = $name;
    }    
    
    //making sure image is set.
    if (isset($request_data["image"])) {

        //assingning into variable.
        $image = strip_tags(addslashes($request_data["image"]));
        
        //making sure image is not empty.
        if(empty($image)) {
            error("The image field must not empty", 400);
        }

        //limit the length of image.
         if (strlen($image) > 1000) {
            error("The image must be less than 1000 characters.", 400);
         }
        
         //assigning into associative array.
        $product["image"] = $image;
    } 

    //making sure description is set.
    if (isset($request_data["description"])) {

        //assigning into variable.
        $description = strip_tags(addslashes($request_data["description"]));
        
        //making sure description is not empty.
        if(empty($description)) {
            error("The description field must not empty", 400);
        }
        
        //assigning into associative array.
        $product["description"] = $description;
    } 

    //making sure price is set.
    if (isset($request_data["price"])) {

        //making sure price is numeric.
        if (!is_numeric($request_data["price"])){
            error("price must have numeric value", 400);
        }

        //assigning into variable.
        $price = floatval($request_data["price"]);
        
        //Limit the range of the age.
        if ($price < 0 || $price > 65.2) {
            error("The price must be between 0 and 65.2", 400);
        }
        
        //Make sure the price is an decimal.
        if (is_int($price)) {
            error("The price cann't have interger values.", 400);
        }
        
        //assigning into associative array.
        $product["price"] = $price;
    }

    //making sure stock is set.
    if (isset($request_data["stock"])) {

        //making sure stock is numeric.
        if (!is_numeric($request_data["stock"])){
            error("stock must have integer value", 400);
        }

        //assigning into variable.
        $stock = intval($request_data["stock"]);
        
        //Limit the range of the age.
        if ($stock < 0 || $stock > 11) {
            error("The stock must be between 0 and 11", 400);
        }
        
        //Make sure the stock is an integer.
        if (is_float($stock)) {
            error("The stock cann't have decimal values.", 400);
        }
        
        //assigning into associative array.
        $product["stock"] = $stock;
    }

    //saving the data.
    if (update($product_id, "product", "product_id", $product["sku"], $product["active"], $product["id_category"], $product["name"], $product["image"], $product["description"], $product["price"], $product["stock"])){
        success("product_id: " . $product_id . " is successfully updated", 200);
    }
    else {
        error("an error occured while saving the data.", 500);
    }
    return $response; 
});

/**
 * @OA\Get(
 *     path="/All/Product",
 *     summary="All will show up in response body",
 *     tags={"Product"},
 *     @OA\Parameter(
 *         name="product_id",
 *         in="path",
 *         required=true,
 *         description="Fetches all the data from Product table",
 *         @OA\Schema(
 *             type="NULL",
 *             example="NULL"
 *         )
 *     ),
 *     @OA\Response(response="200", description="Successfully authenticated"),
 *     @OA\Response(response="401", description="Unauthorized"),
 *     @OA\Response(response="200", description="Successfully authenticated"),
 *     @OA\Response(response="500", description="Internal server error"))
*/
$app->get("/All/Product", function (Request $request, Response $response, $args) {
    //Check the client's authentication.
    require "controller/verify.php";
    
    //get entities.
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