<?php
session_start();

if (!isset($_SESSION["userID"])) {
    header("Location: logout.php");
}

if (!empty($_GET)) { //if an infection is being reported, this chunk of code runs
    /*
        CREATE TABLE Infections (infectionID int NOT NULL AUTO_INCREMENT,
            userID int NOT NULL,
            date DATE NOT NULL,
            time TIME NOT NULL,
            PRIMARY KEY(infectionID),
            FOREIGN KEY(userID) REFERENCES Users(userID));

    
    +-------------+---------+------+-----+---------+----------------+
    | Field       | Type    | Null | Key | Default | Extra          |
    +-------------+---------+------+-----+---------+----------------+
    | infectionID | int(11) | NO   | PRI | NULL    | auto_increment |   //ID, primary infection key
    | userID      | int(11) | NO   | MUL | NULL    |                |   //user ID, foreign key
    | date        | date    | NO   |     | NULL    |                |   //date
    | time        | time    | NO   |     | NULL    |                |   //time
    +-------------+---------+------+-----+---------+----------------+
    
    */

    $invalidvalues = false;
    foreach ($_GET as $value) { //ensuring no nulls
        if (empty($value)) {
            $invalidvalues = true;
            $errormsg = "Inputs missing!";
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

        $querystring = 'INSERT INTO Infections VALUES(0,
                                                ' . $_SESSION['userID'] . ',
                                                ' . "'" . $date . "'" . ',
                                                ' . "'" . $time . "'" . ');';

        $addquery = mysqli_query($conn,  $querystring);

        $query->close;

        if ($addquery) {
            $visitsquery = mysqli_query(
                $conn,
                "SELECT *
                    FROM Visits
                    WHERE userID = '" . $_SESSION['userID'] . "'"
            );

            if ($visitsquery) { 

                require "curlconnect.php";
                $url = "http://ml-lab-7b3a1aae-e63e-46ec-90c4-4e430b434198.ukwest.cloudapp.azure.com:60999/report";
                curl_setopt($handle, CURLOPT_URL, $url);
                curl_setopt($handle, CURLOPT_RETURNTRANSFER, false);
                curl_setopt($handle, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                $msg = "Infection Reported!";

                while ($row = mysqli_fetch_assoc($visitsquery)) {

                    $json = array(
                        "x" => $row["x"],
                        "y" => $row["y"],
                        "date" => $row["date"],
                        "time" => $row["time"],
                        "duration" => $row["duration"]
                    );

                    curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($json));
                    $output = curl_exec($handle);

                    if (!$output) {
                        $msg = "ERROR IN SUBMITTING VISITS TO WEBSERVICE!";
                        break;
                    }
                }
                curl_close($handle);

                http_response_code(200);
                echo "<script>", "if(!alert('", $msg, "')) window.location.href='./report.php';", "</script>"; //throws js message
            } else {
                $conn->close();
                http_response_code(500);
                echo "<script>", "if(!alert('", "Error in database access!", "')) window.location.href='./report.php';", "</script>"; //throws js message;
            }
        } else {
            $conn->close();
            http_response_code(500);
            echo "<script>", "if(!alert('", "Error in database access!", "')) window.location.href='./report.php';", "</script>"; //throws js message;
        }
    } else {
        $conn->close();
        http_response_code(400);
        echo "<script>", "if(!alert('", $errormsg, "')) window.location.href='./report.php';", "</script>"; //throws js message
    }
}
?>
<DOCTYPE html PUBLIC>
    <html>

    <head>
        <title>
            Report Page
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
                width: 90%;
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
                width: 200px;
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
                width: auto;
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
            <a href="./addvisit.php">
                <li>
                    Add Visit
                </li>
            </a>
            <a href="./report.php" class=selected>
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
                    <th colspan="3" class=status_header>Report an infection</th>
                </tr>
                <form action="report.php" method="PUT" id="report_form">

                    <tr>
                        <td colspan="3" class=caption>Please report the time and date you were tested positive for COVID-19</td>
                    </tr>
                    <tr>
                        <td colspan="3"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td><input type="date" name="date" class=input placeholder="Date" required></td>
                        <td></td>
                    </tr>

                    <tr>
                        <td></td>
                        <td><input type="time" name="time" class=input placeholder="Time" required></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td><input type=submit value=Report name="Report" class=button></td>
                        <td></td>
                        <td><input type=reset value=Cancel name="Cancel" class=button></td>
                    </tr>
                </form>


            </table>
        </div>


    </body>

    </html>