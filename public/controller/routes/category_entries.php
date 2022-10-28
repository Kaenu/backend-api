<?php
    //Classes that are necessary for this web application
	use Psr\Http\Message\ResponseInterface as Response;
	use Psr\Http\Message\ServerRequestInterface as Request;
	use Slim\Factory\AppFactory;
	use ReallySimpleJWT\Token;

	/**
     * @OA\Get(
     *     path="/Get/cat",
     *     summary="Show all entries.",
     *     tags={"Category"},
     *     @OA\Parameter(
     *         name="parameter",
     *         in="path",
     *         required=false,
     *         description="",
     *         @OA\Schema(
     *             type="",
     *             example=""
     *         )
     *     ),
     *     @OA\Response(response="200", description="Successfully authenticated"),
     *     @OA\Response(response="401", description="Unauthorized"),
     *     @OA\Response(response="500", description="Internal server error")
	 * )
 	*/
    $app->get("/Get/cat", function (Request $request, Response $response, $args) {
        //Check the client's authentication
        require "controller/require_authentication.php";

		//Table entries are stored in the variable
        $cat_entries = get_category_entries();

		//Validation
        if (is_string($cat_entries)) {
			//Error
            error($cat_entries, 500);
        }
        else {
			//Success
            echo json_encode($cat_entries);
        }
        return $response;
    });

	/**
     * @OA\Get(
     *     path="/Get/cat/{cat_id}",
     *     summary="Fetches a entry with the given ID.",
     *     tags={"Category"},
     *     @OA\Parameter(
     *         name="cat_id",
     *         in="path",
     *         required=true,
     *         description="The ID of the entry to fetch.",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(response="200", description="Successfully authenticated"),
     *     @OA\Response(response="401", description="Unauthorized"),
     *     @OA\Response(response="500", description="Internal server error"),
     *     @OA\Response(response="404", description="Category ID: \" . $category_id . \" was not found.")
	 * )
	 */
    $app->get("/Get/cat/{cat_id}", function (Request $request, Response $response, $args) {
		//Check the client's authentication
		require "controller/require_authentication.php";

		$category_id = intval($args["cat_id"]);

		//Table entries are stored in the variable
		$cat_id = get_category_id($category_id);

		//Validation
		if (!$cat_id) {
			//Error
			error("Category ID: " . $category_id . " was not found.", 404);
		}
		else if (is_string($cat_id)) {
			//Error
			error($cat_id, 500);
		}
		else {
			//Success
			echo json_encode($cat_id);
		}
		return $response;
	});

	/**
     * @OA\Post(
     *     path="/Post/cat",
     *     summary="Creates a new entry in the "category" table",
     *     tags={"Category"},
     *     requestBody=@OA\RequestBody(
     *         request="/Post/cat",
     *         required=true,
     *         description="{\"active\":\"0\","name\":\"example\"}\",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="active", type="integer", example="Either 0 or 1"),
     *                 @OA\Property(property="name", type="String", example="Maximum 500 characters")
     *             )
     *         )
     *     ),
     *     @OA\Response(response="201", description="The category \" . $name . \" with the status: \" . $status . \" was successfully created."),
     *		@OA\Response(response="401", description="Unauthorized"),
     *     @OA\Response(response="500", description="Internal server error"),
     *     @OA\Response(response="400", description="One or more field(s) does not meet the requirement")
	 * )
	*/
    $app->post("/Post/cat", function (Request $request, Response $response, $args) {
		//Check the client's authentication
		require "controller/require_authentication.php";

		$request_body_string = file_get_contents("php://input");
		$request_data = json_decode($request_body_string, true);

		if (!isset($request_data["active"]) || !is_numeric($request_data["active"])) {
			error("Should the category be active or deactivated? Enter \"1\" for active and \"0\" for deactivated.", 400);
		}
		if (!isset($request_data["name"])) {
			error("Enter the \"name\" of the category.", 400);
		}

		//Sanitize the values where necessary
        $active = intval($request_data["active"]);
		$name = strip_tags(addslashes($request_data["name"]));

		//Check
		if ($active != 0 && $active != 1) {
			error("The \"active\" field must be either \"0\" (deactivated) or \"1\" (active).", 400);
		}
        if (empty($name)) {
			error("The fields must not be empty.", 400);
		}
		if (strlen($name) > 500) {
			error("The name is too long. Please enter less than or equal to 500 characters.", 400);
		}
		if (create_category_entry($active, $name) === true) {
			http_response_code(201);

			//An If/Else to output the status (serves the usability)
            if(!$active == 0) {
                $status = "\"activated\"";
            } else {
                $status = "\"deactivated\"";
            }
			//Success
			echo "The category " . $name . " with the status: " . $status . " was successfully created.";
		}
		else {
			error("An error occurred while saving the changes.", 500);
		}

		return $response;
	});

	/**
     * @OA\Put(
     *     path="/Put/cat/{cat_id}",
     *     summary="You can use it to update already existing entries",
     *     tags={"Category"},
     *     @OA\Parameter(
     *         name="cat_id",
     *         in="path",
     *         required=true,
     *         description="The category ID of the category from the category table",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     requestBody=@OA\RequestBody(
     *         request="/Put/cat/{cat_id}",
     *         required=true,
     *         description="Existing entries can be changed using category_id."},
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="active", type="integer", example="Either 0 or 1"),
     *                 @OA\Property(property="name", type="String", example="Maximum 500 characters")
     *             )
     *         )
     *     ),
     *		@OA\Response(response="200", description="The entry was updated."),
	 *		@OA\Response(response="401", description="Unauthorized"),
     *		@OA\Response(response="500", description="Internal server error"),
     *     	@OA\Response(response="404", description="No entry found for the ID \" . $category_id . \".")
     * )
	*/
    $app->put("/Put/cat/{cat_id}", function (Request $request, Response $response, $args) {
		//Check the client's authentication
		require "controller/require_authentication.php";

		$category_id = intval($args["cat_id"]);

		//Table entries are stored in the variable
		$cat_id = get_category_id($category_id);

		//Validation
		if (!$cat_id) {
			//No entity found
			error("No entry found for the ID " . $category_id . ".", 404);
		}
		else if (is_string($cat_id)) {
			error($cat_id, 500);
		}

		//Read request body input string
		$request_body_string = file_get_contents("php://input");

		//Parse the JSON string
		$request_data = json_decode($request_body_string, true);

        if (isset($request_data["active"])) {
			if (!is_numeric($request_data["active"])) {
				error("The \"active\" field must be either \"0\" (deactivated) or \"1\" (active).", 400);
			}
			$active = intval($request_data["active"]);

			if ($active != 0 && $active != 1) {
                error("The \"active\" field must be either \"0\" (deactivated) or \"1\" (active).", 400);
            }
			$cat_id["active"] = $active;
		}
		if (isset($request_data["name"])) {
			//Sanitize the name
			$name = strip_tags(addslashes($request_data["name"]));

			//Validation
			if (empty($name)) {
				error("The \"name\" field must not be empty.", 400);
			}
			if (strlen($name) > 500) {
				error("The name is too long. Please enter less than or equal to 500 characters.", 400);
			}
			$cat_id["name"] = $name;
		}
		if (update_category_entry($category_id, $cat_id["active"], $cat_id["name"])) {
			//Success
			echo "The entry was updated.";
		}
		else {
			//Error
			error("An error occurred while saving the changes.", 500);
		}
		return $response;
	});

	/**
     * @OA\Delete(
     *     path="/Del/cat/{cat_id}",
     *     summary="Deletes an entry with a specific category_id from the category table",
     *     tags={"Category"},
     *     @OA\Parameter(
     *         name="cat_id",
     *         in="path",
     *         required=true,
     *         description="The category ID of the category table",
     *         @OA\Schema(
     *             type="integer",
     *             example="1"
     *         )
     *     ),
     *     @OA\Response(response="200", description="Successfully authenticated"),
     *     @OA\Response(response="401", description="Unauthorized"),
     *     @OA\Response(response="500", description="Internal server error"),
     *     @OA\Response(response="404", description="Category ID: \" . $category_id . \" was not found.)
     * )
	*/
    $app->delete("/Del/cat/{cat_id}", function (Request $request, Response $response, $args) {
		//Check the client's authentication
		require "controller/require_authentication.php";

		$category_id = intval($args["cat_id"]);

		//Delete the entity
		$delete_cat_entry = delete_category_entry($category_id);

		//Validation
		if (!$delete_cat_entry) {
			//No entity found
			error("Category ID: " . $category_id . " was not found.", 404);
		}
		else if (is_string($delete_cat_entry)) {
			//Error while deleting
			error($delete_cat_entry, 500);
		}
		else {
			//Success
			echo json_encode($delete_cat_entry);
		}
		return $response;
	});
?>