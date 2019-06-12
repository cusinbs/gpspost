<?php
date_default_timezone_set("UTC");
$servername = "sql182.main-hosting.eu";
$database = "u426042465_gpsgo";
$username = "u426042465_cusin";
$password = "anhbacson2";
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
	$uid = $inc['uid'];
	$coord = $inc['coord'];
} else {
	$path_info = !empty($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : (!empty($_SERVER['ORIG_PATH_INFO']) ? $_SERVER['ORIG_PATH_INFO'] : '');
	$cmd = substr(strtolower(trim($path_info)), 1);
};



$now = time();
// $diff = $now - $inc['utc'];
// $day = intval(($diff) / 60 / 60 / 24);
// $hour = intval(($diff) / 60 / 60);
// // $min = intval(($diff - $hour * 3600) / 60);
// // $second = intval($diff - $hour * 3600 - $min * 60);
// if ($day > 0) {
// 	$duration = $day . " day(s)";
// } else {
// 	$duration = $hour . " hour(s)";
// }
//$duration = sprintf("%02d", $day) . ":" . sprintf("%02d", $hour) . ":" . sprintf("%02d", $second); //get the time in days and hours
//echo $duration;

$coord = $inc['coord'];

$uid = $inc['uid'];

$temp = explode(", ", $coord);
$latitude = $temp[0];
$longtitude = $temp[1];
$status = '';
$result = $conn->query("SELECT * FROM Data WHERE unitID = '$uid'");

// print_r($result);
// die();


if (mysqli_num_rows($result) == 0) { //not exist, create new record
	echo "Not exist, attempt to insert new record";
	echo " " . $now;
	$sql = "INSERT INTO Data (unitID, unitName, longtitude, latitude, deviceStatus, startTime, duration, deviceRange, alert_trigger, alert_method) VALUES ('$uid', 'Device 1', '$longtitude', '$latitude', 'Present', '$now', null, null, null, null)";
	if (mysqli_query($conn, $sql)) {
		echo "Records inserted successfully.";
	} else {
		echo "ERROR: Could not able to execute $sql. " . mysqli_error($conn);
	}
} else { //existed, update record
	echo "Record existed";
	$duration = 0;
	$result = $conn->query("SELECT startTime, deviceStatus FROM Data WHERE unitID = '$uid'");
	if ($result->num_rows > 0) {
		$row = $result->fetch_assoc();
		$startTime = $row['startTime'];
		$status = $row['deviceStatus'];
		//echo $startTime . ' ' . $status;
	} else {
		echo 'Field does not exist';
	}
	$diff = $now - $startTime;
	//echo $startTime . ' ' . $now;
	$day = intval(($diff) / 60 / 60 / 24);
	$hour = intval(($diff) / 60 / 60);
	$min = intval(($diff - $hour * 3600) / 60);
	$second = intval($diff - $hour * 3600 - $min * 60);
	$duration = sprintf("%02d", $day) . " day(s):" . sprintf("%02d", $hour) . " hour(s):" . sprintf("%02d", $min) . " minute(s):" . sprintf("%02d", $second) . ' second(s)'; //get the time in days and hours

	$conn->query("UPDATE Data SET longtitude='$longtitude', latitude='$latitude', duration='$duration' WHERE unitID = '$uid'");
}

$conn->close();

//Email sending part
ini_set('display_errors', 1);
error_reporting(E_ALL);
$from = "bacsonteam@bacson.tech";
$to = "cusinbs@gmail.com";
$subject = "Power pack device status report";
//echo ' ' . $latitude . ' ' . $longtitude;
$address = getaddress($latitude, $longtitude);

$message = "Device: " . $uid . "\n" .
	"Status: " . $status . "\n" .
	"Location: " . $address . "\n" .
	"Coordinate: " . $latitude . ", " . $longtitude . "\n" . 
	"Time: " . $duration . "\n" .
	"From: Bac Son Tech LLC";
$headers = "From:" . $from;
mail($to, $subject, $message, $headers);
echo "The email message was sent.";

function getaddress($lat, $lng)
{
	$url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng=' . trim($lat) . ',' . trim($lng) . '&key=AIzaSyCdyer5aAaj0UYDo2vruteqH6JJ16I0juE';
	$json = @file_get_contents($url);
	$data = json_decode($json);
	$status = $data->status;
	if ($status == "OK") {
		return $data->results[0]->formatted_address;
	} else {
		return 'Not found';
	}
}
