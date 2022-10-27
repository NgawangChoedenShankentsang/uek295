<?php
	use Psr\Http\Message\ResponseInterface as Response;
	use Psr\Http\Message\ServerRequestInterface as Request;
	use Slim\Factory\AppFactory;
	use ReallySimpleJWT\Token;  
   
/**
 * @OA\Post(
 *     path="/Authenticate",
 *     summary="Used to authenticate and obtain an access token that will be stored in the cookies.",
 *     tags={"Autheticate"},
 *     requestBody=@OA\RequestBody(
 *         request="/Authenticate",
 *         required=true,
 *         description="The credentials are passed to the server via the request body.",
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 @OA\Property(property="username", type="string", example="admin"),
 *                 @OA\Property(property="password", type="string", example="sec!ReT423*&")
 *             )
 *         )
 *     ),
 *     @OA\Response(response="200", description="Successfully authenticated"),
 *     @OA\Response(response="401", description="Invalid credentials"))
 * )
*/   
$app->post("/Authenticate", function (Request $request, Response $response, $args) { 
        global $api_username;
        global $api_password;

        //fetch data from request body.
        $request_body_string = file_get_contents("php://input");

		//Parse the JSON string.
		$request_data = json_decode($request_body_string, true);

        //assigning variable.
        $username = $request_data["username"];
        $password = $request_data["password"];

        //if either the username or the password is incorrect, return a 401 error.
        if ($username != $api_username || $password != $api_password) {
            $error = array("message" => "Invalid credentials");
            error("Invalid credential", 401);
        }
        //Generate the access token and store it in the cookies.
        $token = Token::create($username, $password, time() + 3600, "localhost");
        setcookie("token", $token);
        //if the username and password is correct, return a 200.
        success("Successfully Authentify", 200);
        return $response; 
});
    

/**
 * @OA\Post(
 *     path="/Create/Category",
 *     summary="Used to  obtain an data that will be stored in the Category Table.",
 *     tags={"Category"},
 *     requestBody=@OA\RequestBody(
 *         request="/Create/Category",
 *         required=true,
 *         description="Data to create into category Table",
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 @OA\Property(property="active", type="TINYINT(1)", example="12"),
 *                 @OA\Property(property="name", type="VARCHAR(500)", example="Namaste")
 *             )
 *          )
 *     ),
 *     @OA\Response(response="201", description="Successfully created"),
 *     @OA\Response(response="200", description="Successfully authenticated"),
 *     @OA\Response(response="401", description="Invalid credentials"),
 *     @OA\Response(response="400", description="Bad Request/ Unknow key values"),
 *     @OA\Response(response="500", description="Internal server error"))
 * )
*/ 
$app->post("/Create/Category", function (Request $request, Response $response, $args) { 
        //Checking authentication
        require "controller/verify.php";

        //fetching data from request data.
		$request_body_string = file_get_contents("php://input");

		//Parse the JSON string.
		$request_data = json_decode($request_body_string, true);

        //if either active and name is no set, return 400.
        if (!isset($request_data["active"]) && !isset($request_data["name"])) {
			error("please provide active and name", 400);
		}

        //if active is no set or is not numerical, return 400.
        if (!isset($request_data["active"]) || !is_numeric($request_data["active"])) {
            error("Please provide an integer value for active", 400);
		}

        //if name is integer, return 400.
        if (is_integer($request_data["name"])) {
			error("The name field must be string", 400);
		}
        
        //Sanitizing the values where neccesary.
        $active = intval($request_data["active"]);
		$name = strip_tags(addslashes($request_data["name"]));

        //Make sure that the values are not empty and integer.
        if (is_int($name)){
            error("The name field must be string", 400);
        }
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

		//Make sure the active is an integer.
		if (is_float($active)) {
			error("The age must not have decimals.", 400);
		}

        //if both are true, return 201.
        if (create("category", "", $active, "", $name, "", "", "", "") === true) {
            success("Data are successfully created", 201);
        }

        //if return is false than, return 500.
		else {
            error("An error occured while saving the category data.", 500);
        }
        return $response; 
});

