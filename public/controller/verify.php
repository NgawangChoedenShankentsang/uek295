<?php
use ReallySimpleJWT\Token;
require_once "config/config.php";

global $api_password;

//Checking it if token is in cookie or not
if (!isset($_COOKIE["token"]) || !Token::validate($_COOKIE["token"], $api_password)) {
	error("Unauthorised", 401);
}
?>