<?php

require_once("rabbitmq_required.php");
require_once('DatabaseQuery.php');
require_once('APIQuery.php');

function requestProcessor($request) {
	echo "Request received".PHP_EOL;
	$primary = require('primary.php');

	$date = date('Y-m-d');
	$time = date('h:m:sa');

	if(!isset($request['type'])) {
		return "Error: unsupported message type";
	}

	$apiquery = new APIQuery(); // Call the API class to query from the API

	try {

		$dbquery = new DatabaseQuery(Connection::connect($primary['database'])); 


		// Call the Database class to query from the DATABASE

		$log = "{$date}, {$time}: Successfully connected to the database.";
		file_put_contents("log.txt", $log.PHP_EOL, FILE_APPEND | LOCK_EX);

	} catch (PDOException $e){

		$log = "{$date}, {$time}: Failed to connect to the database.";
		file_put_contents("log.txt", $log.PHP_EOL, FILE_APPEND | LOCK_EX);

	}

	switch ($request['type']) {

		case "activity_add":
			return $dbquery->activity_add($request['firstname'], $request['activity'], $request['object']);

		case "activity_show":
			return $dbquery->activity_show();

		case "beer_favorite":
			return $dbquery->beer_favorite($request['id'], $request['beer_name'], $request['firstname']);

		case "beer_rate":
			return $dbquery->beer_rate($request['user_id'], $request['firstname'], $request['beer_name'], $request['rate']);

		case "beer_rate_average":
			return $dbquery->beer_rate_average($request['beer_name']);

		case "beer_remove":
			return $dbquery->beer_remove($request['id'], $request['beer_name']);

		case "beer_show":
			return $dbquery->beer_show($request['user_id']);

		case "beer_search_name":
			return $apiquery->beer_search_name($request['beer_name']);

		case "beer_search_all":
			return $apiquery->beer_search_all($request['beer_all']);

		case "friend_add":
			return $dbquery->friend_add($request['id'], $request['firstname'], $request['user_id'], $request['friends_name']);

		case "friends_show";
			return $dbquery->friends_show($request['friends_show']);

		case "location_search":
			return $apiquery->location_search($request['location_search']);

		case "login":
			return $dbquery->login($request['username'], $request['password']);

		case "logout":
			return $dbquery->logout($request['username']);

		case "register":
			return $dbquery->register($request['username'], $request['password'], $request['firstname'], $request['lastname']);

		case "user_search":
			return $dbquery->user_search($request['user_search']);

		case "venue_search_id":
			return $apiquery->venue_search_id($request['venue_id']);

		case "venue_search_all":
			return $apiquery->venue_search_all($request['venue_all']);


	}

	return array("returnCode" => '0', 'message' => "Server received request and processed");
}

$server = new rabbitMQServer("testRabbitMQ.ini", "Frontend");
$server->process_requests('requestProcessor');

exit();

?>