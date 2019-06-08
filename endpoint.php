<?php
date_default_timezone_set("UTC");
$servername = "sql182.main-hosting.eu";
$database = "u426042465_test";
$username = "u426042465_xin";
$password = "123456";
$conn = new mysqli($servername, $username, $password, $database);
$inc = array();
$inc = array_merge($inc, $_POST);
$inc = array_merge($inc, $_GET);

// Check connection

if (!$conn) {
	die("Connection failed: " . mysqli_connect_error());
}

// Pull Our API Command
if (php_sapi_name() == 'cli') {
	$cmd = $inc['cmd'];
	$userToken = $inc['token'];
	$utc = $inc['utc'];
	$coord = $inc['coord'];
} else {
	$path_info = !empty($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : (!empty($_SERVER['ORIG_PATH_INFO']) ? $_SERVER['ORIG_PATH_INFO'] : '');
	$cmd = substr(strtolower(trim($path_info)), 1);
};



$now = intval(microtime(true));
$diff = $now - $inc['utc'];
$day = intval(($diff) / 60 / 60 / 24);
$hour = intval(($diff) / 60 / 60);
// $min = intval(($diff - $hour * 3600) / 60);
// $second = intval($diff - $hour * 3600 - $min * 60);
if ($day > 0) {
	$duration = $day . " day(s)";
} else {
	$duration = $hour . " hour(s)";
}
//$duration = sprintf("%02d", $day) . ":" . sprintf("%02d", $hour) . ":" . sprintf("%02d", $second); //get the time in days and hours
//echo $duration;

$coord = $inc['coord'];

$remaddr = $_SERVER['REMOTE_ADDR'];

$deviceName = gethostbyaddr($remaddr);

$userToken = $inc['token'];

// print_r($userToken);
// die();

$result = $conn->query("SELECT * FROM GPS WHERE token = '$userToken'");

// print_r($result);
// die();


if (mysqli_num_rows($result) == 0) { //not exist, create new record
	$sql = "INSERT INTO GPS (token, deviceName, deviceIP, currentLocation, currentDuration) VALUES ('$userToken', '$deviceName', '$remaddr', '$coord', '$duration')";

	if (mysqli_query($conn, $sql)) {
		echo "Records inserted successfully.";
	} else {
		echo "ERROR: Could not able to execute $sql. " . mysqli_error($conn);
	}

	echo "False";
} else { //existed, update record
	$query = $conn->query("SELECT currentLocation FROM GPS WHERE token = '$userToken'");
	if (mysqli_num_rows($query) > 0) {
		$currentLoc = mysqli_fetch_assoc($query)['currentLocation'];
		// print_r($currentLoc);
		// die();
	}else{
		$row = '';
	}

	$conn->query("UPDATE GPS SET currentDuration='$duration', currentLocation='$coord', lastLocation='$currentLoc' WHERE token = '$userToken'");
	echo "True";
}

$conn->close();
