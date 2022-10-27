<?php
	use Psr\Http\Message\ResponseInterface as Response;
	use Psr\Http\Message\ServerRequestInterface as Request;
	use Slim\Factory\AppFactory;
	use ReallySimpleJWT\Token;

	$app->post("/authenticate", function (Request $request, Response $response, $args) {
		global $api_username;
		global $api_password;

		$request_body_string = file_get_contents("php://input");
		$request_data = json_decode($request_body_string, true);

		$entered_username = $request_data["username"];
		$entered_password = $request_data["password"];

		if ($entered_username != $api_username || $entered_password != $api_password) {
			error("The username and/or password are incorrect.", 401);
		}

		//Generate the access token and store it in the cookies.
		$token = Token::create($entered_username, $entered_password, time() + 3600, "localhost");

		setcookie("token", $token);
		echo "You have successfully logged in!";

		return $response;
	});
?>