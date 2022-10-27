<?php
//error for old version 
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
//set conten-type for all endpoints
header("Content-Type: application/json");

//class that needed
use Psr\Http\Message\ResponseInterface as Response; 
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use ReallySimpleJWT\Token;

//path to file: _DIR_
require __DIR__ . "/../vendor/autoload.php";
require "model/action.php";
require_once "config/config.php";

$app = AppFactory::create();

/**
 * @OA\Info(title="ük295: Backend für Applikation realisieren", version="1")
 */

/**
 * Returns an error to the client with the given message and status code.
 * This will immediately return the response and end all scripts.
 * @param $message The error message string.
 * @param $code The response code to set for the response.
 */
function error($message, $code){
    $error = array("message" => $message);
    echo json_encode($error);
    http_response_code($code);
    die();
}
/**
 * Returns an success to the client with the given message and status code.
 * This will immediately return the response and end all scripts.
 * @param $message The succcess message string.
 * @param $code The response code to set for the response.
 */
function success($message, $code){
    $success = array("message" => $message);
    echo json_encode($success);
    http_response_code($code);
    die();
}
//path to endpoints
require "controller/category_endpoints.php";
require "controller/product_endpoints.php";

$app->run();

?>