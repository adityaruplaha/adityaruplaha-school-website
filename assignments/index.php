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

<html lang='en'>

<head>
    <meta charset="utf-8">
    <title>XII Sc A - Assignments</title>
    <script src='script.js'>
    </script>
    <link rel='stylesheet' type='text/css' href='stylesheet.css' />
</head>

<body onload="clean()">

    <h1 align='center'>XII Sc A - Assignments</h1>
    <hr />
    <p align='center'>
        <i>
            <?php
            date_default_timezone_set("Asia/Kolkata");
            echo "Report generated on " . date("d M Y h:i:sa") . " IST."
            ?>
            <br />
            <br />
            Click on the subject header to see resources from that subject.
        </i>
        <hr />
    </p>

    <div>
        <table class='nav'>
            <tr>
                <td>Subject</td>
                <td>Assignments</td>
            </tr>
            <?php
            foreach ($SUBCODES as $k => $v) {
                $a = $ASS[$k];
                echo "<tr>";
                echo "<td onclick=\"show('{$k}')\" class='button'>{$v}</td><td>{$a}</td>";
                echo "</tr>";
            }
            ?>
        </table>
        <br />
    </div>

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
