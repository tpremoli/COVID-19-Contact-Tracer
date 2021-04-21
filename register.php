<?php
//this is the registration code

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    http_response_code(405);
    die();
}

$invalidvalues = false;

foreach ($_POST as $value) { //ensuring no nulls
    if (empty($value)) {
        $invalidvalues = true;
        $errormsg = "Inputs missing!";
    }
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

mysqli_select_db($conn, "$sqlname") or die("Error in adding: " . mysqli_error($conn));

$name =  mysqli_real_escape_string($conn, $_POST["name"]); //protects against injections
$lastname = mysqli_real_escape_string($conn, $_POST["surname"]);
$userusername = mysqli_real_escape_string($conn, $_POST["username"]);
$password = mysqli_real_escape_string($conn, $_POST["password"]);

if (
    !ctype_alnum($name) or (!ctype_alnum($lastname) and $lastname != "") // have to add the != "" check as blank ctype alnum returns false 
    or !ctype_alnum($userusername) or !ctype_alnum($password)            //checks fields are alphanumeric
) {
    $errormsg = "Error in registration: Fields must be Alphanumeric!";
    $conn->close();
    http_response_code(400);
    echo "<script>", "if(!alert('", $errormsg, "')) window.location.href='./register.html';", "</script>"; //throws js message
    exit();
} elseif (strlen($password) < 8) {
    $errormsg = "Error in registration: Password must be at least 8 characters!";
    $conn->close();
    http_response_code(400);
    echo "<script>", "if(!alert('", $errormsg, "')) window.location.href='./register.html';", "</script>"; //throws js message
    exit();
}

$options = ['cost' => 12,];

$saltedpw = password_hash($password, PASSWORD_BCRYPT, $options);

/*
 CREATE TABLE Users ( userID int NOT NULL AUTO_INCREMENT,
        firstname char(255) NOT NULL, 
        lastname char(255), 
        username char(255) NOT NULL UNIQUE, 
        pw char(255) NOT NULL, 
        PRIMARY KEY (userID)); 

 
+------------+-----------+------+-----+---------+----------------+
| Field      | Type      | Null | Key | Default | Extra          |
+------------+-----------+------+-----+---------+----------------+
| userID     | int(11)   | NO   | PRI | NULL    | auto_increment |  //user ID (primary key)
| firstname  | char(255) | NO   |     | NULL    |                |  //first name
| lastname   | char(255) | YES  |     | NULL    |                |  //last name
| username   | char(255) | NO   | UNI | NULL    |                |  //username (MUST BE UNIQUE)
| pw         | char(255) | NO   |     | NULL    |                |  //pw (encrypted)
+------------+-----------+------+-----+---------+----------------+
*/

if (!$invalidvalues) {
    $query = mysqli_query($conn, "INSERT INTO Users
            VALUES (
                0, 
                '$name', 
                '$lastname', 
                '$userusername',
                '$saltedpw'
                )");
}


if ($query == false) { // query will only be false if username has already been taken due ot data processing
    $errormsg = "Error in registration: (Username has already been taken!)";

    $conn->close();

    http_response_code(400);
    echo "<script>", "if(!alert('", $errormsg, "')) window.location.href='./register.html';", "</script>"; //throws js message

    exit();
} else if ($invalidvalues) {
    $conn->close();

    http_response_code(400);
    echo "<script>", "if(!alert('", $errormsg, "')) window.location.href='./register.html';", "</script>"; //throws js message

    exit();
} else {
    $conn->close();

    // successful login alert
    http_response_code(200);
    echo "<script>", "if(!alert('Successfully Registered! Logging in...'));", "</script>"; //figure out how to log in from here


    /*  
        this chunk of code generates HTML and JS; 
        A form is made, using POST towards login.php, values for username and
        password are already set from registered user. The form submission is
        done by the js script under it.
    */
    echo "<form action='login.php' method='POST' id='login'>
                <input type='hidden' name='password' value='", $password, "' />
                <input type='hidden' name='username' value='", $userusername, "' />
            </form>
            <script type='text/javascript'>
                document.getElementById('login').submit();
            </script>";
}
