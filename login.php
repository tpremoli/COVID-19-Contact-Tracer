<?php
session_start();

//this code performs the login sequence for the database


$conf = include('config.php');

if ($_SERVER["REQUEST_METHOD"] != "POST"){
    http_response_code(405);
    die();
}

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

$sqlname = $conf["sqlname"];

$conn->select_db("$sqlname") or die(mysqli_error($conn) . http_response_code(500));

$userusername = mysqli_real_escape_string($conn, $_POST["username"]); //protects against injections
$password = mysqli_real_escape_string($conn, $_POST["password"]);

$usernamequery = mysqli_query($conn, "SELECT *
                    FROM Users
                    WHERE username = '" . $userusername . "'")
    or die(mysqli_error($conn) .  http_response_code(500));


$conn->close();

if ($usernamequery->num_rows > 0) {
    $result = $usernamequery->fetch_assoc() or die(mysqli_error($conn) . http_response_code(500));

    if (password_verify($password, $result["pw"])) {

        $_SESSION["userID"] = $result["userID"];
        $_SESSION["firstname"] = $result["firstname"];
        $_SESSION["lastname"] = $result["lastname"];
        $_SESSION["username"] = $result["username"];

        if (count($_COOKIE) == 1  or count($_COOKIE) == 0) {
            setcookie("timewindow", 2);
            setcookie("distance", 100);
        }

        http_response_code(200);
        header("Location: home.php");
        exit();
    } else {
        http_response_code(400);
        session_destroy();
        header("Location: logout.php");
        exit();
    }
} else {
    http_response_code(500);
    session_destroy();
    header("Location: logout.php");
    exit();
}
