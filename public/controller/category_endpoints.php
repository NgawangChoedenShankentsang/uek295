<?php
	use Psr\Http\Message\ResponseInterface as Response;
	use Psr\Http\Message\ServerRequestInterface as Request;
	use Slim\Factory\AppFactory;
	use ReallySimpleJWT\Token;  
   
    
    
$app->post("/Authenticate", function (Request $request, Response $response, $args) { 
        $request_body_string = file_get_contents("php://input");
		//Parse the JSON string.
		$request_data = json_decode($request_body_string, true);
        $username = $request_data["username"];
        $password = $request_data["password"];
        if ($username != "admin" || $password != "sec!ReT423*&") {
            $error = array("message" => "Invalid credentials");
            echo json_encode($error);
            http_response_code(401);
            die();
        }
        $token = Token::create($username, $password, time() + 1, "localhost");
        setcookie("token", $token);
        success("Successfully Authentify", 200);
        //echo $token;
        return $response; 
});
    
    //post: Create Category

    
$app->post("/Create/Category", function (Request $request, Response $response, $args) { 
        require "controller/verify.php";
        //Read request body input string.
        // file_get_contents: ruft die POST-Rohdaten ab
		$request_body_string = file_get_contents("php://input");
		//Parse the JSON string.
		$request_data = json_decode($request_body_string, true);
        if (!isset($request_data["active"]) && !isset($request_data["name"])) {
			error("please provide active and name", 400);
		}
        if (!isset($request_data["active"]) || !is_numeric($request_data["active"])) {
            error("Please provide an integer value for active", 400);
		}
        if (!isset($request_data["name"])) {
			error("please provide name", 400);
		}
        
		
        $active = intval($request_data["active"]);
		$name = strip_tags(addslashes($request_data["name"]));
        //Make sure that the values are not empty and that the diet is one of the expected values.
		if (empty($name)) {
			error("The name field must not be empty.", 400);
		}
        //Limit the length of the name.
		if (strlen($name) > 500) {
			error("The name is too long. Please enter less than or equal to 500 characters.", 400);
		}
        if ($active < -128 || $active > 127) {
			error("The active must between -128 and 127.", 400);
		} 
		//Make sure the age is an integer.
		if (is_float($active)) {
			error("The age must not have decimals.", 400);
		}
        if (create_category($active, $name) === true) {
            success("Data are successfully created", 201);
        }
		else {
            error("An error occured while saving the category data.", 500);
        }
        return $response; 
});

   

    //get: Read
    /**
     * @OA\Get(
     *     path="/Read/Category/{category_id}",
     *     summary="Fetches a data with the given ID.",
     *     tags={"Read/Category"},
     *     @OA\Parameter(
     *         name="category_id",
     *         in="path",
     *         required=true,
     *         description="The ID of the data to fetch.",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(response="200", description="Successfully authenticated")),
     *     @OA\Response(response="401", description="Unauthorized")),
     *     @OA\Response(response="500", description="Internal server error")),
     *     @OA\Response(response="404", description="Registration not found"))
	 */
$app->get("/Read/Category/{category_id}", function (Request $request, Response $response, $args) { 
        require "controller/verify.php";
        //$response->getBody()->write("Hello, world"); 
        $category_id = intval($args["category_id"]);
        //Get the entity.
        $category = get_category($category_id);
		//Get the entity.
		if (!$category) {
            error("category_id: " . $category_id . " not found.", 404);
		}
        else if (is_string($category)){
            error($category, 500);
        }
        else {
            echo json_encode($category);
        }
        return $response; 
});



    //delete: Delete    
$app->delete("/Delete/Category/{category_id}", function (Request $request, Response $response, $args) { 
            require "controller/verify.php";
            $category_id = intval($args["category_id"]);
            $category = delete_category($category_id);
            if (!$category) {
                //No entity found.
                error("category_id: " . $category_id . " not found.", 404);
            }
            else if (is_string($category)) {
                //Error while deleting.
                error($category, 500);
            }
            else {
                //Success.
                success("category_id: " . $category_id . " is successfully deleted", 200);
            }
            return $response; 
});
     


    // put: Update        
$app->put("/Update/Category/{category_id}", function (Request $request, Response $response, $args) { 
        require "controller/verify.php";
        $category_id = $args["category_id"];
        //get the entity
        $category = get_category($category_id);
        
        if (!$category) {
            //No entity found.
            error("category_id: " . $category_id . " not found.", 404);
        }
        else if (is_string($category)) {
            error($category, 500);
        }
        $request_body_string = file_get_contents("php://input");
		//Parse the JSON string.
		$request_data = json_decode($request_body_string, true);
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
            $category["active"] = $active;
        }
        if (isset($request_data["name"])) {
            $name = strip_tags(addslashes($request_data["name"]));
        
            if(empty($name)) {
                error("The name field must not empty", 400);
            }
             if (strlen($name) > 500) {
                error("The name must be less than 500 characters.", 400);
             }
            
            $category["name"] = $name;
        }    
        
        
        if (update_category($category_id, $category["active"], $category["name"])){
            success("category_id: " . $category_id . " is successfully updated", 200);
        }
        else {
            error("an error occured while saving the data.", 500);
        }
        return $response; 
    });

$app->get("/All", function (Request $request, Response $response, $args) {
        //Check the client's authentication.
        require "controller/verify.php";
        $all_data = get_all_data();

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