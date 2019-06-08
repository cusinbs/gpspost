<?php
/* Attempt MySQL server connection. Assuming you are running MySQL
server with default setting (user 'root' with no password) */
$servername = "sql182.main-hosting.eu";
$database = "u426042465_test";
$username = "u426042465_xin";
$password = "123456";
$link = mysqli_connect($servername, $username, $password, $database);
 
// Check connection
if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
 
// Attempt select query execution
$sql = "SELECT * FROM GPS";
if($result = mysqli_query($link, $sql)){
    if(mysqli_num_rows($result) > 0) {
        echo '<table class="table">';
            echo "<tr>";
                echo "<th>id</th>";
                echo "<th>Device Name</th>";
                echo "<th>Device IP</th>";
                echo "<th>Current Location</th>";
                echo "<th>Duration</th>";
            echo "</tr>";
        while($row = mysqli_fetch_array($result)){
            echo "<tr>";
                echo "<td>" . $row['ID'] . "</td>";
                echo "<td>" . $row['deviceName'] . "</td>";
                echo "<td>" . $row['deviceIP'] . "</td>";
                echo "<td>" . $row['currentLocation'] . "</td>";
                echo "<td>" . $row['currentDuration'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        // Free result set
        mysqli_free_result($result);
    } else{
        echo "No records matching your query were found.";
    }
} else{
    echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);
}
 
// Close connection
mysqli_close($link);
?>