<?php

require_once "../login.php";

use \ScA\Student\TGLogin\TGLogin;


$s = TGLogin::from_cookie();

$is_logged_in = ($s != NULL);

Deprecate\disable_page();

if ($s != NULL) {
    $s = (new \ScA\Student\Student(NULL, $s->id));
    $s->report_url_visit($_SERVER['PHP_SELF']);
}

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

use const ScA\Classes\SUBCODES;

$conn = new mysqli(DB_HOST, DB_USER, DB_PWD, DB);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$d = date("Y-m-d", $_GET['from'] ? strtotime($_GET['from']) : strtotime("yesterday"));

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
    <script src='/sc_a/scripts/http_requests.js'>
    </script>
    <script src='/sc_a/scripts/anchorme.min.js'>
    </script>
    <script src='script.js'>
    </script>
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/base.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/tables.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/modals.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/cards.css' />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/showdown/1.9.1/showdown.min.js"></script>
</head>

<body>

    <h1 class='center'>XII Sc A - Class Schedule</h1>
    <hr />
    <p class='center'>
        <i>
            <?php
            echo "Report generated on " . date("d M Y h:i:sa") . " IST."
            ?>
        </i>
    </p>
    <div class='tab' id='data'>
        <table class='bordered autowidth'>
            <tr>
                <th>Date</th>
                <th>Classes</th>
                <th>Time</th>
                <th>Trello</th>
                <th>Trello (Members Only)</th>
                <th>Upload By</th>
            </tr>
            <?php

            while ($day = Day::from_array($result->fetch_assoc())) {
                $u = $day->get_upload_data($conn);
                $cl = $day->get_classes($conn, $subjects);
                $n = count($cl);
                if (!$n) {
                    if (!empty($subjects)) {
                        continue;
                    }
                    echo "<tr>";
                    echo "<td>" . date("d F Y (D)", $day->date) . "</td>";
                    echo "<td colspan=3>No classes scheduled.</td>";
                    echo "<td style='text-align: center;'><a href=\"" . $day->trello . "\">" . $day->trello . "</a></td>";
                    echo "<td>" . $u["UploadedBy"] . "</td>";
                    echo "</tr>";
                    continue;
                }
                $cl1 = array_shift($cl);
                echo "<tr>";
                echo "<td rowspan={$n}>" . date("d F Y (D)", $day->date) . "</td>";
                echo "<td class='hoverable' onclick='details({$cl1->timestamp}, \"{$cl1->subject}\")'>" . SUBCODES[$cl1->subject] . "</td><td>" . date('h:i A', $cl1->timestamp) .
                    "</td><td><a href=\"" . $cl1->trello . "\">" . $cl1->trello . "</a></td>";
                echo "<td rowspan={$n} style='text-align: center;'><a href=\"" . $day->trello . "\">" . $day->trello . "</a></td>";
                echo "<td rowspan={$n}>" . $u["UploadedBy"] . "</td>";
                echo "</tr>";
                foreach ($cl as $c) {
                    echo "<tr>";
                    echo "<td  class='hoverable' onclick='details({$c->timestamp}, \"{$c->subject}\")'>" . SUBCODES[$c->subject] . "</td><td>" . date('h:i A', $c->timestamp) .
                        "</td><td><a href=\"" . $c->trello . "\">" . $c->trello . "</a></td>";
                    echo "</tr>";
                }
            }

            ?>
        </table>
    </div>
</body>

</html>
