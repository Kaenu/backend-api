<?php
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    use Slim\Factory\AppFactory;
    use ReallySimpleJWT\Token;

    $app->get("/get/cat", function (Request $request, Response $response, $args) {
        //Check the client's authentication.
        require "controller/require_authentication.php";

        $cat_entries = get_category_entries();

        if (is_string($cat_entries)) {
            error($cat_entries, 500);
        }
        else {
            echo json_encode($cat_entries);
        }
        return $response;
    });

    $app->get("/get/cat/{cat_id}", function (Request $request, Response $response, $args) {
		//Check the client's authentication.
		require "controller/require_authentication.php";

		$category_id = intval($args["cat_id"]);

		//Get the entity.
		$cat_id = get_category_id($category_id);

		if (!$cat_id) {
			error("Category ID: " . $category_id . " was not found.", 404);
		}
		else if (is_string($cat_id)) {
			error($cat_id, 500);
		}
		else {
			//Success.
			echo json_encode($cat_id);
		}

		return $response;
	});

    $app->post("/post/cat", function (Request $request, Response $response, $args) {
		//Check the client's authentication.
		require "controller/require_authentication.php";

		$request_body_string = file_get_contents("php://input");
		$request_data = json_decode($request_body_string, true);

		if (!isset($request_data["active"]) || !is_numeric($request_data["active"])) {
			error("Should the category be active or deactivated? Enter \"1\" for active and \"0\" for deactivated.", 400);
		}
		if (!isset($request_data["name"])) {
			error("Enter the \"name\" of the category.", 400);
		}

		//Sanitize the values where necessary.
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
            if(!$active == 0) {
                $status = "\"activated\"";
            } else {
                $status = "\"deactivated\"";
            }
			echo "The category " . $name . " with the status: " . $status . " was successfully created.";
		}
		else {
			error("An error occurred while saving the changes.", 500);
		}

		return $response;
	});


    $app->put("/put/cat/{cat_id}", function (Request $request, Response $response, $args) {
		//Check the client's authentication.
		require "controller/require_authentication.php";

		$category_id = intval($args["cat_id"]);

		//Get the entity.
		$cat_id = get_category_id($category_id);

		if (!$cat_id) {
			//No entity found.
			error("No entries found for the ID " . $category_id . ".", 404);
		}
		else if (is_string($cat_id)) {
			error($cat_id, 500);
		}

		//Read request body input string.
		$request_body_string = file_get_contents("php://input");

		//Parse the JSON string.
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
			//Sanitize the name.
			$name = strip_tags(addslashes($request_data["name"]));

			if (empty($name)) {
				error("The \"name\" field must not be empty.", 400);
			}
			if (strlen($name) > 500) {
				error("The name is too long. Please enter less than or equal to 500 characters.", 400);
			}

			$cat_id["name"] = $name;
		}
		if (update_category_entry($category_id, $cat_id["active"], $cat_id["name"])) {
			echo "true";
		}
		else {
			error("An error occurred while saving the changes.", 500);
		}

		return $response;
	});


    $app->delete("/del/cat/{cat_id}", function (Request $request, Response $response, $args) {
		//Check the client's authentication.
		require "controller/require_authentication.php";

		$category_id = intval($args["cat_id"]);

		//Delete the entity.
		$delete_cat_entry = delete_category_entry($category_id);

		if (!$delete_cat_entry) {
			//No entity found.
			error("Category ID: " . $category_id . " was not found.", 404);
		}
		else if (is_string($delete_cat_entry)) {
			//Error while deleting.
			error($delete_cat_entry, 500);
		}
		else {
			//Success.
			echo json_encode($delete_cat_entry);
		}

		return $response;
	});

?>