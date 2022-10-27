<?php
	//Set the content type for all endpoints to application/json.
	header("Content-Type: application/json");

	use Psr\Http\Message\ResponseInterface as Response;
	use Psr\Http\Message\ServerRequestInterface as Request;
	use Slim\Factory\AppFactory;
	use ReallySimpleJWT\Token;

	require "../vendor/autoload.php";
	require "model/function/category_entries.php";
    require "model/function/product_entries.php";
	require_once "config/api_config.php";
    require_once "config/db_config.php";

	$app = AppFactory::create();

	function error($message, $code) {
		$error = array("message" => $message);
		echo json_encode($error);
		http_response_code($code);
		die();
	}
	require "controller/routes/authenticate.php";
	require "controller/routes/category_entries.php";
	require "controller/routes/product_entries.php";

	$app->run();
?>