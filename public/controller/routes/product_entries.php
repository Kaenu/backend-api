<?php
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    use Slim\Factory\AppFactory;
    use ReallySimpleJWT\Token;

	$app->get("/get/prod", function (Request $request, Response $response, $args) {
        //Check the client's authentication.
        require "controller/require_authentication.php";

        $prod_entries = get_product_entries();

        if (is_string($prod_entries)) {
            error($prod_entries, 500);
        }
        else {
            echo json_encode($prod_entries);
        }
        return $response;
    });

    $app->get("/prod/{prod_id}", function (Request $request, Response $response, $args) {
            //Check the client's authentication.
            require "controller/require_authentication.php";

            $product_id = intval($args["prod_id"]);

            //Get the entity.
            $prod_id = get_product_id($product_id);

            if (!$prod_id) {
                error("Product ID: " . $product_id . " was not found.", 400);
            }
            else if (is_string($prod_id)) {
                error($prod_id, 500);
            }
            else {
                //Success.
                echo json_encode($prod_id);
            }

            return $response;
    });

    $app->post("/post/prod", function (Request $request, Response $response, $args) {
		//Check the client's authentication.
		require "controller/require_authentication.php";

		$request_body_string = file_get_contents("php://input");
		$request_data = json_decode($request_body_string, true);

		//Check if all values are provided.
        if (!isset($request_data["sku"]) || !is_numeric($request_data["sku"])) {
			error("Enter the part number. (SKU)", 400);
		}
		if (!isset($request_data["active"]) || !is_numeric($request_data["active"])) {
			error("Should the category be active or deactivated? Enter \"1\" for active and \"0\" for deactivated.", 400);
		}
        if (!isset($request_data["category_id"]) || !is_numeric($request_data["category_id"])) {
			error("Enter the category ID.", 400);
		}
		if (!isset($request_data["name"])) {
			error("Enter the \"name\" of the product.", 400);
		}
        if (!isset($request_data["image"])) {
			error("Enter image URL for the product.", 400);
		}
        if (!isset($request_data["description"])) {
			error("Enter the description for the product.", 400);
		}
        if (!isset($request_data["price"]) || !is_numeric($request_data["price"])) {
			error("Enter the price for the product.", 400);
		}
        if (!isset($request_data["stock"]) || !is_numeric($request_data["stock"])) {
			error("How many products are in stock?", 400);
		}

		//Sanitize the values where necessary.
        $sku = intval($request_data["sku"]);
        $active = intval($request_data["active"]);
        $category_id = intval($request_data["category_id"]);
		$name = strip_tags(addslashes($request_data["name"]));
        $image = strip_tags(addslashes($request_data["image"]));
        $description = strip_tags(addslashes($request_data["description"]));
        $price = intval($request_data["price"]);
        $stock = intval($request_data["stock"]);

		//Check
        if (empty($sku) || empty($category_id) || empty($name) || empty($image) || empty($description) || empty($stock)) {
			error("The fields must not be empty.", 400);
		}
        if (strlen($sku) > 100) {
			error("The item number " . $sku . " is invalid.", 400);
		}
        if ($active != 0 && $active != 1) {
			error("The \"active\" field must be either \"0\" (deactivated) or \"1\" (active).", 400);
		}
        if (is_float($category_id) || is_float($stock)) {
			error("The number (Category-ID, Stock) must not be in decimal", 400);
		}
        if (strlen($name) > 500) {
			error("The name is too long. Please enter less than or equal to 500 characters.", 400);
		}
		if (strlen($image) > 1000) {
			error("The URL can be a maximum of 1000 characters long.", 400);
		}
        if (strlen($price < 0)) {
			error("The price that will not be below 0.", 400);
		}
		if (create_product_entry($sku, $active, $category_id, $name, $image, $description, $price, $stock) === true) {
			http_response_code(201);
            if(!$active == 0) {
                $status = "\"activated\"";
            } else {
                $status = "\"deactivated\"";
            }
			echo "The product \"" . $name . "\" was successfully created and is current " . $status. ".";
		}
		else {
			error("An error occurred while saving the changes.", 500);
		}

		return $response;
	});

	$app->put("/put/prod/{prod_id}", function (Request $request, Response $response, $args) {
		//Check the client's authentication.
		require "controller/require_authentication.php";
		
		$product_id = intval($args["prod_id"]);
		$prod_id = get_product_id($product_id);

		if (!$prod_id) {
			error("No entries found for the ID " . $product_id . ".", 404);
		}
		else if (is_string($prod_id)) {
			error($prod_id, 500);
		}

		//Read request body input string.
		$request_body_string = file_get_contents("php://input");

		//Parse the JSON string.
		$request_data = json_decode($request_body_string, true);
		
		/* if (!isset($request_data["sku"]) || !is_numeric($request_data["sku"])) {
			error("Enter the part number. (SKU)", 400);
			$sku = intval($request_data["sku"]);

			$prod_id["sku"] = $sku;
		}
		if (!isset($request_data["active"]) || !is_numeric($request_data["active"])) {
			$active = intval($request_data["active"]);
			error("Should the category be active or deactivated? Enter \"1\" for active and \"0\" for deactivated.", 400);

			$prod_id["active"] = $active;
		}
        if (!isset($request_data["category_id"]) || !is_numeric($request_data["category_id"])) {
			$category_id = intval($request_data["category_id"]);
			error("Enter the category ID.", 400);

			$prod_id["category_id"] = $category_id;
		}
		if (!isset($request_data["name"])) {
			$name = strip_tags(addslashes($request_data["name"]));
			error("Enter the \"name\" of the product.", 400);

			$prod_id["name"] = $name;
		}
        if (!isset($request_data["image"])) {
			$image = strip_tags(addslashes($request_data["image"]));
			
			if (strlen($image) > 1000) {
				error("The URL can be a maximum of 1000 characters long.", 400);
			}
			$prod_id["image"] = $image;
		}
        if (!isset($request_data["description"])) {
			$description = strip_tags(addslashes($request_data["description"]));
			
			$prod_id["description"] = $description;

        if (!isset($request_data["price"]) || !is_numeric($request_data["price"])) {
			$price = intval($request_data["price"]);

			$prod_id["price"] = $price;
		}
        if (!isset($request_data["stock"]) || !is_numeric($request_data["stock"])) {
			$stock = intval($request_data["stock"]);

			if (strlen($stock) < 0) {
				error("The name is too long. Please enter less than or equal to 500 characters.", 400);
			}
			$prod_id["stock"] = $stock;
		} */

		if (isset($request_data["name"])) {
			$name = strip_tags(addslashes($request_data["name"]));

			if (empty($name)) {
				error("The \"name\" field must not be empty.", 400);
			}

			if (strlen($name) > 500) {
				error("The name is too long. Please enter less than or equal to 500 characters.", 400);
			}

			$prod_id["name"] = $name;
		}

		//Save the information.
		if (update_product_entry($product_id, $prod_id["active"], $prod_id["name"])) {
			echo "true";
		}
		else {
			error("An error occurred while saving the changes.", 500);
		}

		return $response;
	});


    $app->delete("/del/prod/{prod_id}", function (Request $request, Response $response, $args) {
		//Check the client's authentication.
		require "controller/require_authentication.php";

		$product_id = intval($args["prod_id"]);

		//Delete the entity.
		$delete_prod_entry = delete_product_entry($product_id);

		if (!$delete_prod_entry) {
			error("Product ID: " . $product_id . " was not found.", 404);
		}
		else if (is_string($delete_prod_entry)) {
			error($delete_prod_entry, 500);
		}
		else {
			echo json_encode($delete_prod_entry);
		}
		return $response;
	});