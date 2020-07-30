<?php

require_once "../login.php";
require_once "../teacher/defs.php";

use \ScA\Student\TGLogin\TGLogin;
use \ScA\Teacher;

$s = TGLogin::from_cookie();

$is_logged_in = ($s != NULL) || (Teacher\is_logged_in());

if ($s != NULL) {
    $s = (new \ScA\Student\Student(NULL, $s->id));
    $s->report_url_visit($_SERVER['PHP_SELF']);
}

if (!$is_logged_in) {
    header("Location: ../?nauth");
    exit;
}


require_once "../defs.php";

use const ScA\DB;
use const ScA\DB_HOST;
use const ScA\DB_PWD;
use const ScA\DB_USER;

$table = 'assignments';

$conn = new mysqli(DB_HOST, DB_USER, DB_PWD, DB);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$d = date("Y-m-d");

$SUBCODES = array(
    "phy" => "Physics",
    "chem" => "Chemistry",
    "math" => "Mathematics",
    "cs" => "Computer Science",
    "en" => "English",
    "pe" => "Physical Education",
    "bn" => "Bengali",
    "hi" => "Hindi",
    "any" => "Miscellaneous",
);

$ASS = array();
foreach ($SUBCODES as $sub => $v) {
    $result = $conn->query("SELECT COUNT(`Name`) FROM {$table} WHERE `Subject` = '{$sub}'");
    if (!$result) {
        die("Query to show fields from table failed.");
    }
    $ASS[$sub] = $result->fetch_row()[0];
}
?>

<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=0.75">
    <title>XII Sc A - Assignments</title>
    <script src='script.js'>
    </script>
    <?php
    echo "<script>";
    echo "function showint(n) {";
    $buf = [];
    foreach ($SUBCODES as $sub => $v) {
        array_push($buf, "'{$sub}'");
    }
    echo "var subs = [" . implode(",", $buf) . "];";
    echo "show(subs[n]);";
    echo "}";
    echo "</script>";
    ?>
    <link rel='stylesheet' type='text/css' href='stylesheet.css' />
    <link rel='stylesheet' type='text/css' href='../shared/select-css.css' />
</head>

<body onload="clean(); showint(0);">

    <h1 align='center'>XII Sc A - Assignments</h1>
    <hr />
    <p align='center'>
        <i>
            <?php
            date_default_timezone_set("Asia/Kolkata");
            echo "Report generated on " . date("d M Y h:i:sa") . " IST."
            ?>
        </i>
        <hr />
    </p>

    <p>
        <label for="subject">Select Subject: </label>&nbsp;&nbsp;
        <select name="subject" id="subject" onchange="showint(value)" class="select-css">
            <?php
            $i = 0;
            foreach ($SUBCODES as $k => $v) {
                $a = $ASS[$k];
                echo "<option class='button' value='{$i}'>{$v} ({$a})</option>";
                echo "</tr>";
                $i += 1;
            }
            ?>
        </select>
    </p>

    <?php

    foreach ($SUBCODES as $sub => $SUB) {

        // Query
        $result = $conn->query("SELECT `Name`, `Notes`, DATE_FORMAT(`AssignedOn`, '%d %M %Y') 'AssignedOn',
        DATE_FORMAT(`DueOn`, '%d %M %Y') 'DueOn', `URL` FROM {$table} WHERE `Subject` = '{$sub}' ORDER BY `{$table}`.`AssignedOn` ASC");

        if (!$result) {
            die("Query to show fields from table failed.");
        }

        echo "
        <div class='tab' id='{$sub}'>
        <table>
            <tr>
                <th>Assignment</th>
                <th>Assigned On</th>
                <th>Due On</th>
                <th>File/URL</th>
                <th>Notes</th>
            </tr>";

        while ($assignment = $result->fetch_assoc()) {
            echo "<tr>";

            echo "<td>" . $assignment["Name"] . "</td>";
            echo "<td>" . $assignment["AssignedOn"] . "</td>";

            echo ($d = $assignment["DueOn"]) ? "<td>" . $d . "</td>" : "<td></td>";
            echo ($l = $assignment["URL"]) ? "<td><a href=\"" . $l . "\">Download</a></td>" : "<td></td>";

            if ($r = $assignment["Notes"]) {
                echo "<td class='button' onclick=\"alert(`" . htmlspecialchars($r) . "`);\">Click to view.</td>";
            } else {
                echo "<td></td>";
            }

            echo "</tr>";
        }

        echo "</table>
        <p>You are viewing assignments for {$SUB}.</p>
        </div>";
        $result->free();
    }

    ?>
</body>

</html>