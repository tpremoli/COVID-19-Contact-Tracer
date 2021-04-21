<?php
session_start();

if (!isset($_SESSION["userID"])) {
    header("Location: logout.php");
}

function loadPoints() //php function loading the locations from the webservice
{
    require "curlconnect.php";
    $ts = $_COOKIE["timewindow"] * 7;

    $url = "http://ml-lab-7b3a1aae-e63e-46ec-90c4-4e430b434198.ukwest.cloudapp.azure.com:60999/infections?ts=" . $ts;
    curl_setopt($handle, CURLOPT_URL, $url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);

    $output = curl_exec($handle);
    // echo $output;

    curl_close($handle);

    return $output;
}

//php function loading the user's visits
function getUserVisits()
{
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

    $sqlname = $conf["sqlname"];

    $conn->select_db("$sqlname") or die(mysqli_error($conn) . http_response_code(500));

    $ts = $_COOKIE["timewindow"] * 7;

    $visitquery = mysqli_query($conn, "SELECT *
                        FROM Visits
                        WHERE userID = '" . $_SESSION["userID"] . "'
                        AND date >= DATE(NOW() - INTERVAL '" . $ts . "' day)")
        or die(mysqli_error($conn) .  http_response_code(500));


    $conn->close();

    return mysqli_fetch_all($visitquery, MYSQLI_BOTH);
}

//checks if the uservisit is risky according to the given currentmarker.
// if true, there is a risk of infection (due to date, time, and location). If false, there's not a risk
function isRisky($uservisit, $currentmarker)
{
    $dist = sqrt(($uservisit["x"] - $currentmarker["x"]) ** 2 + ($uservisit["y"] - $currentmarker["y"]) ** 2);
    if ($dist <= $_COOKIE["distance"]) {
        if (isTimeOverlap($uservisit, $currentmarker)) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

//checks if dates and times of visits overlap, to see if they were risky.
function isTimeOverlap($uservisit, $currentmarker)
{
    if ($uservisit["date"] != $currentmarker["date"]) { // this works
        return false;
    }

    $usertimestart = new DateTime($uservisit["time"]);
    $usertimeend = clone $usertimestart;
    $usertimeend->add(date_interval_create_from_date_string($uservisit["duration"] . ' minutes'));

    $markerstart = new DateTime($currentmarker["time"]);
    $markerend = clone $markerstart;
    $markerend->add(date_interval_create_from_date_string($currentmarker["duration"] . ' minutes'));


    if (($usertimestart < $markerend)) {
        if (($markerstart < $usertimeend)) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

//returns Json of markers to be displayed.
function generateMarkers()
{
    $data = loadPoints();
    $uservisits = getUserVisits();

    $markers = json_decode($data, true);

    $resultmarkers = array(); //this will be passed into js for rendering each point

    foreach ($markers as $marker) {
        $risky = false; //turns true if within distance range

        // why am I creating a new marker with the same data?
        // cause for some reason I can't append risky to the 
        // existing marker. This works, don't question it
        $currentmarker = array(
            "x" => $marker["x"],
            "y" => $marker["y"],
            "date" => $marker["date"],
            "time" => $marker["time"],
            "duration" => $marker["duration"],
            "risky" => false //this indicates if the marker is red or not
        );
        foreach ($uservisits as $uservisit) {
            if (isRisky($uservisit, $currentmarker)) {
                $currentmarker["risky"] = true;
            }
        }

        array_push($resultmarkers, $currentmarker);
    }

    //rendered with js
    echo "<script>generateMarkers('" . json_encode($resultmarkers) . "')</script>";
}


?>
<script>
    function generateMarkers(data) { //gets the data from the php call and generates markers on the map
        try {
            var markers = JSON.parse(data);
        } catch (e) {
            document.write(e);
        }

        basex = document.getElementById("map").getBoundingClientRect().left;
        basey = document.getElementById("map").getBoundingClientRect().top;

        var i;
        for (i = 0; i < markers.length; i++) {
            if ((0 < markers[i].x && markers[i].x < 500) && (0 < markers[i].y && markers[i].y < 500)) {
                var img = document.createElement('img');
                if (markers[i].risky) {
                    img.src = 'marker_red.png';
                    style = "height: 40px; width: 40px; position: absolute; z-index: 100;";
                    // the id contains the infection's information, encoded as a JSON string
                    img.id = '[' + markers[i].x + ', ' + markers[i].y + ', "' + markers[i].date + '", "' + markers[i].time + '"]';
                    img.className = "redmarker";
                } else {
                    img.src = 'marker_black.png';
                    style = "height: 40px; width: 40px; position: absolute; z-index: 10;";
                    img.className = "blackmarker";
                }
                img.style = style;

                x = markers[i].x + basex - 20; //adjusting for image size to center marker
                y = markers[i].y + basey - 40; //it's 40x40, so centered on x, and on bottom of y

                img.style.left = x;
                img.style.top = y;

                document.getElementById('mapcontainer').appendChild(img);
            }
        }
    }

    // the onclick event of red markers.
    function highlightInfo(event) {
        info = JSON.parse(event.target.id);
        document.getElementById("infobox").innerHTML = "Infection at x = " + info[0] + " y = " + info[1] +
            ", on " + info[2] + ", " + info[3];
    }


    window.onload = function() {
        document.querySelectorAll('.redmarker').forEach(item => { //redmarker onclick
            item.addEventListener('click', highlightInfo, false);
        });
        if (document.querySelectorAll('.redmarker').length == 0) { // deletes warning if there hasn't been connections to infected
            document.getElementById("greeting").innerHTML = "Hello <?php echo $_SESSION["firstname"]; ?>, you have not had any connections" +
                " to infected people in the last <?php echo $_COOKIE["timewindow"]; ?> weeks.";
            document.getElementById("bottomgreeting").innerHTML = "";
        }
    };
</script>
<DOCTYPE html PUBLIC>
    <html>

    <head>
        <title>
            Home Page
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

            .status_header {
                font-family: Arial, Helvetica, sans-serif;
                font-size: 24;
                font-weight: bold;
            }

            tr.border_bottom th {
                border-bottom: 3px solid black;
            }

            #map {
                width: 500px;
                height: 500px;
                border: 3px solid black;
            }

            .caption {
                font-family: 'Times New Roman', Times, serif;
                font-size: 20;
            }
        </style>
    </head>

    <body>
        <h1 class=header>COVID - 19 Contact Tracing</h1>

        <ol class=sidebar>
            <a href="./home.php" class=selected>
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
                    <th colspan="2" class=status_header>Status</th>
                </tr>

                <tr>
                    <td class=caption id=greeting> Hello
                        <?php echo $_SESSION["firstname"]; ?>, you might have had a connection to an infected person at the locations shown in red</td>
                    <td rowspan="3" id="mapcontainer">
                        <img src="exeter.jpg" alt="" id="map">

                        <?php
                        generateMarkers();
                        ?>

                    </td>
                </tr>

                <tr>
                    <td class=caption id="infobox"></td>
                </tr>

                <tr>
                    <td class=caption id=bottomgreeting>
                        Click on the marker to see details about the infection.
                    </td>

                </tr>



            </table>


        </div>


    </body>

    </html>