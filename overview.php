<?php
session_start();

if (!isset($_SESSION["userID"])) {
    header("Location: logout.php");
}

function loadTable() //loads table of visits
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

    $visitquery = mysqli_query($conn, "SELECT *
                        FROM Visits
                        WHERE userID = '" . $_SESSION["userID"] . "'")
        or die(mysqli_error($conn) .  http_response_code(500));


    $conn->close();

    return $visitquery;
}
?>
<script type="text/javascript">
    window.onload = function() {//onclick for cross images
        document.querySelectorAll('.cross').forEach(item => {
            item.addEventListener('click', deleteVisit, false);
        });
    };

    function deleteVisit(event) { //deletes selected visit through AJAX call
        var index = event.target.parentElement.parentElement.rowIndex; //getting row

        var table = document.getElementById("visits_table");
        var row = table.rows[index];

        id = row.cells[0].innerHTML;


        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                if (!alert("Element successfully deleted!")) {
                    window.location.href = './overview.php';
                }
            } else if (this.readyState == 4 && this.status != 200) {
                if (!alert("ERROR IN DELETING ELEMENT! Code: " + this.status)) {
                    window.location.href = './overview.php';
                }
            }
        };
        xmlhttp.open("POST", "deleterecord.php", true);
        xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xmlhttp.send("visitID=" + id);

    }
</script>

<DOCTYPE html PUBLIC>
    <html>

    <head>
        <title>
            Overview Page
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
                height: 50px;
            }

            .status_header {
                font-family: Arial, Helvetica, sans-serif;
                font-size: 24;
                font-weight: bold;
                height: 100px;
            }

            .table_items {
                font-family: 'Times New Roman', Times, serif;
                font-size: 20;
            }

            .caption {
                font-family: 'Times New Roman', Times, serif;
                font-size: 20;
            }

            .cross {
                height: 30px;
                width: 30px;
                z-index: 10;
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
            <a href="./overview.php" class=selected>
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
            <table class=center id="visits_table">


                <tr class=status_header>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Duration</th>
                    <th>X</th>
                    <th>Y</th>
                    <th></th>
                </tr>

                <?php
                $visits = loadTable(); //this renders the table of visits

                while ($row = mysqli_fetch_assoc($visits)) {
                ?>
                    <tr class=table_items>
                        <td style="display:none;"><?php echo $row['visitID'] ?></td>
                        <td><?php echo $row['date'] ?></td>
                        <td><?php echo $row['time'] ?></td>
                        <td><?php echo $row['duration'] ?></td>
                        <td><?php echo $row['x'] ?></td>
                        <td><?php echo $row['y'] ?></td>
                        <td><img src="cross.png" alt="" class="cross"></td>
                    </tr>

                <?php
                }
                ?>

            </table>
        </div>


    </body>

    </html>