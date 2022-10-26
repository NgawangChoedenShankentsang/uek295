<?php
require("../vendor/autoload.php");
$openapi = \OpenApi\Generator::scan([__DIR__]);
header('Content-Type: application/x-yaml');
echo $openapi->toYaml();

?>