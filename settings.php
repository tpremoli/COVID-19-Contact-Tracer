<?php
session_start();

if (!isset($_SESSION["userID"])) {
    header("Location: logout.php");
} else if (!empty($_GET)) {
    updateData(); //here goes the function call
}

//when submission happens this runs
function updateData() 
{
    $timewindow = $_GET["timewindow"];
    $distance = $_GET["distance"];

    if (!ctype_digit($timewindow) or !ctype_digit($distance)) { //checks fields are numeric
        $errormsg = "Error in registration: Fields must be numerical and positive!"; // relay to user
        http_response_code(400);
        echo "<script>", "if(!alert('", $errormsg, "')) window.location.href='./settings.php';", "</script>"; //throws js message
        exit();
    } elseif (!(($distance <= 500) and ($$distance >= 0))) {
        $errormsg = "Error in updating: Distance must be between 0 and 500!"; // relay to user
        http_response_code(400);
        echo "<script>", "if(!alert('", $errormsg, "')) window.location.href='./settings.php';", "</script>"; //throws js message
        exit();
    } else {
        setcookie("timewindow", $timewindow);
        setcookie("distance", $distance);


        $msg = "Successfully updated settings! ";
        http_response_code(200);
        echo "<script>", "if(!alert('", $msg, "')) window.location.href='./settings.php';", "</script>";
    }
}


?>

<script type="text/javascript">
    function resetSettings() { //sets fields back to cookie values
        setSelectedIndex(document.getElementById("timewindow"), getCookie("timewindow"));
        document.getElementById("distance").value = getCookie("distance");
    }

    function setSelectedIndex(s, i) {
        s.options[i - 1].selected = true;
        return;
    }

    function getCookie(cname) { //code to get cookie from document.cookie string
        var name = cname + "=";
        var decodedCookie = decodeURIComponent(document.cookie);
        var ca = decodedCookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length);
            }
        }
        return "";
    }

    window.onload = function() {
        setSelectedIndex(document.getElementById("timewindow"), getCookie("timewindow"));
    };
</script>

<DOCTYPE html PUBLIC>
    <html>

    <head>
        <title>
            Settings Page
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
                width: 400px;
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
            <a href="./report.php">
                <li>
                    Report
                </li>
            </a>
            <a href="./settings.php" class=selected>
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
                    <th colspan="3" class=status_header>Alert Settings</th>
                </tr>
                <form action="settings.php" method="PUT" id="settings_form">

                    <tr>
                        <td colspan="3" class=caption>Here you may change the alert distance and time span for which contact tracing will be performed</td>
                    </tr>
                    <tr>
                        <td colspan="3"></td>
                    </tr>
                    <tr>
                        <td style="text-align:right;" class="caption">window</td>
                        <td>
                            <select id="timewindow" name="timewindow" form="settings_form" class=input selected="<?php echo $_COOKIE['timewindow']; ?>">
                                <option value='1' class=input>1 week</option>
                                <option value='2' class=input>2 weeks</option>
                                <option value='3' class=input>3 weeks</option>
                                <option value='4' class=input>4 weeks</option>
                        </td>
                        <td></td>
                    </tr>

                    <tr>
                        <td style="text-align:right;" class="caption">distance</td>
                        <td><input type="text" id="distance" name="distance" class=input required value="<?php echo $_COOKIE['distance']; ?>"></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td><input type=submit name="Update" class=button></td>
                        <td></td>

                </form>
                <td><input type=reset value="Cancel" name="Cancel" class=button onclick="resetSettings()"></td>
                </tr>


            </table>
        </div>


    </body>

    </html>