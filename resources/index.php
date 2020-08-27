<?php

require_once "../login.php";
require_once "../teacher/defs.php";

use \ScA\Student\TGLogin\TGLogin;
use \ScA\Teacher;

$s = TGLogin::from_cookie();

$s = TGLogin::from_cookie();
if ($s != NULL) {
    $s = new \ScA\Student\Student(NULL, $s->id);
    if ($s->get_block_resource_access()) {
        $s = NULL;
    }
}

if ($s != NULL) {
    $s->report_url_visit($_SERVER['PHP_SELF']);
}

$is_logged_in = ($s != NULL) || (Teacher\is_logged_in());

if (!$is_logged_in) {
    header("Location: ../?nauth");
    exit;
}


require_once "../defs.php";

use const ScA\DB;
use const ScA\DB_HOST;
use const ScA\DB_PWD;
use const ScA\DB_USER;

$table = "resources";

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

$RES = array();
foreach ($SUBCODES as $sub => $v) {
    $result = $conn->query("SELECT COUNT(`Name`) FROM {$table} WHERE `Subject` = '{$sub}'");
    if (!$result) {
        die("Query to show fields from table failed.");
    }
    $RES[$sub] = $result->fetch_row()[0];
}
?>

<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=0.75">
    <title>XII Sc A - Resources</title>
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
    <script src="/sc_a/scripts/sorttable.js"></script>
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/base.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/tables.css' />
    <link rel='stylesheet' type='text/css' href='/sc_a/themes/dark/select.css' />
</head>

<body onload="clean(); showint(0);">
    <h1 class='center'>XII Sc A - Resources</h1>
    <hr />
    <p class='center'>
        <i>
            <?php
            date_default_timezone_set("Asia/Kolkata");
            echo "Report generated on " . date("d M Y h:i:sa") . " IST."
            ?>
        </i>
    </p>
    <hr />
    <p>
        <label for="subject">Select Subject: </label>&nbsp;&nbsp;
        <select name="subject" id="subject" onchange="showint(value)" class="select-css">
            <?php
            $i = 0;
            foreach ($SUBCODES as $k => $v) {
                $a = $RES[$k];
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
        $result = $conn->query("SELECT `Name`, `Notes`, `GivenOn`,
         `URL`, `Source` FROM {$table} WHERE `Subject` = '{$sub}' ORDER BY `{$table}`.`GivenOn` ASC");

        if (!$result) {
            die("Query to show fields from table failed.");
        }

        echo "
        <div class='tab' id='{$sub}'>
        <style>
        table.sortable th:not(.sorttable_sorted):not(.sorttable_sorted_reverse):not(.sorttable_nosort):after { 
            content: \" \\25B4\\25BE\" 
        }
        </style>
        <table class='center autowidth semibordered conpact sortable'>
            <tr>
                <th>Resource</th>
                <th>Given On</th>
                <th class='sorttable_nosort'>File/URL</th>
                <th>Source</th>
                <th class='sorttable_nosort'>Notes</th>
            </tr>";

        while ($resource = $result->fetch_assoc()) {
            if (!$s->has_privileges("Member") && $resource["Source"] == "Community") {
                continue;
            }

            echo "<tr>";

            echo "<td>" . $resource["Name"] . "</td>";

            echo ($d = $resource["GivenOn"]) ? "<td sorttable_customkey='{$d}'>" . strftime('%d %B %Y', strtotime($d)) . "</td>" : "<td></td>";
            if ($l = $resource["URL"]) {
                if (strpos($l, "https://classroom.google.com/") === 0) {
                    echo "<td><a class='compact' href=\"" . $l . "\">Join</a></td>";
                } elseif (strpos($l, "https://trello.com/") === 0) {
                    echo "<td><a class='compact' href=\"" . $l . "\">Open Card</a></td>";
                } elseif (strpos($l, "https://res.cloudinary.com/") === 0) {
                    echo "<td><a class='compact red' href=\"" . $l . "\">[Dead link]</a></td>";
                } elseif (strpos($l, "https://trello-attachments.s3.amazonaws.com/") === 0) {
                    echo "<td><a class='compact' href=\"" . $l . "\">Download</a></td>";
                } elseif (strpos($l, "https://s3.ap-south-1.amazonaws.com/res.cloudinary-s3-vawsum-new-media/") === 0) {
                    echo "<td><a class='compact' href=\"" . $l . "\">Download</a></td>";
                } elseif (strpos($l, "http://schoolatweb.byethost7.com/") === 0) {
                    echo "<td><a class='compact yellow' href=\"" . $l . "\">Download</a></td>";
                } elseif (strpos($l, "https://drive.google.com/") === 0) {
                    echo "<td><a class='compact' href=\"" . $l . "\">Open in Drive</a></td>";
                } else {
                    echo "<td><a class='compact' href=\"" . $l . "\">Open</a></td>";
                }
            } else {
                echo "<td></td>";
            }
            echo ($source = $resource["Source"]) ? "<td>" . $source . "</td>" : "<td></td>";

            if ($r = $resource["Notes"]) {
                echo "<td class='button' onclick=\"alert(`" . htmlspecialchars($r) . "`);\">Click to view.</td>";
            } else {
                echo "<td></td>";
            }

            echo "</tr>";
        }

        echo "</table>
        <p>You are viewing Resources for {$SUB}.</p>
        </div>";
        $result->free();
    }

    ?>



</body>

</html>