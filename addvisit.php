<?php
session_start();
//code to add visit

if (!isset($_SESSION["userID"])) {
    header("Location: logout.php");
} else if (!empty($_GET)) { // if a visit is being added, this code adds it
    $invalidvalues = false; //checking if values are valid or not

    foreach ($_GET as $value) { //ensuring no nulls
        if (empty($value)) {
            $invalidvalues = true;
            $errormsg = "Inputs missing!";
        }
    }
    if (!$invalidvalues) { //checking if the x/y values are out of range
        if ($_GET["x"] > 500 or $_GET["x"] < 0) {
            $invalidvalues = true;
            $errormsg = "Coordinates out of range!";
        }
        if ($_GET["y"] > 500 or $_GET["y"] < 0) {
            $invalidvalues = true;
            $errormsg = "Coordinates out of range!";
        }
    }

    if (!$invalidvalues) {
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
            die("Connection failed: " . $conn->connect_error);
        }

        $sqlname = $conf["sqlname"];

        $conn->select_db("$sqlname") or die(mysqli_error($conn));

        $date = urldecode($_GET["date"]);
        $time = urldecode($_GET["time"]);
        $duration = urldecode($_GET["duration"]);
        $x = $_GET["x"];
        $y = $_GET["y"];

        $querystring = 'INSERT INTO Visits VALUES(0,
                                                ' . $_SESSION['userID'] . ',
                                                ' . "'" . $date . "'" . ',
                                                ' . "'" . $time . "'" . ',
                                                ' . $duration . ',
                                                ' . $x . ',
                                                ' . $y . ');';

        $addquery = mysqli_query($conn,  $querystring);


        $query->close;
        $conn->close;

        if ($addquery) {
            http_response_code(200);
            echo "<script>", "if(!alert('", "Added visit!", "')) window.location.href='./addvisit.php';", "</script>"; //throws js message
        } else {
            http_response_code(500);
            echo "<script>", "if(!alert('", "ERROR: Values invalid!", "')) window.location.href='./addvisit.php';", "</script>";
        }
    } else {
        http_response_code(400);
        echo "<script>", "if(!alert('", "ERROR: " . $errormsg,
        "')) window.location.href='./addvisit.php';", "</script>"; //throws js message
    }
}


/* 
    CREATE TABLE Visits(
        visitID int NOT NULL AUTO_INCREMENT,
        userID int NOT NULL,
        date DATE NOT NULL,
        time TIME NOT NULL,
        duration int NOT NULL,
        x int NOT NULL,
        y int NOT NULL,
        PRIMARY KEY(visitID),
        FOREIGN KEY(userID) REFERENCES Users(userID)
    )

+----------+---------+------+-----+---------+----------------+
| Field    | Type    | Null | Key | Default | Extra          |
+----------+---------+------+-----+---------+----------------+
| visitID  | int(11) | NO   | PRI | NULL    | auto_increment |
| userID   | int(11) | NO   | MUL | NULL    |                |
| date     | date    | NO   |     | NULL    |                |
| time     | time    | NO   |     | NULL    |                |
| duration | int(11) | NO   |     | NULL    |                |
| x        | int(11) | NO   |     | NULL    |                |
| y        | int(11) | NO   |     | NULL    |                |
+----------+---------+------+-----+---------+----------------+

*/
?>
<script type="text/javascript">
    function moveMarker(event) { //moves marker according to map click
        marker = document.getElementById("marker");
        marker.style.visibility = "visible";

        var bounds = this.getBoundingClientRect();


        var leftmargin = bounds.left;
        var topmargin = bounds.top;

        var xCoord = event.pageX - leftmargin; //values of coords on map
        var yCoord = event.pageY - topmargin;


        document.getElementById("x").value = xCoord; //filling in hidden form values
        document.getElementById("y").value = yCoord;


        markerX = event.pageX - (marker.clientWidth / 2); //display values
        markerY = event.pageY - marker.clientHeight; //this code centers the marker on the point

        marker.style.left = markerX + "px";
        marker.style.top = markerY + "px";

    }

    window.onload = function() {
        document.getElementById('map').ondragstart = function() {
            return false;
        };
        document.getElementById('map').addEventListener('click', moveMarker, false);
    };
</script>

<DOCTYPE html PUBLIC>
    <html>

    <head>
        <title>
            Add Visit Page
        </title>
        <link rel="stylesheet" href="sites.css">
        <style>
            table {
                margin-top: 2%;
                border-color: black;
                width: 80%;
                table-layout: fixed;
                border-collapse: collapse;
            }

            td {
                text-align: center;
                height: 100px;
            }

            #map {
                width: 500px;
                height: 500px;
                border: 3px solid black;
            }

            .status_header {
                font-family: Arial, Helvetica, sans-serif;
                font-size: 24;
                font-weight: bold;
            }

            tr.border_bottom th {
                border-bottom: 3px solid black;
            }

            .caption {
                font-family: 'Times New Roman', Times, serif;
                font-size: 20;
            }

            .button {
                background-color: white;
                border-radius: 16px;
                text-align: center;
                font-size: 20px;
                font-family: 'Times New Roman', Times, serif;
                margin: 0 auto;
                display: inline;
                height: 70px;
                width: 400px;
            }

            .button:hover {
                background-color: black;
                color: white;
            }

            .input {
                background-color: rgba(255, 255, 255, 0);
                color: black;
                text-align: center;
                font-size: 30px;
                font-family: 'Times New Roman', Times, serif;
                height: 70px;
            }

            .input::placeholder {
                color: black;
            }
        </style>
    </head>

    <body>
        <h1 class=header>COVID - 19 Contact Tracing</h1>

        <ol class=sidebar>
            <a href="./home.php">
                <li>
                    Home
                </li>
            </a>
            <a href="./overview.php">
                <li>
                    Overview
                </li>
            </a>
            <a href="./addvisit.php" class=selected>
                <li>
                    Add Visit
                </li>
            </a>
            <a href="./report.php">
                <li>
                    Report
                </li>
            </a>
            <a href="./settings.php">
                <li>
                    Settings
                </li>
            </a>
            <div class=sidebar_footer>
                <a href="./logout.php">
                    <li>
                        Logout
                    </li>
                </a>
            </div>
        </ol>

        <div class=main_area>
            <table class=center>


                <tr class="border_bottom">
                    <th colspan="2" class=status_header>Add a new visit</th>
                </tr>
                <form action="addvisit.php" method="PUT" id="addvisit_form">

                    <tr>
                        <td><input type="date" name="date" class=input placeholder="Date" required></td>
                        <td rowspan="6">
                            <img src="exeter.jpg" alt="" id="map">
                            <img id="marker" src="marker_black.png" style="height: 40px; width: 40px; position: absolute; z-index: 1000; visibility: hidden">
                        </td>
                    </tr>
                    <tr>
                        <td><input type="time" name="time" class=input placeholder="Time" required></td>
                    </tr>
                    <tr>
                        <td><input type="number" name="duration" class=input placeholder="Duration" required></td>
                    </tr>

                    <tr>
                        <td></td>
                    </tr>
                    <tr>
                        <td><input type=submit value=Add name="Add" class=button></td>
                    </tr>

                    <tr>
                        <td><input type=reset value=Cancel name="Cancel" class=button></td>
                    </tr>
                    <input id="x" type=hidden name="x" required>
                    <input id="y" type=hidden name="y" required>
                </form>


            </table>
        </div>


    </body>

    </html>