<?php

require_once "../login.php";
require_once "../teacher/defs.php";

use \ScA\Student\TGLogin\TGLogin;
use \ScA\Teacher;

$is_logged_in = (TGLogin::from_cookie() != NULL) || (Teacher\is_logged_in());

if (!$is_logged_in) {
    header("Location: ../?nauth");
    exit;
}

require_once '../classes.php';

date_default_timezone_set("Asia/Kolkata");

use ScA\Classes\Day;

use const ScA\DB;
use const ScA\DB_HOST;
use const ScA\DB_PWD;
use const ScA\DB_USER;

use const ScA\Classes\SCHEDULE_BEAUTY_TABULATED;

$conn = new mysqli(DB_HOST, DB_USER, DB_PWD, DB);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$d = date("Y-m-d", strtotime("yesterday"));

// Query
$result = $conn->query("SELECT `Date`, `PrivateTrello`
    FROM days WHERE `Date` >= '{$d}'");

if (!$result) {
    die("Query to show fields from table failed");
}

$subjects = isset($_GET['subs']) ? explode(',', $_GET['subs']) : [];

?>
<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset="utf-8">
    <title>XII Sc A - Class Schedule</title>
    <link rel='stylesheet' type='text/css' href='stylesheet.css' />
</head>

<body>

    <h1 align='center'>XII Sc A - Class Schedule</h1>
    <hr />
    <p align='center'>
        <i>
            <?php
            echo "Report generated on " . date("d M Y h:i:sa") . " IST."
            ?>
        </i>
    </p>
    <hr />
    <p align='center'>
        Please note, in the schedule...<br />
        <br />
        Chemistry (Vol 1) = Soumi Ma'am, even if she's teaching Vol 2.<br />
        Chemistry (Vol 2) = ND Ma'am, even if she's teaching Vol 1.<br />
        <br />
        Physics (Vol 1) = Saswati Ma'am, even if she's teaching Vol 2.<br />
        Physics (Vol 2) = Debarati Ma'am, even if she's teaching Vol 1.<br />
        <br />
        This is for statistical continuity and historical reasons.
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
                $u = $day->get_upload_data($conn);
                $ub = ($u["UploadedBy"] ? "<br/><br/><b>Upload By: {$u["UploadedBy"]}</b>" : "");
                $cl = $day->get_classes($conn, $subjects);
                $n = count($cl);
                $cl1 = array_shift($cl);
                echo "<tr>";
                echo "<td rowspan={$n}>" . date("d F Y (D)", $day->date) . "</td>";
                print($cl1->beautify(SCHEDULE_BEAUTY_TABULATED));
                echo "<td rowspan={$n} style='text-align: center;'><a href=\"" . $day->trello . "\">" . $day->trello . "</a>{$ub}</td>";
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