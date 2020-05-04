<?php

require '../defs.php';

date_default_timezone_set("Asia/Kolkata");

$db_host = 'localhost';
$db_user = 'prog_access';
$db_pwd = '';

$database = 'school';
$table = 'xii_sc_a_ptrello';

$conn = new mysqli($db_host, $db_user, $db_pwd, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$d = date("Y-m-d", strtotime("yesterday"));

// Query
$result = $conn->query("SELECT `Date`, `PrivateTrello`
    FROM {$table} WHERE `Date` >= '{$d}'");

if (!$result) {
    die("Query to show fields from table failed");
}

$subjects = isset($_GET['subs']) ? explode(',', $_GET['subs']) : [];

?>
<html lang='en'>

<head>
    <meta charset="utf-8">
    <title>XII Sc A - Class Schedule</title>
    <link rel='stylesheet' type='text/css' href='stylesheet.css' />
</head>

<body onload="beautify()">

    <h1 align='center'>XII Sc A - Class Schedule</h1>
    <hr />
    <p align='center'>
        <i>
            <?php
            echo "Report generated on " . date("d M Y h:i:sa") . " IST."
            ?>
        </i>
    </p>
    <div class='tab' id='data'>
        <table>
            <tr>
                <th>Date</th>
                <th>Classes</th>
                <th>Time</th>
                <th>Trello</th>
                <th>Trello (Members Only)</th>
            </tr>
            <?php

            while ($day = Day::from_array($result->fetch_assoc())) {
                $cl = $day->get_classes($conn, $subjects);
                $n = count($cl);
                $cl1 = array_shift($cl);
                echo "<tr>";
                echo "<td rowspan={$n}>" . date("d F Y (D)", $day->date) . "</td>";
                print($cl1->beautify(SCHEDULE_BEAUTY_TABULATED));
                echo "<td rowspan={$n}><a href=\"" . $day->trello . "\">" . $day->trello . "</a></td>";
                echo "</tr>";
                foreach ($cl as $c) {
                    echo "<tr>";
                    print($c->beautify(SCHEDULE_BEAUTY_TABULATED));
                    echo "</tr>";
                }
            }

            ?>
        </table>
    </div>
</body>

</html>