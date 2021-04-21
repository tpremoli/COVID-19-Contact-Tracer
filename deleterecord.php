<?php
//this gets the ajax call to remove a visit and deletes the record

if ($_SERVER["REQUEST_METHOD"] != "POST"){
    http_response_code(405);
    die();
}

//loading config file
$conf = include('config.php');

// This works
$servername = $conf["servername"];
$username = $conf["username"];
$password = $conf["password"];
$dbname = $conf["dbname"];
$port = $conf["port"];

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    die("Connection failed: " . $conn->connect_error);
}


//choosing right database
$sqlname = $conf["sqlname"];

mysqli_select_db($conn, "$sqlname") or die("Error in removing: " . mysqli_error($conn));

$deletequery = mysqli_query($conn, "DELETE
                        FROM Visits
                        WHERE visitID = '" . $_POST["visitID"] . "'");

if (!$deletequery) {
    http_response_code(400);
} else {
    http_response_code(200);
}
