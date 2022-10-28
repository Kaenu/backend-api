<?php
	//Class for JSON WebTokens
	use ReallySimpleJWT\Token;

	//One time loading of API settings
	require_once "config/api_config.php";

	// Password from API
	global $api_password;

	// Validation of the token in the cookies
	if (!isset($_COOKIE["token"]) || !Token::validate($_COOKIE["token"], $api_password)) {
		$error = array("message" => "Unauthorised.");
		echo json_encode($error);
		http_response_code(401);
		die();
	}
?>