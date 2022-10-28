<?php
	//Classes that are necessary for this web application
	use Psr\Http\Message\ResponseInterface as Response;
	use Psr\Http\Message\ServerRequestInterface as Request;
	use Slim\Factory\AppFactory;
	use ReallySimpleJWT\Token;

	/**
     * @OA\Post(
     *     path="/Authenticate",
     *     summary="Used to authenticate and obtain an access token that will be stored in the cookies.",
     *     tags={"General"},
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
     *     @OA\Response(response="200", description="Successfully authenticated")),
     *     @OA\Response(response="401", description="Invalid credentials")),
     *     @OA\Response(response="500", description="Internal server error"))
     * )
	 */
	$app->post("/Authenticate", function (Request $request, Response $response, $args) {
		//The API login data
		global $api_username;
		global $api_password;

		//For both requests, the entry is read first and the JSON string is parsed
		$request_body_string = file_get_contents("php://input");
		$request_data = json_decode($request_body_string, true);

		//The entered data for API are put into a variable
		$entered_username = $request_data["username"];
		$entered_password = $request_data["password"];

		//Validation of the entered data with the given data
		if ($entered_username != $api_username || $entered_password != $api_password) {
			error("The username and/or password are incorrect.", 401);
		}

		//Generate the access token and store it in the cookies.
		$token = Token::create($entered_username, $entered_password, time() + 3600, "localhost");

		//The token is stored in the cookies, a message that it has succeeded is issued.
		setcookie("token", $token);
		echo "You have successfully logged in!";
		return $response;
	});
?>