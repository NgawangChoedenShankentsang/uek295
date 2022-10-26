<?php
use ReallySimpleJWT\Token;

if (!isset($_COOKIE["token"]) || !Token::validate($_COOKIE["token"], "sec!ReT423*&")) {
	error("Unauthorised", 401);
}
?>