/**
 * @OA\Get(
 *     path="/Read/Category/{category_id}",
 *     summary="Fetches a data with the given ID",
 *     tags={"Category"},
 *     @OA\Parameter(
 *         name="category_id",
 *         in="path",
 *         required=true,
 *         description="The ID of the category to fetch",
 *         @OA\Schema(
 *             type="integer",
 *             example="1"
 *         )
 *     ),
 *     @OA\Response(response="200", description="Successfully authenticated"),
 *     @OA\Response(response="401", description="Unauthorized"),
 *     @OA\Response(response="500", description="Internal server error"),
 *     @OA\Response(response="404", description="category_id not found"))
*/
$app->get("/Read/Category/{category_id}", function (Request $request, Response $response, $args) { 
        //checking the authentication.
        require "controller/verify.php";

        //assingning value.
        $category_id = intval($args["category_id"]);

        //Get the entity.
        $category = get_data($category_id, "category", "category_id");

		if (!$category) {
            //no entity found.
            error("category_id: " . $category_id . " not found.", 404);
		}
        else if (is_string($category)){
            //error while fetching.
            error($category, 500);
        }
        else {
            //success.
            echo json_encode($category);
        }
        return $response; 
});

    
/**
 * @OA\Delete(
 *     path="/Delete/Category/{category_id}",
 *     summary="it will be delete ",
 *     tags={"Category"},
 *     @OA\Parameter(
 *         name="category_id",
 *         in="path",
 *         required=true,
 *         description="Id will be fetch and deleted",
 *         @OA\Schema(
 *             type="Integer",
 *             example="1"
 *         )
 *     ),
 *     @OA\Response(response="404", description="category_id not found"),
 *     @OA\Response(response="500", description="Internal server error"),
 *     @OA\Response(response="401", description="Unauthorized"),
 *     @OA\Response(response="200", description="OK: executed))
 * ) 
*/
$app->delete("/Delete/Category/{category_id}", function (Request $request, Response $response, $args) { 
            //checking the authentification.
            require "controller/verify.php";

            //assinging parameter value in variable.
            $category_id = intval($args["category_id"]);

            //Delete the entity.
            $category = delete($category_id, "category", "category_id");

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
     
/**
 * @OA\Put(
 *     path="/Update/Category/{category_id}",
 *     summary="It will update data from category table",
 *     tags={"Category"},
 *     @OA\Parameter(
 *         name="category_id",
 *         in="path",
 *         required=true,
 *         description="it will find the matches category_id and update it",
 *         @OA\Schema(
 *             type="Int",
 *             example="1"
 *         )
 *     ),
 *     requestBody=@OA\RequestBody(
 *         request="/Update/Category/{category_id}",
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
 *     @OA\Response(response="404", description="category_id not found"),
 *     @OA\Response(response="500", description="Internal server error"),
 *     @OA\Response(response="400", description="Bad Request/ Unknow key values"),
 *     @OA\Response(response="200", description="Erklärung der Antwort mit Status 200"))
 * )
*/

$app->put("/Update/Category/{category_id}", function (Request $request, Response $response, $args) { 
        //checking the authentification
        require "controller/verify.php";

        //assigning parameter value in variable.
        $category_id = $args["category_id"];

        //get the entity.
        $category = get_data($category_id, "category", "category_id");
        
        if (!$category) {
            //No entity found.
            error("category_id: " . $category_id . " not found.", 404);
        }
        else if (is_string($category)) {
            //error while fetching.
            error($category, 500);
        }

        //fetching the request body data.
        $request_body_string = file_get_contents("php://input");

		//Parse the JSON string.
		$request_data = json_decode($request_body_string, true);

        //put the updated information into the fetched entity.
        if (isset($request_data["active"])) {
            //making sure that active is numeric.
            if (!is_numeric($request_data["active"])){
                error("active must have integer value", 400);
            }

            //sanitize the active.
            $active = intval($request_data["active"]);

            //Limit the range of the active.
			if ($active < -128 || $active > 127) {
				error("The active must be between -128 and 127", 400);
			}

			//Make sure the age is an TinyInt(1).
			if (is_float($active)) {
				error("The active cann't have decimal values.", 400);
			}
            
            //assigning into associative array.
            $category["active"] = $active;
        }
        if (isset($request_data["name"])) {
            
            //sanitize the name
            $name = strip_tags(addslashes($request_data["name"]));
            
            //making sure that name is not empty.
            if (empty($name)) {
                error("The name field must not empty", 400);
            }

            //Limit the range of the name.
            if (strlen($name) > 500) {
                error("The name must be less than 500 characters.", 400);
             }
            
             //assigning into associative array.
            $category["name"] = $name;
        }    
        
        //saving the data.
        if (update($category_id, "category", "category_id","",$category["active"], "", $category["name"], "", "", "", "")){
            success("category_id: " . $category_id . " is successfully updated", 200);
        }
        else {
            //error while saving.
            error("an error occured while saving the data.", 500);
        }
        return $response; 
});

/**
 * @OA\Get(
 *     path="/All/Category",
 *     summary="All will show up in response body",
 *     tags={"Category"},
 *     @OA\Parameter(
 *         name="category_id",
 *         in="path",
 *         required=true,
 *         description="Fetches all the data from category table",
 *         @OA\Schema(
 *             type="NULL",
 *             example="NULL"
 *         )
 *     ),
 *     @OA\Response(response="200", description="Successfully authenticated"),
 *     @OA\Response(response="401", description="Unauthorized"),
 *     @OA\Response(response="500", description="Internal server error"))
*/

$app->get("/All/Category", function (Request $request, Response $response, $args) {
        //Check the client's authentication.
        require "controller/verify.php";

        //get entities.
        $all_data = get_all_data("category");

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