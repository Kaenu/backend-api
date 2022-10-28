<?php
	//Set the content type for all endpoints to application/json
	header("Content-Type: application/json");

	//Classes that are necessary for this web application
	use Psr\Http\Message\ResponseInterface as Response;
	use Psr\Http\Message\ServerRequestInterface as Request;
	use Slim\Factory\AppFactory;
	use ReallySimpleJWT\Token;

	//All setting and function calls
	require "../vendor/autoload.php";
	require_once "config/api_config.php";
    require_once "config/db_config.php";
	require "model/function/category_entries.php";
    require "model/function/product_entries.php";

	$app = AppFactory::create();

	//Through this function the error messages are reported back as JSON_Encode
	function error($message, $code) {
		$error = array("message" => $message);
		echo json_encode($error);
		http_response_code($code);
		die();
	}

	/**
     * @OA\Info(title="API", version="3.3.3")
	 */
	//Calls to all endpoints
	require "controller/routes/authenticate.php";
	require "controller/routes/category_entries.php";
	require "controller/routes/product_entries.php";

	$app->run();
?>